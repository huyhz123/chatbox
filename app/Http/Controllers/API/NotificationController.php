<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $notifications = DB::table('notifications')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'total' => $notifications->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $count = DB::table('notifications')
                ->where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $updated = DB::table('notifications')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
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
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            DB::table('notifications')
                ->where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all as read',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a notification
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $deleted = DB::table('notifications')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            DB::table('notifications')
                ->where('user_id', $user->id)
                ->where('is_read', true)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'All read notifications deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get notification settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $settings = [
                'enable_push' => $user->enable_push_notifications ?? true,
                'enable_email' => $user->enable_email_notifications ?? true,
                'notify_messages' => $user->notify_messages ?? true,
                'notify_likes' => $user->notify_likes ?? true,
                'notify_comments' => $user->notify_comments ?? true,
                'notify_follows' => $user->notify_follows ?? true,
                'notify_gifts' => $user->notify_gifts ?? true,
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $user->update([
                'enable_push_notifications' => $request->enable_push ?? true,
                'enable_email_notifications' => $request->enable_email ?? true,
                'notify_messages' => $request->notify_messages ?? true,
                'notify_likes' => $request->notify_likes ?? true,
                'notify_comments' => $request->notify_comments ?? true,
                'notify_follows' => $request->notify_follows ?? true,
                'notify_gifts' => $request->notify_gifts ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
