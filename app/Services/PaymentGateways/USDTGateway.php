<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class USDTGateway
{
    protected $config;

    public function __construct()
    {
        $this->config = config('payment.gateways.usdt');

        if (!$this->config['enabled']) {
            throw new Exception('USDT gateway is not enabled');
        }
    }

    /**
     * Create a payment and return USDT wallet address and amount
     *
     * @param Order $order
     * @param Payment $payment
     * @param array $data
     * @return array
     */
    public function createPayment(Order $order, Payment $payment, array $data = [])
    {
        try {
            $walletAddress = $this->config['wallet_address'];
            $network = $this->config['network'] ?? 'TRC20';
            $apiKey = $this->config['api_key'];

            if (!$walletAddress || !$apiKey) {
                throw new Exception('Missing USDT configuration');
            }

            // Convert payment amount to USDT (assuming 1 USDT = 1 USD)
            $usdtAmount = $this->convertToUSDT($payment->amount, $payment->currency);
            $paymentAddress = $this->generateUniqueAddress($walletAddress, $payment->id, $network);

            // Store payment metadata
            $metadata = [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount_usdt' => $usdtAmount,
                'network' => $network,
                'wallet_address' => $paymentAddress,
                'created_at' => now()->toDateTimeString(),
                'expires_at' => now()->addHours(24)->toDateTimeString(),
            ];

            Log::info('USDT payment created', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount_usdt' => $usdtAmount,
                'network' => $network,
            ]);

            return [
                'wallet_address' => $paymentAddress,
                'amount' => $usdtAmount,
                'network' => $network,
                'currency' => 'USDT',
                'memo' => $payment->id,
                'metadata' => $metadata,
            ];
        } catch (Exception $e) {
            Log::error('USDT payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment callback from USDT blockchain
     *
     * @param array $data
     * @return string|null Payment ID
     */
    public function handleCallback(array $data)
    {
        try {
            $txHash = $data['tx_hash'] ?? null;
            $fromAddress = $data['from_address'] ?? null;
            $toAddress = $data['to_address'] ?? null;
            $amount = $data['amount'] ?? null;
            $network = $data['network'] ?? null;
            $signature = $data['signature'] ?? null;

            if (!$txHash || !$fromAddress || !$toAddress || !$amount) {
                throw new Exception('Missing required callback data');
            }

            // Verify signature (if provided by webhook provider)
            if ($signature) {
                if (!$this->verifySignature($data, $signature)) {
                    throw new Exception('Invalid callback signature');
                }
            }

            // Verify the transaction on blockchain
            if (!$this->verifyTransactionOnBlockchain($txHash, $toAddress, $amount, $network)) {
                throw new Exception('Transaction not verified on blockchain');
            }

            Log::info('USDT callback processed successfully', [
                'tx_hash' => $txHash,
                'from_address' => $fromAddress,
                'amount' => $amount,
                'network' => $network,
            ]);

            return $txHash;
        } catch (Exception $e) {
            Log::error('USDT callback error', [
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
            $transactionId = $payment->transaction_id;
            $responseData = $payment->response_data ?? [];

            if (!$transactionId) {
                return false;
            }

            $network = $responseData['network'] ?? $this->config['network'] ?? 'TRC20';

            // Verify transaction on blockchain
            if ($this->verifyTransactionOnBlockchain(
                $transactionId,
                $responseData['to_address'] ?? null,
                $responseData['amount'] ?? null,
                $network
            )) {
                Log::info('USDT payment verified', [
                    'payment_id' => $payment->id,
                    'tx_hash' => $transactionId,
                ]);

                return true;
            }

            Log::warning('USDT payment not verified on blockchain', [
                'payment_id' => $payment->id,
                'tx_hash' => $transactionId,
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('USDT verification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Convert amount to USDT based on currency
     *
     * @param float $amount
     * @param string $currency
     * @return float
     */
    protected function convertToUSDT($amount, $currency)
    {
        $currencies = config('payment.currencies');

        if ($currency === 'USDT') {
            return $amount;
        }

        if (!isset($currencies[$currency])) {
            return $amount;
        }

        // Convert through base currency
        $baseAmount = $amount / $currencies[$currency]['rate'];
        return $baseAmount * ($currencies['USDT']['rate'] ?? 0.000041);
    }

    /**
     * Generate unique payment address for this payment
     *
     * @param string $baseAddress
     * @param int $paymentId
     * @param string $network
     * @return string
     */
    protected function generateUniqueAddress($baseAddress, $paymentId, $network)
    {
        // In a real implementation, you might generate a unique receiving address
        // or use a payment gateway API to create one
        // For now, we return the configured wallet address with memo for identification
        return $baseAddress;
    }

    /**
     * Verify signature from webhook
     *
     * @param array $data
     * @param string $signature
     * @return bool
     */
    protected function verifySignature($data, $signature)
    {
        try {
            // Remove signature from data
            $dataCopy = $data;
            unset($dataCopy['signature']);

            // Create a string from sorted data
            ksort($dataCopy);
            $signatureString = http_build_query($dataCopy);

            // Verify signature using API key as secret
            $computedSignature = hash_hmac('sha256', $signatureString, $this->config['api_key']);

            return $computedSignature === $signature;
        } catch (Exception $e) {
            Log::error('Signature verification error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Verify transaction on blockchain
     *
     * @param string $txHash
     * @param string $toAddress
     * @param float $amount
     * @param string $network
     * @return bool
     */
    protected function verifyTransactionOnBlockchain($txHash, $toAddress, $amount, $network)
    {
        try {
            // This is a simplified implementation
            // In production, you would call blockchain explorers or APIs like:
            // - Tron Grid API for TRC20
            // - Ethereum RPC for ERC20
            // - etc.

            if ($network === 'TRC20') {
                return $this->verifyTronTransaction($txHash, $toAddress, $amount);
            } elseif ($network === 'ERC20') {
                return $this->verifyEthereumTransaction($txHash, $toAddress, $amount);
            }

            Log::warning('Unsupported network for verification', ['network' => $network]);
            return false;
        } catch (Exception $e) {
            Log::error('Blockchain verification error', [
                'tx_hash' => $txHash,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verify TRON (TRC20) transaction
     *
     * @param string $txHash
     * @param string $toAddress
     * @param float $amount
     * @return bool
     */
    protected function verifyTronTransaction($txHash, $toAddress, $amount)
    {
        try {
            // Example: Query Tron Grid API
            // In production, use a real TRON API endpoint
            $response = Http::timeout(10)->get('https://api.tronstack.io/v1/transaction/' . $txHash);

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();

            // Verify transaction details
            // This is simplified - actual verification would be more complex
            return !empty($data);
        } catch (Exception $e) {
            Log::error('TRON verification error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Verify Ethereum (ERC20) transaction
     *
     * @param string $txHash
     * @param string $toAddress
     * @param float $amount
     * @return bool
     */
    protected function verifyEthereumTransaction($txHash, $toAddress, $amount)
    {
        try {
            // Example: Query Ethereum RPC or blockchain API
            // In production, use Infura, Alchemy, or similar service

            // This is simplified - actual verification would need proper RPC calls
            return !empty($txHash) && !empty($toAddress);
        } catch (Exception $e) {
            Log::error('Ethereum verification error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
