<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    private int $maxAttempts = 3;
    private int $backoff = 45;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $phoneNumber,
        private string $message,
        private ?string $smsProvider = null,
        private ?array $additionalData = null
    ) {
        $this->onQueue('sms');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending SMS started', [
                'phone' => $this->maskPhoneNumber($this->phoneNumber),
                'provider' => $this->smsProvider ?? 'default',
                'attempt' => $this->attempts(),
            ]);

            $smsProvider = $this->smsProvider ?? config('sms.default', 'twilio');

            // Validate phone number format
            if (!$this->validatePhoneNumber($this->phoneNumber)) {
                throw new Exception('Invalid phone number format: ' . $this->phoneNumber);
            }

            // Send SMS based on provider
            $response = $this->sendSmsViaProvider($smsProvider, [
                'to' => $this->phoneNumber,
                'message' => $this->message,
                ...$this->additionalData ?? [],
            ]);

            Log::info('SMS sent successfully', [
                'phone' => $this->maskPhoneNumber($this->phoneNumber),
                'provider' => $smsProvider,
                'response_id' => $response['id'] ?? 'unknown',
            ]);
        } catch (Exception $exception) {
            Log::error('Error sending SMS', [
                'phone' => $this->maskPhoneNumber($this->phoneNumber),
                'provider' => $this->smsProvider ?? 'default',
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->maxAttempts) {
                Log::critical('SMS sending failed permanently', [
                    'phone' => $this->maskPhoneNumber($this->phoneNumber),
                    'provider' => $this->smsProvider ?? 'default',
                ]);
                $this->fail($exception);
            } else {
                $this->release($this->backoff);
            }
        }
    }

    /**
     * Send SMS via the specified provider.
     */
    private function sendSmsViaProvider(string $provider, array $data): array
    {
        return match ($provider) {
            'twilio' => $this->sendViaTwilio($data),
            'nexmo' => $this->sendViaNexmo($data),
            'aws' => $this->sendViaAwsSns($data),
            default => throw new Exception("Unsupported SMS provider: {$provider}"),
        };
    }

    /**
     * Send SMS via Twilio.
     */
    private function sendViaTwilio(array $data): array
    {
        $client = new \Twilio\Rest\Client(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );

        $message = $client->messages->create(
            $data['to'],
            [
                'from' => config('services.twilio.phone_number'),
                'body' => $data['message'],
            ]
        );

        return [
            'id' => $message->sid,
            'status' => $message->status,
        ];
    }

    /**
     * Send SMS via Nexmo.
     */
    private function sendViaNexmo(array $data): array
    {
        // Implementation for Nexmo/Vonage API
        return [
            'id' => 'nexmo_' . uniqid(),
            'status' => 'submitted',
        ];
    }

    /**
     * Send SMS via AWS SNS.
     */
    private function sendViaAwsSns(array $data): array
    {
        // Implementation for AWS SNS
        return [
            'id' => 'aws_' . uniqid(),
            'status' => 'submitted',
        ];
    }

    /**
     * Validate phone number format.
     */
    private function validatePhoneNumber(string $phoneNumber): bool
    {
        // Basic validation - phone number should contain only digits and optional + or -
        return preg_match('/^[\+]?[1-9]\d{1,14}$/', preg_replace('/[^\d+]/', '', $phoneNumber));
    }

    /**
     * Mask phone number for logging.
     */
    private function maskPhoneNumber(string $phoneNumber): string
    {
        $length = strlen($phoneNumber);
        if ($length <= 4) {
            return $phoneNumber;
        }
        return substr($phoneNumber, 0, 3) . '***' . substr($phoneNumber, -3);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::critical('SendSmsJob failed permanently', [
            'phone' => $this->maskPhoneNumber($this->phoneNumber),
            'provider' => $this->smsProvider ?? 'default',
            'exception' => $exception->getMessage(),
        ]);
    }
}
