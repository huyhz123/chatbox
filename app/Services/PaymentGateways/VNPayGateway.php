<?php

namespace App\Services\PaymentGateways;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VNPayGateway
{
    protected $config;

    public function __construct()
    {
        $this->config = config('payment.gateways.vnpay');

        if (!$this->config['enabled']) {
            throw new Exception('VNPay gateway is not enabled');
        }
    }

    /**
     * Create a payment and return payment URL
     *
     * @param Model $payable (Transaction, GiftTransaction, etc.)
     * @param Payment $payment
     * @param array $data
     * @return array
     */
    public function createPayment(Model $payable, Payment $payment, array $data = [])
    {
        try {
            $amount = (int)($payment->amount * 100); // Convert to cents
            $tmnCode = $this->config['tmn_code'];
            $hashSecret = $this->config['hash_secret'];
            $returnUrl = $this->config['return_url'];
            $vnpUrl = $this->config['url'];

            $inputData = [
                'vnp_Version' => '2.1.0',
                'vnp_TmnCode' => $tmnCode,
                'vnp_Amount' => $amount,
                'vnp_Command' => 'pay',
                'vnp_CreateDate' => date('YmdHis'),
                'vnp_CurrCode' => 'VND',
                'vnp_IpAddr' => $this->getClientIp(),
                'vnp_Locale' => 'vn',
                'vnp_OrderInfo' => "Payment for {$payable->id}",
                'vnp_OrderType' => 'billpayment',
                'vnp_ReturnUrl' => $returnUrl,
                'vnp_TxnRef' => $payment->id . '-' . time(),
            ];

            ksort($inputData);

            $query = '';
            $i = 0;
            $hashData = '';
            foreach ($inputData as $key => $value) {
                if ($i == 1) $query .= '&';
                $query .= urlencode($key) . '=' . urlencode($value);
                if ($i == 0) $hashData .= urlencode($key) . '=' . urlencode($value);
                else $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }

            $vnp_HashSecret = $hashSecret;
            $vnpSecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
            $paymentUrl = $vnpUrl . '?' . $query . '&vnp_SecureHash=' . $vnpSecureHash;

            Log::info('VNPay payment created', [
                'order_id' => $payable->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
            ]);

            return [
                'payment_url' => $paymentUrl,
                'txn_ref' => $inputData['vnp_TxnRef'],
            ];
        } catch (Exception $e) {
            Log::error('VNPay payment creation failed', [
                'order_id' => $payable->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment callback from VNPay
     *
     * @param array $data
     * @return string|null Payment ID
     */
    public function handleCallback(array $data)
    {
        try {
            $vnp_SecureHash = $data['vnp_SecureHash'] ?? null;

            if (!$vnp_SecureHash) {
                throw new Exception('Missing secure hash in callback');
            }

            // Remove secure hash from data to verify
            unset($data['vnp_SecureHash']);
            unset($data['vnp_SecureHashType']);

            ksort($data);

            $hashData = '';
            $i = 0;
            foreach ($data as $key => $value) {
                if ($i == 1) $hashData .= '&';
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }

            $vnp_HashSecret = $this->config['hash_secret'];
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            if ($secureHash !== $vnp_SecureHash) {
                throw new Exception('Invalid secure hash');
            }

            $responseCode = $data['vnp_ResponseCode'] ?? '99';

            if ($responseCode != '00') {
                throw new Exception("Payment failed with response code: {$responseCode}");
            }

            $transactionId = $data['vnp_TransactionNo'] ?? null;

            Log::info('VNPay callback processed successfully', [
                'txn_ref' => $data['vnp_TxnRef'] ?? null,
                'transaction_id' => $transactionId,
            ]);

            return $transactionId;
        } catch (Exception $e) {
            Log::error('VNPay callback error', [
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
            // VNPay verification would typically involve querying their API
            // For now, we trust the callback which was already verified with secure hash

            Log::info('VNPay payment verified', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);

            return $payment->status === 'completed';
        } catch (Exception $e) {
            Log::error('VNPay verification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    protected function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
        return $ip;
    }
}
