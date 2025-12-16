<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;

class GsmApiService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.gsm.api_url');
        $this->apiKey = config('services.gsm.api_key');

        if (!$this->apiUrl || !$this->apiKey) {
            throw new Exception('GSM API credentials not configured');
        }
    }

    /**
     * Submit order to GSM API
     *
     * @param Ticket $ticket
     * @return array
     * @throws Exception
     */
    public function submitOrder(Ticket $ticket): array
    {
        try {
            // Prepare request data based on ticket input
            $requestData = $this->prepareRequestData($ticket);

            // Log API request
            $ticket->update([
                'api_request' => $requestData,
            ]);

            Log::info('GSM API Request', ['ticket_id' => $ticket->id, 'data' => $requestData]);

            // Make API call
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($this->apiUrl . '/submit', $requestData);

            if (!$response->successful()) {
                Log::error('GSM API Error', [
                    'ticket_id' => $ticket->id,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new Exception('GSM API Error: ' . $response->status());
            }

            $result = $response->json();

            Log::info('GSM API Response', ['ticket_id' => $ticket->id, 'result' => $result]);

            return [
                'order_id' => $result['order_id'] ?? $result['id'] ?? null,
                'status' => $result['status'] ?? 'pending',
                'result' => $result,
            ];
        } catch (Exception $e) {
            Log::error('GSM API Exception', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get order status from GSM API
     *
     * @param Ticket $ticket
     * @return array
     * @throws Exception
     */
    public function getOrderStatus(Ticket $ticket): array
    {
        try {
            if (!$ticket->api_order_id) {
                throw new Exception('No GSM order ID found for ticket');
            }

            Log::info('GSM API Status Check', ['ticket_id' => $ticket->id, 'order_id' => $ticket->api_order_id]);

            // Make API call to check status
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->get($this->apiUrl . '/status/' . $ticket->api_order_id);

            if (!$response->successful()) {
                Log::error('GSM Status Check Error', [
                    'ticket_id' => $ticket->id,
                    'order_id' => $ticket->api_order_id,
                    'status' => $response->status(),
                ]);
                throw new Exception('GSM Status Check Error: ' . $response->status());
            }

            $result = $response->json();

            Log::info('GSM Status Response', ['ticket_id' => $ticket->id, 'result' => $result]);

            // Map GSM status to our status
            $status = $this->mapStatus($result['status'] ?? 'pending');

            return [
                'order_id' => $ticket->api_order_id,
                'status' => $status,
                'result' => $result['result'] ?? $result,
                'message' => $result['message'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('GSM Status Check Exception', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Prepare request data for GSM API
     *
     * @param Ticket $ticket
     * @return array
     */
    protected function prepareRequestData(Ticket $ticket): array
    {
        $inputData = $ticket->input_data ?? [];

        return [
            'api_key' => $this->apiKey,
            'service_id' => $ticket->service_id,
            'ticket_number' => $ticket->ticket_number,
            'input_data' => $inputData,
            'webhook_url' => route('api.webhooks.gsm'),
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Map GSM status to internal status
     *
     * @param string $status
     * @return string
     */
    protected function mapStatus(string $status): string
    {
        $statusMap = [
            'pending' => 'processing',
            'processing' => 'processing',
            'in_progress' => 'processing',
            'completed' => 'completed',
            'done' => 'completed',
            'success' => 'completed',
            'failed' => 'failed',
            'error' => 'failed',
            'cancelled' => 'failed',
        ];

        return $statusMap[strtolower($status)] ?? 'processing';
    }

    /**
     * Process webhook callback from GSM
     *
     * @param array $data
     * @return bool
     */
    public function processWebhook(array $data): bool
    {
        try {
            $ticket = Ticket::where('api_order_id', $data['order_id'])
                ->orWhere('ticket_number', $data['ticket_number'])
                ->first();

            if (!$ticket) {
                Log::warning('GSM Webhook: Ticket not found', ['data' => $data]);
                return false;
            }

            $status = $this->mapStatus($data['status'] ?? 'pending');

            if ($status === 'completed') {
                $ticket->update([
                    'result' => $data['result'] ?? null,
                    'api_response' => $data,
                ]);
                $ticket->updateStatus('completed', 'Order completed via webhook', $data);
            } elseif ($status === 'failed') {
                $ticket->updateStatus('failed', 'Order failed: ' . ($data['message'] ?? ''), $data);
            }

            return true;
        } catch (Exception $e) {
            Log::error('GSM Webhook Processing Error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
