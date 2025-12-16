<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Stripe webhook
     */
    public function stripe(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('Stripe-Signature');

        Log::info('Stripe Webhook Received', $payload);

        try {
            // Verify webhook signature
            // $event = \Stripe\Webhook::constructEvent($payload, $signature, config('services.stripe.webhook_secret'));

            // Handle different event types
            $eventType = $payload['type'] ?? null;

            switch ($eventType) {
                case 'payment_intent.succeeded':
                    $this->handleStripePaymentSuccess($payload);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handleStripePaymentFailure($payload);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event', ['type' => $eventType]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle payment status webhook
     */
    public function paymentStatus(Request $request)
    {
        $payload = $request->all();
        Log::info('Payment Status Webhook Received', $payload);

        try {
            $paymentId = $payload['payment_id'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$paymentId || !$status) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $payment = Payment::where('transaction_id', $paymentId)->first();

            if (!$payment) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            $payment->update(['status' => $status]);

            // Update order if payment is completed
            if ($status === 'completed') {
                $payment->order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Payment Status Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle DHRU webhook
     */
    public function dhru(Request $request)
    {
        $payload = $request->all();
        Log::info('DHRU Webhook Received', $payload);

        try {
            $ticketId = $payload['ticket_id'] ?? null;
            $status = $payload['status'] ?? null;
            $response = $payload['response'] ?? null;

            if (!$ticketId) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $ticket = Ticket::where('external_ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(['error' => 'Ticket not found'], 404);
            }

            $ticket->update([
                'status' => $status,
                'response' => $response,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('DHRU Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle GSM webhook
     */
    public function gsm(Request $request)
    {
        $payload = $request->all();
        Log::info('GSM Webhook Received', $payload);

        try {
            $ticketId = $payload['ticket_id'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$ticketId) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $ticket = Ticket::where('external_ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(['error' => 'Ticket not found'], 404);
            }

            $ticket->update([
                'status' => $status,
                'response' => $payload['response'] ?? null,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('GSM Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generic webhook handler
     */
    public function handle(Request $request, $provider)
    {
        $payload = $request->all();
        Log::info("Webhook Received from {$provider}", $payload);

        try {
            // Handle generic webhook
            return response()->json([
                'success' => true,
                'provider' => $provider,
                'message' => 'Webhook received',
            ]);

        } catch (\Exception $e) {
            Log::error("Webhook Error ({$provider}): " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Handle Stripe payment success
     */
    protected function handleStripePaymentSuccess($payload)
    {
        $paymentIntentId = $payload['data']['object']['id'] ?? null;

        if ($paymentIntentId) {
            $payment = Payment::where('transaction_id', $paymentIntentId)->first();

            if ($payment) {
                $payment->update(['status' => 'completed']);
                $payment->order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);
            }
        }
    }

    /**
     * Helper: Handle Stripe payment failure
     */
    protected function handleStripePaymentFailure($payload)
    {
        $paymentIntentId = $payload['data']['object']['id'] ?? null;

        if ($paymentIntentId) {
            $payment = Payment::where('transaction_id', $paymentIntentId)->first();

            if ($payment) {
                $payment->update(['status' => 'failed']);
            }
        }
    }
}
