<?php

namespace App\Services\PaymentGateways;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZaloPayGateway
{
    protected $config;

    public function __construct()
    {
        $this->config = config('payment.gateways.zalopay');

        if (!$this->config['enabled']) {
            throw new Exception('ZaloPay gateway is not enabled');
        }
    }

    /**
     * Create a payment and return payment data
     *
     * @param Model $payable
     * @param Payment $payment
     * @param array $data
     * @return array
     */
    public function createPayment(Model $payable, Payment $payment, array $data = [])
    {
        try {
            $appId = $this->config['app_id'];
            $key1 = $this->config['key1'];
            $key2 = $this->config['key2'];
            $endpoint = $this->config['endpoint'];
            $callbackUrl = $this->config['callback_url'];

            $transId = time() . '-' . $payment->id;
            $amount = (int)($payment->amount * 100); // ZaloPay uses smallest unit

            $embedData = [
                'order_id' => $payable->id,
                'payment_id' => $payment->id,
            ];

            $itemData = [];
            foreach ($order->items as $item) {
                $itemData[] = [
                    'itemid' => $item->id,
                    'itemname' => $item->product_name ?? 'Item',
                    'itemprice' => (int)$item->unit_price * 100,
                    'itemquantity' => $item->quantity,
                ];
            }

            $postData = [
                'app_id' => $appId,
                'app_trans_id' => $transId,
                'app_user' => $order->user_id,
                'app_time' => time() * 1000,
                'amount' => $amount,
                'item' => json_encode($itemData),
                'description' => "Payment for {$payable->id} - E-Commerce Platform",
                'embed_data' => json_encode($embedData),
                'callback_url' => $callbackUrl,
            ];

            $data_str = "{$appId}|{$transId}|{$order->user_id}|{$amount}|" . time() * 1000 . "|" . json_encode($embedData) . "|" . json_encode($itemData);
            $mac = hash_hmac('sha256', $data_str, $key1);

            $postData['mac'] = $mac;

            $response = Http::timeout(30)->post($endpoint, $postData);

            if (!$response->successful()) {
                throw new Exception('ZaloPay API error: ' . $response->status());
            }

            $result = $response->json();

            if (($result['return_code'] ?? -1) != 1) {
                throw new Exception('ZaloPay payment creation failed: ' . ($result['return_message'] ?? 'Unknown error'));
            }

            Log::info('ZaloPay payment created', [
                'order_id' => $payable->id,
                'payment_id' => $payment->id,
                'zalo_trans_id' => $transId,
            ]);

            return [
                'order_token' => $result['order_token'] ?? null,
                'return_code' => $result['return_code'] ?? null,
                'trans_id' => $transId,
                'qr_code' => $result['qr_code'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('ZaloPay payment creation failed', [
                'order_id' => $payable->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment callback from ZaloPay
     *
     * @param array $data
     * @return string|null Payment ID
     */
    public function handleCallback(array $data)
    {
        try {
            $key2 = $this->config['key2'];

            // Get and verify MAC
            $mac = $data['mac'] ?? null;

            if (!$mac) {
                throw new Exception('Missing MAC in callback');
            }

            // Reconstruct the data string for verification
            $dataStr = "{$data['app_id']}|{$data['app_trans_id']}|{$data['app_user']}|{$data['amount']}|{$data['app_time']}|{$data['embed_data']}|{$data['item']}";
            $computedMac = hash_hmac('sha256', $dataStr, $key2);

            if ($computedMac !== $mac) {
                throw new Exception('Invalid ZaloPay MAC');
            }

            $returnCode = $data['return_code'] ?? null;

            if ($returnCode != 1) {
                throw new Exception("Payment failed with return code: {$returnCode}");
            }

            $transactionId = $data['zalo_trans_id'] ?? $data['app_trans_id'] ?? null;

            Log::info('ZaloPay callback processed successfully', [
                'app_trans_id' => $data['app_trans_id'] ?? null,
                'transaction_id' => $transactionId,
            ]);

            return $transactionId;
        } catch (Exception $e) {
            Log::error('ZaloPay callback error', [
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
            // ZaloPay verification - check payment status
            // In production, you might want to call ZaloPay's query API

            Log::info('ZaloPay payment verified', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);

            return $payment->status === 'completed';
        } catch (Exception $e) {
            Log::error('ZaloPay verification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
