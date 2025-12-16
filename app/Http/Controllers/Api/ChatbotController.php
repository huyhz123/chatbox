<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Send message to chatbot
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|exists:chatbot_conversations,id',
            'message' => 'required|string|max:1000',
        ]);

        $userId = $request->user() ? $request->user()->id : null;
        $conversationId = $validated['conversation_id'] ?? null;

        // Create new conversation if not provided
        if (!$conversationId) {
            $conversation = ChatbotConversation::create([
                'user_id' => $userId,
                'session_id' => $request->session()->getId(),
            ]);
            $conversationId = $conversation->id;
        }

        // Save user message
        $userMessage = ChatbotMessage::create([
            'conversation_id' => $conversationId,
            'sender' => 'user',
            'message' => $validated['message'],
        ]);

        // Get bot response
        $botResponse = $this->chatbotService->processMessage(
            $validated['message'],
            $conversationId
        );

        // Save bot message
        $botMessage = ChatbotMessage::create([
            'conversation_id' => $conversationId,
            'sender' => 'bot',
            'message' => $botResponse,
        ]);

        return response()->json([
            'conversation_id' => $conversationId,
            'user_message' => $userMessage,
            'bot_message' => $botMessage,
        ]);
    }

    /**
     * Get conversation messages
     */
    public function getConversation($id)
    {
        $conversation = ChatbotConversation::with('messages')->findOrFail($id);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages,
        ]);
    }

    /**
     * Create new conversation
     */
    public function createConversation(Request $request)
    {
        $userId = $request->user() ? $request->user()->id : null;

        $conversation = ChatbotConversation::create([
            'user_id' => $userId,
            'session_id' => $request->session()->getId(),
        ]);

        return response()->json([
            'message' => 'Conversation created',
            'conversation' => $conversation,
        ], 201);
    }

    /**
     * Delete conversation
     */
    public function deleteConversation(Request $request, $id)
    {
        $conversation = ChatbotConversation::findOrFail($id);

        // Check ownership if user is authenticated
        if ($request->user() && $conversation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $conversation->delete();

        return response()->json([
            'message' => 'Conversation deleted',
        ]);
    }

    /**
     * Submit feedback
     */
    public function submitFeedback(Request $request)
    {
        $validated = $request->validate([
            'message_id' => 'required|exists:chatbot_messages,id',
            'rating' => 'required|in:positive,negative',
            'comment' => 'nullable|string|max:500',
        ]);

        // Save feedback (you can create a Feedback model)
        return response()->json([
            'message' => 'Feedback submitted successfully',
        ]);
    }

    /**
     * Get user's conversations
     */
    public function myConversations(Request $request)
    {
        $conversations = ChatbotConversation::where('user_id', $request->user()->id)
            ->with('messages')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'conversations' => $conversations->items(),
            'pagination' => [
                'total' => $conversations->total(),
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
            ],
        ]);
    }
}
