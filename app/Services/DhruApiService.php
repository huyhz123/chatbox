<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;

class DhruApiService
{
    protected $apiUrl;
    protected $apiKey;
    protected $username;

    public function __construct()
    {
        $this->apiUrl = config('services.dhru.api_url');
        $this->apiKey = config('services.dhru.api_key');
        $this->username = config('services.dhru.username');

        if (!$this->apiUrl || !$this->apiKey) {
            throw new Exception('DHRU API credentials not configured');
        }
    }

    /**
     * Submit order to DHRU Fusion API
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

            Log::info('DHRU API Request', ['ticket_id' => $ticket->id, 'data' => $requestData]);

            // Make API call
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($this->apiUrl . '/orders/submit', $requestData);

            if (!$response->successful()) {
                Log::error('DHRU API Error', [
                    'ticket_id' => $ticket->id,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new Exception('DHRU API Error: ' . $response->status());
            }

            $result = $response->json();

            Log::info('DHRU API Response', ['ticket_id' => $ticket->id, 'result' => $result]);

            return [
                'order_id' => $result['order_id'] ?? $result['id'] ?? null,
                'status' => $result['status'] ?? 'pending',
                'result' => $result,
            ];
        } catch (Exception $e) {
            Log::error('DHRU API Exception', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get order status from DHRU Fusion API
     *
     * @param Ticket $ticket
     * @return array
     * @throws Exception
     */
    public function getOrderStatus(Ticket $ticket): array
    {
        try {
            if (!$ticket->api_order_id) {
                throw new Exception('No DHRU order ID found for ticket');
            }

            Log::info('DHRU API Status Check', ['ticket_id' => $ticket->id, 'order_id' => $ticket->api_order_id]);

            // Make API call to check status
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->get($this->apiUrl . '/orders/' . $ticket->api_order_id . '/status');

            if (!$response->successful()) {
                Log::error('DHRU Status Check Error', [
                    'ticket_id' => $ticket->id,
                    'order_id' => $ticket->api_order_id,
                    'status' => $response->status(),
                ]);
                throw new Exception('DHRU Status Check Error: ' . $response->status());
            }

            $result = $response->json();

            Log::info('DHRU Status Response', ['ticket_id' => $ticket->id, 'result' => $result]);

            // Map DHRU status to our status
            $status = $this->mapStatus($result['status'] ?? 'pending');

            return [
                'order_id' => $ticket->api_order_id,
                'status' => $status,
                'result' => $result['result'] ?? $result,
                'message' => $result['message'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('DHRU Status Check Exception', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Prepare request data for DHRU API
     *
     * @param Ticket $ticket
     * @return array
     */
    protected function prepareRequestData(Ticket $ticket): array
    {
        $inputData = $ticket->input_data ?? [];

        return [
            'username' => $this->username,
            'api_key' => $this->apiKey,
            'service_id' => $ticket->service_id,
            'ticket_id' => $ticket->ticket_number,
            'data' => $inputData,
            'callback_url' => route('api.webhooks.dhru'),
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Map DHRU status to internal status
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
     * Process webhook callback from DHRU
     *
     * @param array $data
     * @return bool
     */
    public function processWebhook(array $data): bool
    {
        try {
            $ticket = Ticket::where('api_order_id', $data['order_id'])
                ->orWhere('ticket_number', $data['ticket_id'])
                ->first();

            if (!$ticket) {
                Log::warning('DHRU Webhook: Ticket not found', ['data' => $data]);
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
            Log::error('DHRU Webhook Processing Error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
