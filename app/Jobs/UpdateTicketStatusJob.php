<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateTicketStatusJob implements ShouldQueue
{
    use Queueable;

    private int $maxAttempts = 5;
    private int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Ticket $ticket,
        private string $status,
        private ?string $message = null,
        private ?array $apiResponse = null
    ) {
        $this->onQueue('tickets');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Updating ticket status started', [
                'ticket_id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'new_status' => $this->status,
                'attempt' => $this->attempts(),
            ]);

            // Update ticket status
            $this->ticket->updateStatus(
                $this->status,
                $this->message ?? "Status updated to {$this->status}",
                $this->apiResponse
            );

            // Update API response if provided
            if ($this->apiResponse !== null) {
                $this->ticket->update([
                    'api_response' => $this->apiResponse,
                ]);
            }

            // Update timestamp based on status
            if ($this->status === 'completed') {
                $this->ticket->update(['completed_at' => now()]);
            }

            Log::info('Ticket status updated successfully', [
                'ticket_id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'status' => $this->status,
            ]);
        } catch (Exception $exception) {
            Log::error('Error updating ticket status', [
                'ticket_id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'status' => $this->status,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->maxAttempts) {
                Log::critical('Ticket status update failed permanently', [
                    'ticket_id' => $this->ticket->id,
                    'ticket_number' => $this->ticket->ticket_number,
                ]);
                $this->fail($exception);
            } else {
                $this->release($this->backoff);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::critical('UpdateTicketStatusJob failed permanently', [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'status' => $this->status,
            'exception' => $exception->getMessage(),
        ]);
    }
}
