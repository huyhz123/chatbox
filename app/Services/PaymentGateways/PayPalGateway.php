<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalGateway
{
    protected $config;
    protected $mode;
    protected $clientId;
    protected $secret;
    protected $endpoint;

    public function __construct()
    {
        $this->config = config('payment.gateways.paypal');

        if (!$this->config['enabled']) {
            throw new Exception('PayPal gateway is not enabled');
        }

        $this->mode = $this->config['mode'];
        $this->selectCredentials();
    }

    /**
     * Select credentials based on mode
     *
     * @return void
     */
    protected function selectCredentials()
    {
        if ($this->mode === 'sandbox') {
            $this->clientId = $this->config['sandbox']['client_id'];
            $this->secret = $this->config['sandbox']['secret'];
            $this->endpoint = 'https://api-m.sandbox.paypal.com';
        } else {
            $this->clientId = $this->config['live']['client_id'];
            $this->secret = $this->config['live']['secret'];
            $this->endpoint = 'https://api-m.paypal.com';
        }
    }

    /**
     * Get PayPal access token
     *
     * @return string
     */
    protected function getAccessToken()
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->secret)
                ->post($this->endpoint . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get PayPal access token');
            }

            return $response->json()['access_token'];
        } catch (Exception $e) {
            Log::error('PayPal token error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a payment and return payment data
     *
     * @param Order $order
     * @param Payment $payment
     * @param array $data
     * @return array
     */
    public function createPayment(Order $order, Payment $payment, array $data = [])
    {
        try {
            $accessToken = $this->getAccessToken();

            $items = [];
            $totalAmount = 0;

            foreach ($order->items as $item) {
                $itemPrice = number_format($item->unit_price, 2, '.', '');
                $itemTotal = $itemPrice * $item->quantity;
                $totalAmount += $itemTotal;

                $items[] = [
                    'name' => $item->product_name ?? 'Product',
                    'description' => $item->product_description ?? null,
                    'quantity' => $item->quantity,
                    'unit_amount' => [
                        'currency_code' => strtoupper($payment->currency),
                        'value' => $itemPrice,
                    ],
                ];
            }

            $createOrderPayload = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $order->id,
                        'description' => "Order {$order->id}",
                        'amount' => [
                            'currency_code' => strtoupper($payment->currency),
                            'value' => number_format($totalAmount, 2, '.', ''),
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => strtoupper($payment->currency),
                                    'value' => number_format($totalAmount, 2, '.', ''),
                                ],
                            ],
                        ],
                        'items' => $items,
                    ],
                ],
                'payer' => [
                    'email_address' => $order->user->email ?? null,
                ],
                'application_context' => [
                    'return_url' => route('payment.success', ['payment' => $payment->id]),
                    'cancel_url' => route('payment.cancel', ['payment' => $payment->id]),
                    'brand_name' => 'E-Commerce Platform',
                    'user_action' => 'PAY_NOW',
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post($this->endpoint . '/v2/checkout/orders', $createOrderPayload);

            if (!$response->successful()) {
                throw new Exception('PayPal order creation failed: ' . $response->body());
            }

            $result = $response->json();

            if (($result['status'] ?? null) !== 'CREATED') {
                throw new Exception('PayPal order status not created');
            }

            $orderId = $result['id'];
            $approvalLink = null;

            foreach ($result['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalLink = $link['href'];
                    break;
                }
            }

            Log::info('PayPal payment created', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'paypal_order_id' => $orderId,
            ]);

            return [
                'order_id' => $orderId,
                'payment_url' => $approvalLink,
                'status' => $result['status'],
            ];
        } catch (Exception $e) {
            Log::error('PayPal payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment callback from PayPal
     *
     * @param array $data
     * @return string|null Payment ID
     */
    public function handleCallback(array $data)
    {
        try {
            $orderId = $data['orderID'] ?? null;

            if (!$orderId) {
                throw new Exception('Missing PayPal order ID');
            }

            $accessToken = $this->getAccessToken();

            // Capture payment
            $response = Http::withToken($accessToken)
                ->post($this->endpoint . "/v2/checkout/orders/{$orderId}/capture");

            if (!$response->successful()) {
                throw new Exception('PayPal payment capture failed');
            }

            $result = $response->json();

            if (($result['status'] ?? null) !== 'COMPLETED') {
                throw new Exception("Payment status not completed: {$result['status']}");
            }

            // Get transaction ID
            $transactionId = null;
            if (isset($result['purchase_units'][0]['payments']['captures'][0]['id'])) {
                $transactionId = $result['purchase_units'][0]['payments']['captures'][0]['id'];
            }

            Log::info('PayPal callback processed successfully', [
                'paypal_order_id' => $orderId,
                'transaction_id' => $transactionId,
                'status' => $result['status'],
            ]);

            return $transactionId;
        } catch (Exception $e) {
            Log::error('PayPal callback error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Verify payment status
     *
     * @param Payment $payment
     * @return bool
     */
    public function verifyPayment(Payment $payment)
    {
        try {
            if (!$payment->transaction_id) {
                return false;
            }

            $accessToken = $this->getAccessToken();

            // Query PayPal for capture details
            $response = Http::withToken($accessToken)
                ->get($this->endpoint . "/v2/payments/captures/{$payment->transaction_id}");

            if (!$response->successful()) {
                return false;
            }

            $result = $response->json();

            if (($result['status'] ?? null) === 'COMPLETED') {
                Log::info('PayPal payment verified as completed', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                ]);

                return true;
            }

            Log::warning('PayPal payment not completed', [
                'payment_id' => $payment->id,
                'status' => $result['status'] ?? null,
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('PayPal verification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
