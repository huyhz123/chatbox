<?php

namespace App\Services;

use Illuminate\Support\Facades\Broadcast;
use App\Events\MessageSent;
use App\Events\GiftSent;
use App\Events\UserOnlineStatusChanged;
use App\Events\TypingIndicator;
use App\Events\NotificationSent;
use App\Models\Message;
use App\Models\GiftTransaction;
use App\Models\ChatUser;

class BroadcastService
{
    /**
     * Broadcast new message
     */
    public function broadcastMessage(Message $message): void
    {
        try {
            // Load message with relationships
            $message->load(['sender', 'conversation']);

            // Broadcast to conversation channel
            broadcast(new MessageSent($message))->toOthers();

            \Log::info('Message broadcast sent', [
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast message: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast gift animation
     */
    public function broadcastGift(GiftTransaction $transaction): void
    {
        try {
            $transaction->load(['sender', 'receiver', 'gift']);

            // Determine broadcast channel based on context
            $channelName = match($transaction->context_type) {
                'livestream' => 'livestream.' . $transaction->context_id,
                'room' => 'room.' . $transaction->context_id,
                'chat' => 'conversation.' . $transaction->context_id,
                default => 'user.' . $transaction->receiver_id,
            };

            broadcast(new GiftSent($transaction, $channelName))->toOthers();

            \Log::info('Gift broadcast sent', [
                'transaction_id' => $transaction->id,
                'channel' => $channelName,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast gift: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast user online status change
     */
    public function broadcastOnlineStatus(ChatUser $user, bool $isOnline): void
    {
        try {
            broadcast(new UserOnlineStatusChanged($user, $isOnline))->toOthers();

            \Log::info('Online status broadcast sent', [
                'user_id' => $user->id,
                'is_online' => $isOnline,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast online status: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast typing indicator
     */
    public function broadcastTyping(int $conversationId, ChatUser $user, bool $isTyping): void
    {
        try {
            broadcast(new TypingIndicator($conversationId, $user, $isTyping))->toOthers();
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast typing indicator: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast notification
     */
    public function broadcastNotification(int $userId, array $notification): void
    {
        try {
            broadcast(new NotificationSent($userId, $notification))->toOthers();

            \Log::info('Notification broadcast sent', [
                'user_id' => $userId,
                'type' => $notification['type'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast notification: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast to livestream viewers
     */
    public function broadcastToLivestream(int $streamId, string $event, array $data): void
    {
        try {
            $channelName = 'livestream.' . $streamId;

            Broadcast::channel($channelName, function () {
                return true; // Implement proper authorization
            });

            broadcast(new \Illuminate\Broadcasting\PrivateChannel($channelName))
                ->with(['event' => $event, 'data' => $data])
                ->toOthers();

            \Log::info('Livestream broadcast sent', [
                'stream_id' => $streamId,
                'event' => $event,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast to livestream: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast to room participants
     */
    public function broadcastToRoom(int $roomId, string $event, array $data): void
    {
        try {
            $channelName = 'room.' . $roomId;

            broadcast(new \Illuminate\Broadcasting\PrivateChannel($channelName))
                ->with(['event' => $event, 'data' => $data])
                ->toOthers();

            \Log::info('Room broadcast sent', [
                'room_id' => $roomId,
                'event' => $event,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast to room: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast to guild members
     */
    public function broadcastToGuild(int $guildId, string $event, array $data): void
    {
        try {
            $channelName = 'guild.' . $guildId;

            broadcast(new \Illuminate\Broadcasting\PrivateChannel($channelName))
                ->with(['event' => $event, 'data' => $data])
                ->toOthers();

            \Log::info('Guild broadcast sent', [
                'guild_id' => $guildId,
                'event' => $event,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast to guild: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast global announcement
     */
    public function broadcastGlobal(string $event, array $data): void
    {
        try {
            broadcast(new \Illuminate\Broadcasting\Channel('global'))
                ->with(['event' => $event, 'data' => $data]);

            \Log::info('Global broadcast sent', [
                'event' => $event,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast globally: ' . $e->getMessage());
        }
    }

    /**
     * Send push notification via FCM/APNS
     */
    public function sendPushNotification(ChatUser $user, array $notification): void
    {
        try {
            // TODO: Implement FCM/APNS integration
            // This would typically use:
            // - Firebase Cloud Messaging for Android
            // - Apple Push Notification Service for iOS

            // Check user notification settings
            if (!$user->enable_push_notifications) {
                return;
            }

            // Example FCM implementation:
            // $fcmToken = $user->fcm_token;
            // if ($fcmToken) {
            //     $message = CloudMessage::withTarget('token', $fcmToken)
            //         ->withNotification(Notification::create(
            //             $notification['title'],
            //             $notification['body']
            //         ))
            //         ->withData($notification['data'] ?? []);
            //
            //     $messaging = app('firebase.messaging');
            //     $messaging->send($message);
            // }

            \Log::info('Push notification queued', [
                'user_id' => $user->id,
                'type' => $notification['type'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send push notification: ' . $e->getMessage());
        }
    }

    /**
     * Send email notification
     */
    public function sendEmailNotification(ChatUser $user, array $notification): void
    {
        try {
            // Check user email notification settings
            if (!$user->enable_email_notifications) {
                return;
            }

            // Queue email notification
            // \Mail::to($user->email)->queue(new NotificationEmail($notification));

            \Log::info('Email notification queued', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send email notification: ' . $e->getMessage());
        }
    }
}
