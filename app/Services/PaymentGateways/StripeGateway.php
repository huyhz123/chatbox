<?php

namespace App\Services\PaymentGateways;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripeGateway
{
    protected $config;

    public function __construct()
    {
        $this->config = config('payment.gateways.stripe');

        if (!$this->config['enabled']) {
            throw new Exception('Stripe gateway is not enabled');
        }

        // Initialize Stripe API
        Stripe::setApiKey($this->config['secret']);
    }

    /**
     * Create a payment and return payment session data
     *
     * @param Model $payable
     * @param Payment $payment
     * @param array $data
     * @return array
     */
    public function createPayment(Model $payable, Payment $payment, array $data = [])
    {
        try {
            $lineItems = [];
            $totalAmount = 0;

            foreach ($order->items as $item) {
                $amount = (int)($item->unit_price * 100); // Convert to cents
                $totalAmount += $amount * $item->quantity;

                $lineItems[] = [
                    'price_data' => [
                        'currency' => strtolower($payment->currency),
                        'product_data' => [
                            'name' => $item->product_name ?? 'Product',
                            'description' => $item->product_description ?? null,
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => $item->quantity,
                ];
            }

            // Create Stripe checkout session
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.success', ['payment' => $payment->id, 'session_id' => '{CHECKOUT_SESSION_ID}']),
                'cancel_url' => route('payment.cancel', ['payment' => $payment->id]),
                'customer_email' => $order->user->email ?? null,
                'metadata' => [
                    'order_id' => $payable->id,
                    'payment_id' => $payment->id,
                    'user_id' => $order->user_id,
                ],
            ]);

            Log::info('Stripe payment session created', [
                'order_id' => $payable->id,
                'payment_id' => $payment->id,
                'session_id' => $session->id,
                'amount' => $totalAmount,
            ]);

            return [
                'session_id' => $session->id,
                'payment_url' => $session->url,
                'stripe_public_key' => config('services.stripe.public'),
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment creation failed', [
                'order_id' => $payable->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Stripe payment creation failed: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('Stripe payment creation failed', [
                'order_id' => $payable->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment callback from Stripe webhook
     *
     * @param array $data
     * @return string|null Payment ID
     */
    public function handleCallback(array $data)
    {
        try {
            $webhookSecret = $this->config['webhook_secret'];
            $payload = $data['payload'] ?? null;
            $signature = $data['signature'] ?? null;

            if (!$payload || !$signature) {
                throw new Exception('Missing payload or signature');
            }

            // Verify webhook signature
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );

            // Handle the event
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                if ($session->payment_status !== 'paid') {
                    throw new Exception('Payment not completed');
                }

                $transactionId = $session->payment_intent;

                Log::info('Stripe webhook processed successfully', [
                    'session_id' => $session->id,
                    'transaction_id' => $transactionId,
                    'payment_status' => $session->payment_status,
                ]);

                return $transactionId;
            }

            throw new Exception('Unhandled event type: ' . $event->type);
        } catch (Exception $e) {
            Log::error('Stripe webhook error', [
                'error' => $e->getMessage(),
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

            // Retrieve payment intent from Stripe
            $paymentIntent = \Stripe\PaymentIntent::retrieve($payment->transaction_id);

            if ($paymentIntent->status === 'succeeded') {
                Log::info('Stripe payment verified as succeeded', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                ]);

                return true;
            }

            Log::warning('Stripe payment not succeeded', [
                'payment_id' => $payment->id,
                'status' => $paymentIntent->status,
            ]);

            return false;
        } catch (ApiErrorException $e) {
            Log::error('Stripe verification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
