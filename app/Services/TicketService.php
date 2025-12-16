<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Service;
use App\Models\Ticket;
use App\Jobs\ProcessTicketJob;
use Illuminate\Support\Str;

class TicketService
{
    public function createTicket(Order $order, Service $service, array $inputData = [])
    {
        $ticket = Ticket::create([
            'ticket_number' => $this->generateTicketNumber(),
            'user_id' => $order->user_id,
            'service_id' => $service->id,
            'order_id' => $order->id,
            'status' => 'pending',
            'api_provider' => $service->api_provider,
            'input_data' => $inputData,
            'submitted_at' => now(),
        ]);

        $ticket->updateStatus('pending', 'Ticket created');

        // Dispatch job to process ticket
        if ($service->api_provider !== 'manual') {
            ProcessTicketJob::dispatch($ticket)->delay(now()->addSeconds(5));
        }

        // Send notification
        app(NotificationService::class)->sendTicketCreated($ticket);

        return $ticket;
    }

    public function processTicket(Ticket $ticket)
    {
        $service = $ticket->service;

        $ticket->updateStatus('processing', 'Processing ticket via ' . $service->api_provider);

        try {
            if ($service->api_provider === 'dhru') {
                $result = app(DhruApiService::class)->submitOrder($ticket);
            } elseif ($service->api_provider === 'gsm') {
                $result = app(GsmApiService::class)->submitOrder($ticket);
            } else {
                throw new \Exception('Manual processing required');
            }

            $ticket->update([
                'api_order_id' => $result['order_id'] ?? null,
                'api_response' => $result,
            ]);

            // Check status
            $this->updateTicketFromApi($ticket);

            return true;
        } catch (\Exception $e) {
            $ticket->updateStatus('failed', 'Error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateTicketFromApi(Ticket $ticket)
    {
        if (!$ticket->api_order_id) {
            return false;
        }

        try {
            if ($ticket->api_provider === 'dhru') {
                $status = app(DhruApiService::class)->getOrderStatus($ticket);
            } elseif ($ticket->api_provider === 'gsm') {
                $status = app(GsmApiService::class)->getOrderStatus($ticket);
            } else {
                return false;
            }

            if ($status['status'] === 'completed') {
                $ticket->update([
                    'result' => $status['result'] ?? null,
                    'api_response' => $status,
                ]);
                $ticket->updateStatus('completed', 'Order completed', $status);

                // Increment service sold count
                $ticket->service->incrementSoldCount();

                // Send notification
                app(NotificationService::class)->sendTicketCompleted($ticket);
            } elseif ($status['status'] === 'failed') {
                $ticket->updateStatus('failed', 'Order failed: ' . ($status['message'] ?? ''), $status);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function generateTicketNumber()
    {
        return 'TK' . date('Ymd') . strtoupper(Str::random(6));
    }
}
