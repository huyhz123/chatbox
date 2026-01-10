<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get user's conversations list
     */
    public function conversations(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $conversations = Conversation::where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                      ->orWhere('user2_id', $user->id);
            })
            ->whereNull('deleted_at')
            ->with(['user1', 'user2', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

            // Format conversations with other user info
            $formattedConversations = $conversations->map(function ($conversation) use ($user) {
                $otherUser = $conversation->user1_id === $user->id
                    ? $conversation->user2
                    : $conversation->user1;

                // Get unread count
                $unreadCount = Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'username' => $otherUser->username,
                        'avatar' => $otherUser->avatar,
                        'is_online' => $otherUser->is_online,
                        'is_verified' => $otherUser->is_verified,
                        'vip_level' => $otherUser->vip_level,
                    ],
                    'last_message' => $conversation->lastMessage ? [
                        'id' => $conversation->lastMessage->id,
                        'content' => $conversation->lastMessage->content,
                        'type' => $conversation->lastMessage->type,
                        'created_at' => $conversation->lastMessage->created_at,
                        'is_mine' => $conversation->lastMessage->sender_id === $user->id,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'updated_at' => $conversation->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'conversations' => $formattedConversations,
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'total' => $conversations->total(),
                    'per_page' => $conversations->perPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get or create conversation with a user
     */
    public function getOrCreateConversation(Request $request, int $userId): JsonResponse
    {
        try {
            $currentUser = $request->user();

            // Cannot create conversation with self
            if ($currentUser->id === $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create conversation with yourself',
                ], 400);
            }

            // Check if other user exists
            $otherUser = ChatUser::find($userId);
            if (!$otherUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Try to find existing conversation
            $conversation = Conversation::where(function ($query) use ($currentUser, $userId) {
                $query->where('user1_id', $currentUser->id)
                      ->where('user2_id', $userId);
            })
            ->orWhere(function ($query) use ($currentUser, $userId) {
                $query->where('user1_id', $userId)
                      ->where('user2_id', $currentUser->id);
            })
            ->first();

            // Create new conversation if doesn't exist
            if (!$conversation) {
                $conversation = Conversation::create([
                    'user1_id' => $currentUser->id,
                    'user2_id' => $userId,
                ]);
            }

            return response()->json([
                'success' => true,
                'conversation' => [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'username' => $otherUser->username,
                        'avatar' => $otherUser->avatar,
                        'is_online' => $otherUser->is_online,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create conversation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get messages in a conversation
     */
    public function getMessages(Request $request, int $conversationId): JsonResponse
    {
        try {
            $user = $request->user();

            // Verify user is part of conversation
            $conversation = Conversation::where('id', $conversationId)
                ->where(function ($query) use ($user) {
                    $query->where('user1_id', $user->id)
                          ->orWhere('user2_id', $user->id);
                })
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found or unauthorized',
                ], 404);
            }

            // Get messages with pagination
            $messages = Message::where('conversation_id', $conversationId)
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            // Mark messages as read
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            return response()->json([
                'success' => true,
                'messages' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'total' => $messages->total(),
                    'per_page' => $messages->perPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:text,image,audio,video,file,gift,sticker',
                'content' => 'required_if:type,text|string|max:5000',
                'media_url' => 'required_unless:type,text|url',
                'gift_id' => 'required_if:type,gift|exists:gifts,id',
                'reply_to_id' => 'nullable|exists:messages,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Verify user is part of conversation
            $conversation = Conversation::where('id', $conversationId)
                ->where(function ($query) use ($user) {
                    $query->where('user1_id', $user->id)
                          ->orWhere('user2_id', $user->id);
                })
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found or unauthorized',
                ], 404);
            }

            // Create message
            $message = Message::create([
                'conversation_id' => $conversationId,
                'sender_id' => $user->id,
                'type' => $request->type,
                'content' => $request->content,
                'media_url' => $request->media_url,
                'gift_id' => $request->gift_id,
                'reply_to_id' => $request->reply_to_id,
            ]);

            // Update conversation timestamp
            $conversation->touch();

            // TODO: Broadcast message via WebSocket
            // broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message sent',
                'data' => $message->load('sender'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage(Request $request, int $messageId): JsonResponse
    {
        try {
            $user = $request->user();

            $message = Message::where('id', $messageId)
                ->where('sender_id', $user->id)
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found or unauthorized',
                ], 404);
            }

            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(Request $request, int $conversationId): JsonResponse
    {
        try {
            $user = $request->user();

            // Verify user is part of conversation
            $conversation = Conversation::where('id', $conversationId)
                ->where(function ($query) use ($user) {
                    $query->where('user1_id', $user->id)
                          ->orWhere('user2_id', $user->id);
                })
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ], 404);
            }

            // Mark all messages as read
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Conversation marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as read',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search messages in conversation
     */
    public function searchMessages(Request $request, int $conversationId): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Verify user is part of conversation
            $conversation = Conversation::where('id', $conversationId)
                ->where(function ($query) use ($user) {
                    $query->where('user1_id', $user->id)
                          ->orWhere('user2_id', $user->id);
                })
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ], 404);
            }

            $messages = Message::where('conversation_id', $conversationId)
                ->where('type', 'text')
                ->where('content', 'LIKE', '%' . $request->query . '%')
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'results' => $messages,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread messages count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get all user's conversation IDs
            $conversationIds = Conversation::where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                      ->orWhere('user2_id', $user->id);
            })->pluck('id');

            // Count unread messages
            $unreadCount = Message::whereIn('conversation_id', $conversationIds)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
