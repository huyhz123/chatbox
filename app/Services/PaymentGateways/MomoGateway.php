<?php

namespace App\Services\PaymentGateways;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoGateway
{
    protected $config;

    public function __construct()
    {
        $this->config = config('payment.gateways.momo');

        if (!$this->config['enabled']) {
            throw new Exception('MoMo gateway is not enabled');
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
            $partnerCode = $this->config['partner_code'];
            $accessKey = $this->config['access_key'];
            $secretKey = $this->config['secret_key'];
            $endpoint = $this->config['endpoint'];
            $returnUrl = $this->config['return_url'];
            $notifyUrl = $this->config['notify_url'];

            $orderId = $payment->id . '-' . time();
            $amount = (int)$payment->amount;

            $requestId = time() . '';
            $requestType = 'captureWallet';
            $extraData = base64_encode(json_encode(['order_id' => $payable->id, 'payment_id' => $payment->id]));
            $rawSignature = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&orderId={$orderId}&orderInfo=Payment for {$payable->id}&partnerCode={$partnerCode}&requestId={$requestId}&requestType={$requestType}&returnUrl={$returnUrl}&notifyUrl={$notifyUrl}";

            $signature = hash_hmac('sha256', $rawSignature, $secretKey);

            $postData = [
                'partnerCode' => $partnerCode,
                'partnerName' => 'E-Commerce Platform',
                'storeId' => 'storeMomo',
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => "Payment for {$payable->id}",
                'returnUrl' => $returnUrl,
                'notifyUrl' => $notifyUrl,
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature,
                'lang' => 'vi',
            ];

            $response = Http::timeout(30)->post($endpoint, $postData);

            if (!$response->successful()) {
                throw new Exception('MoMo API error: ' . $response->status());
            }

            $result = $response->json();

            if (($result['resultCode'] ?? null) != 0) {
                throw new Exception('MoMo payment creation failed: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('MoMo payment created', [
                'order_id' => $payable->id,
                'payment_id' => $payment->id,
                'momo_order_id' => $orderId,
            ]);

            return [
                'payment_url' => $result['payUrl'] ?? null,
                'request_id' => $requestId,
                'order_id' => $orderId,
                'qr_code' => $result['qrCodeUrl'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('MoMo payment creation failed', [
                'order_id' => $payable->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment callback from MoMo
     *
     * @param array $data
     * @return string|null Payment ID
     */
    public function handleCallback(array $data)
    {
        try {
            $secretKey = $this->config['secret_key'];

            // Extract signature before verification
            $signature = $data['signature'] ?? null;
            unset($data['signature']);

            // Sort and build raw signature string
            ksort($data);
            $rawSignature = '';
            foreach ($data as $key => $value) {
                $rawSignature .= $key . '=' . $value . '&';
            }
            $rawSignature = rtrim($rawSignature, '&');

            // Verify signature
            $computedSignature = hash_hmac('sha256', $rawSignature, $secretKey);

            if ($computedSignature !== $signature) {
                throw new Exception('Invalid MoMo signature');
            }

            $resultCode = $data['resultCode'] ?? null;

            if ($resultCode != 0) {
                throw new Exception("Payment failed with result code: {$resultCode}");
            }

            $transactionId = $data['transId'] ?? null;

            Log::info('MoMo callback processed successfully', [
                'order_id' => $data['orderId'] ?? null,
                'transaction_id' => $transactionId,
            ]);

            return $transactionId;
        } catch (Exception $e) {
            Log::error('MoMo callback error', [
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
            // MoMo verification - check payment status
            // In production, you might want to call MoMo's query API

            Log::info('MoMo payment verified', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);

            return $payment->status === 'completed';
        } catch (Exception $e) {
            Log::error('MoMo verification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
