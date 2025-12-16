<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessTicketJob implements ShouldQueue
{
    use Queueable;

    private int $maxAttempts = 3;
    private int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Ticket $ticket,
        private ?array $additionalData = null
    ) {
        $this->onQueue('tickets');
    }

    /**
     * Execute the job.
     */
    public function handle(TicketService $ticketService): void
    {
        try {
            Log::info('Processing ticket started', [
                'ticket_id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'attempt' => $this->attempts(),
            ]);

            // Update ticket status to processing
            $this->ticket->updateStatus('processing', 'Job processing started');

            // Call the service to process the ticket
            $result = $ticketService->processTicket(
                $this->ticket,
                $this->additionalData ?? []
            );

            // Update ticket with API response
            $this->ticket->update([
                'api_response' => $result,
                'status' => 'completed',
            ]);

            $this->ticket->updateStatus('completed', 'Ticket processed successfully', $result);

            Log::info('Ticket processed successfully', [
                'ticket_id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
            ]);
        } catch (Exception $exception) {
            Log::error('Error processing ticket', [
                'ticket_id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Mark as failed if max attempts reached
            if ($this->attempts() >= $this->maxAttempts) {
                $this->ticket->updateStatus('failed', 'Processing failed after ' . $this->maxAttempts . ' attempts', [
                    'error' => $exception->getMessage(),
                ]);
                Log::critical('Ticket processing failed permanently', [
                    'ticket_id' => $this->ticket->id,
                    'ticket_number' => $this->ticket->ticket_number,
                ]);
                $this->fail($exception);
            } else {
                // Retry the job
                $this->release($this->backoff);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::critical('ProcessTicketJob failed permanently', [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'exception' => $exception->getMessage(),
        ]);

        $this->ticket->updateStatus('failed', 'Job processing failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
