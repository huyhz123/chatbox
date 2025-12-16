<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendEmailJob implements ShouldQueue
{
    use Queueable;

    private int $maxAttempts = 3;
    private int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $recipientEmail,
        private string $subject,
        private string $templateView,
        private array $templateData = [],
        private ?string $recipientName = null,
        private ?array $attachments = null
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending email started', [
                'recipient' => $this->recipientEmail,
                'subject' => $this->subject,
                'template' => $this->templateView,
                'attempt' => $this->attempts(),
            ]);

            // Build mailable
            $mailable = new \Illuminate\Mail\Message();
            $mailable->from(config('mail.from.address'), config('mail.from.name'))
                ->to($this->recipientEmail, $this->recipientName)
                ->subject($this->subject)
                ->view($this->templateView, $this->templateData);

            // Add attachments if provided
            if (!empty($this->attachments)) {
                foreach ($this->attachments as $attachment) {
                    if (is_array($attachment)) {
                        $mailable->attach($attachment['path'], $attachment['options'] ?? []);
                    } else {
                        $mailable->attach($attachment);
                    }
                }
            }

            // Send email
            Mail::send($mailable);

            Log::info('Email sent successfully', [
                'recipient' => $this->recipientEmail,
                'subject' => $this->subject,
            ]);
        } catch (Exception $exception) {
            Log::error('Error sending email', [
                'recipient' => $this->recipientEmail,
                'subject' => $this->subject,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->maxAttempts) {
                Log::critical('Email sending failed permanently', [
                    'recipient' => $this->recipientEmail,
                    'subject' => $this->subject,
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
        Log::critical('SendEmailJob failed permanently', [
            'recipient' => $this->recipientEmail,
            'subject' => $this->subject,
            'exception' => $exception->getMessage(),
        ]);
    }
}
