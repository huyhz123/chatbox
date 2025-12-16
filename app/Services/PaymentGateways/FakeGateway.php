<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FakeGateway
{
    protected $config;

    public function __construct()
    {
        $this->config = config('payment.gateways.fake');

        if (!$this->config['enabled']) {
            throw new Exception('Fake gateway is not enabled');
        }
    }

    /**
     * Create a fake payment and return test data
     * Used for sandbox testing without actual payment processing
     *
     * @param Order $order
     * @param Payment $payment
     * @param array $data
     * @return array
     */
    public function createPayment(Order $order, Payment $payment, array $data = [])
    {
        try {
            $fakeTransactionId = 'FAKE-' . Str::uuid();
            $testLink = route('payment.test-complete', [
                'payment' => $payment->id,
                'transaction_id' => $fakeTransactionId,
            ]);

            Log::info('Fake payment created', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'fake_transaction_id' => $fakeTransactionId,
            ]);

            return [
                'payment_url' => $testLink,
                'test_mode' => true,
                'transaction_id' => $fakeTransactionId,
                'instructions' => 'This is a test payment. Click the link above to simulate payment completion.',
                'test_scenarios' => [
                    'success' => $testLink . '?status=success',
                    'pending' => $testLink . '?status=pending',
                    'failed' => $testLink . '?status=failed',
                ],
            ];
        } catch (Exception $e) {
            Log::error('Fake payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle fake payment callback for testing
     *
     * @param array $data
     * @return string|null Payment ID
     */
    public function handleCallback(array $data)
    {
        try {
            $status = $data['status'] ?? 'success';
            $transactionId = $data['transaction_id'] ?? 'FAKE-' . Str::uuid();

            // Simulate different payment scenarios
            switch ($status) {
                case 'success':
                case 'completed':
                    Log::info('Fake payment callback - Success', [
                        'transaction_id' => $transactionId,
                        'status' => $status,
                    ]);
                    return $transactionId;

                case 'pending':
                    Log::info('Fake payment callback - Pending', [
                        'transaction_id' => $transactionId,
                    ]);
                    throw new Exception('Payment is pending');

                case 'failed':
                case 'cancelled':
                    Log::warning('Fake payment callback - Failed', [
                        'transaction_id' => $transactionId,
                        'status' => $status,
                    ]);
                    throw new Exception("Payment {$status}");

                default:
                    Log::info('Fake payment callback - Default Success', [
                        'transaction_id' => $transactionId,
                    ]);
                    return $transactionId;
            }
        } catch (Exception $e) {
            Log::error('Fake payment callback error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Verify fake payment status
     *
     * @param Payment $payment
     * @return bool
     */
    public function verifyPayment(Payment $payment)
    {
        try {
            // For fake gateway, just check if payment is completed
            $isVerified = $payment->status === 'completed';

            Log::info('Fake payment verified', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'verified' => $isVerified,
            ]);

            return $isVerified;
        } catch (Exception $e) {
            Log::error('Fake payment verification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Test payment success (for manual testing)
     *
     * @param Payment $payment
     * @return bool
     */
    public function testPaymentSuccess(Payment $payment)
    {
        try {
            $payment->markAsCompleted(
                'FAKE-' . Str::uuid(),
                ['test_mode' => true, 'scenario' => 'success']
            );

            Log::info('Fake payment marked as success for testing', [
                'payment_id' => $payment->id,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Fake payment test failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Test payment failure (for manual testing)
     *
     * @param Payment $payment
     * @param string $reason
     * @return bool
     */
    public function testPaymentFailure(Payment $payment, $reason = 'Test failure')
    {
        try {
            $payment->markAsFailed(
                $reason,
                ['test_mode' => true, 'scenario' => 'failure']
            );

            Log::warning('Fake payment marked as failed for testing', [
                'payment_id' => $payment->id,
                'reason' => $reason,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Fake payment failure test failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get test payment data
     *
     * @param Order $order
     * @param Payment $payment
     * @return array
     */
    public function getTestData(Order $order, Payment $payment)
    {
        return [
            'test_mode' => true,
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'scenarios' => [
                'success' => [
                    'description' => 'Simulate successful payment',
                    'endpoint' => route('payment.test-complete'),
                    'params' => [
                        'payment_id' => $payment->id,
                        'status' => 'success',
                    ],
                ],
                'pending' => [
                    'description' => 'Simulate pending payment',
                    'endpoint' => route('payment.test-complete'),
                    'params' => [
                        'payment_id' => $payment->id,
                        'status' => 'pending',
                    ],
                ],
                'failed' => [
                    'description' => 'Simulate failed payment',
                    'endpoint' => route('payment.test-complete'),
                    'params' => [
                        'payment_id' => $payment->id,
                        'status' => 'failed',
                    ],
                ],
            ],
        ];
    }
}
