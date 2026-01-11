<?php

namespace App\Services;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\Service;
use App\Models\File;
use Illuminate\Support\Facades\Http;

class ChatbotService
{
    protected $enabled;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->enabled = config('services.chatbot.enabled');
        $this->apiKey = config('services.chatbot.openai_key');
        $this->model = config('services.chatbot.model');
    }

    public function getOrCreateConversation($sessionId, $userId = null)
    {
        $conversation = ChatbotConversation::where('session_id', $sessionId)->first();

        if (!$conversation) {
            $conversation = ChatbotConversation::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'ip_address' => request()->ip(),
                'is_active' => true,
            ]);
        }

        return $conversation;
    }

    public function chat($sessionId, $message, $userId = null)
    {
        $conversation = $this->getOrCreateConversation($sessionId, $userId);

        // Add user message
        $conversation->addMessage('user', $message);

        // Check for simple queries first
        $response = $this->handleSimpleQueries($message);

        if (!$response && $this->enabled && $this->apiKey) {
            $response = $this->getChatGPTResponse($conversation, $message);
        }

        if (!$response) {
            $response = "Xin lỗi, tôi không hiểu câu hỏi của bạn. Vui lòng liên hệ hỗ trợ để được giúp đỡ.";
        }

        // Add assistant response
        $conversation->addMessage('assistant', $response);

        return $response;
    }

    protected function handleSimpleQueries($message)
    {
        $message = strtolower($message);

        // Services query
        if (str_contains($message, 'dịch vụ') || str_contains($message, 'service')) {
            $services = Service::active()->limit(5)->get(['name', 'price']);
            $response = "Đây là một số dịch vụ của chúng tôi:\n";
            foreach ($services as $service) {
                $response .= "- {$service->name}: " . number_format($service->price) . " VND\n";
            }
            return $response;
        }

        // Files/Courses query
        if (str_contains($message, 'file') || str_contains($message, 'tài liệu')) {
            $files = File::active()->limit(5)->get(['name', 'price']);
            $response = "Đây là một số file/tài liệu:\n";
            foreach ($files as $file) {
                $response .= "- {$file->name}: " . number_format($file->price) . " VND\n";
            }
            return $response;
        }

        // VIP packages query
        if (str_contains($message, 'vip') || str_contains($message, 'gói') || str_contains($message, 'package')) {
            $response = "Đây là các gói VIP của chúng tôi:\n";
            $response .= "- VIP 1 tháng: 99,000 VND\n";
            $response .= "- VIP 3 tháng: 249,000 VND\n";
            $response .= "- VIP 12 tháng: 799,000 VND\n";
            return $response;
        }

        // Pricing query
        if (str_contains($message, 'giá') || str_contains($message, 'price') || str_contains($message, 'cost')) {
            return "Giá của các sản phẩm/dịch vụ khác nhau. Vui lòng xem chi tiết trên website hoặc hỏi về sản phẩm cụ thể.";
        }

        // Support query
        if (str_contains($message, 'hỗ trợ') || str_contains($message, 'support') || str_contains($message, 'help')) {
            return "Chúng tôi luôn sẵn sàng hỗ trợ bạn! Bạn có thể:\n- Gửi email: support@example.com\n- Hotline: 1900-xxxx\n- Hoặc tạo ticket trên website";
        }

        return null;
    }

    protected function getChatGPTResponse($conversation, $message)
    {
        try {
            $messages = $this->buildChatHistory($conversation);
            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('ChatGPT Error: ' . $e->getMessage());
            return null;
        }
    }

    protected function buildChatHistory($conversation)
    {
        $systemPrompt = "Bạn là trợ lý AI cho website bán dịch vụ sửa chữa, file và khóa học. Hãy trả lời câu hỏi của khách hàng một cách lịch sự và chuyên nghiệp.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Get recent messages (last 10)
        $recentMessages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse();

        foreach ($recentMessages as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->message
            ];
        }

        return $messages;
    }
}
