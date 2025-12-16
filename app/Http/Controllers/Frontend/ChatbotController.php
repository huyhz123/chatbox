<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Get or create conversation
     */
    public function conversation(Request $request): JsonResponse
    {
        $user = auth()->user();

        // If no conversation ID provided, create a new one
        $conversationId = $request->input('conversation_id');

        if ($conversationId) {
            $conversation = ChatbotConversation::find($conversationId);

            if (!$conversation || ($user && $conversation->user_id !== $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ], 404);
            }
        } else {
            // Create new conversation
            $conversation = ChatbotConversation::create([
                'user_id' => $user?->id,
                'session_id' => session()->getId(),
            ]);
        }

        // Get conversation history
        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Send message to chatbot
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|exists:chatbot_conversations,id',
            'message' => 'required|string|max:5000',
        ]);

        $conversation = ChatbotConversation::find($request->conversation_id);
        $user = auth()->user();

        // Verify access to conversation
        if ($user && $conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            // Save user message
            ChatbotMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => 'user',
                'message' => $request->message,
            ]);

            // Get bot response
            $botResponse = $this->chatbotService->processMessage(
                $conversation,
                $request->message,
                $user
            );

            // Save bot message
            ChatbotMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => 'bot',
                'message' => $botResponse,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent',
                'response' => $botResponse,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process message: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get conversation messages
     */
    public function getMessages(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|exists:chatbot_conversations,id',
        ]);

        $conversation = ChatbotConversation::find($request->conversation_id);
        $user = auth()->user();

        // Verify access to conversation
        if ($user && $conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Clear conversation
     */
    public function clearConversation(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|exists:chatbot_conversations,id',
        ]);

        $conversation = ChatbotConversation::find($request->conversation_id);
        $user = auth()->user();

        // Verify access to conversation
        if ($user && $conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $conversation->messages()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversation cleared',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear conversation: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete conversation
     */
    public function deleteConversation(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|exists:chatbot_conversations,id',
        ]);

        $conversation = ChatbotConversation::find($request->conversation_id);
        $user = auth()->user();

        // Verify access to conversation
        if ($user && $conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $conversation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversation deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete conversation: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get bot suggestions
     */
    public function suggestions(): JsonResponse
    {
        try {
            $suggestions = $this->chatbotService->getDefaultSuggestions();

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get FAQ
     */
    public function faq(): JsonResponse
    {
        try {
            $faq = $this->chatbotService->getFAQ();

            return response()->json([
                'success' => true,
                'faq' => $faq,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get FAQ: ' . $e->getMessage(),
            ], 422);
        }
    }
}
