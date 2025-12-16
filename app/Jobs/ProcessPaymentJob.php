<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessPaymentJob implements ShouldQueue
{
    use Queueable;

    private int $maxAttempts = 3;
    private int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Payment $payment,
        private array $callbackData
    ) {
        $this->onQueue('payments');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        try {
            Log::info('Processing payment callback started', [
                'payment_id' => $this->payment->id,
                'order_id' => $this->payment->order_id,
                'amount' => $this->payment->amount,
                'attempt' => $this->attempts(),
            ]);

            // Validate callback data
            if (!$this->validateCallbackData($this->callbackData)) {
                throw new Exception('Invalid payment callback data');
            }

            // Update payment status
            $this->payment->update([
                'status' => 'completed',
                'transaction_id' => $this->callbackData['transaction_id'] ?? null,
                'response_data' => $this->callbackData,
                'processed_at' => now(),
            ]);

            // Get associated order
            $order = $this->payment->order();
            if (!$order) {
                throw new Exception('Associated order not found for payment');
            }

            // Update order status
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            // Trigger order processing
            if ($order->status === 'pending') {
                $order->update(['status' => 'confirmed']);
            }

            // Log activity
            $order->refresh();
            activity()
                ->performedOn($order)
                ->withProperties([
                    'payment_id' => $this->payment->id,
                    'amount' => $this->payment->amount,
                ])
                ->log('order_paid');

            DB::commit();

            Log::info('Payment processed successfully', [
                'payment_id' => $this->payment->id,
                'order_id' => $this->payment->order_id,
                'transaction_id' => $this->callbackData['transaction_id'] ?? 'unknown',
            ]);
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('Error processing payment callback', [
                'payment_id' => $this->payment->id,
                'order_id' => $this->payment->order_id,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->maxAttempts) {
                // Mark payment as failed
                $this->payment->update([
                    'status' => 'failed',
                    'response_data' => [
                        'error' => $exception->getMessage(),
                        'attempts' => $this->maxAttempts,
                    ],
                ]);

                Log::critical('Payment processing failed permanently', [
                    'payment_id' => $this->payment->id,
                    'order_id' => $this->payment->order_id,
                ]);

                $this->fail($exception);
            } else {
                $this->release($this->backoff);
            }
        }
    }

    /**
     * Validate payment callback data.
     */
    private function validateCallbackData(array $data): bool
    {
        // Validate required fields
        $requiredFields = ['status', 'amount'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }

        // Validate amount matches
        if ((float) $data['amount'] !== (float) $this->payment->amount) {
            Log::warning('Payment amount mismatch', [
                'payment_id' => $this->payment->id,
                'expected' => $this->payment->amount,
                'received' => $data['amount'],
            ]);
            return false;
        }

        return true;
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::critical('ProcessPaymentJob failed permanently', [
            'payment_id' => $this->payment->id,
            'order_id' => $this->payment->order_id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
