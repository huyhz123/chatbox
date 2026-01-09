<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\ChatUser;
use App\Models\LiveStream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiftController extends Controller
{
    /**
     * Get all gifts catalog
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $category = $request->query('category'); // free, basic, special, vip, lucky

            $query = Gift::where('is_active', true);

            if ($category) {
                $query->where('category', $category);
            }

            $gifts = $query->orderBy('sort_order')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'gifts' => $gifts,
                    'categories' => [
                        'free' => Gift::where('category', 'free')->where('is_active', true)->count(),
                        'basic' => Gift::where('category', 'basic')->where('is_active', true)->count(),
                        'special' => Gift::where('category', 'special')->where('is_active', true)->count(),
                        'vip' => Gift::where('category', 'vip')->where('is_active', true)->count(),
                        'lucky' => Gift::where('category', 'lucky')->where('is_active', true)->count(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch gifts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send gift to user
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gift_id' => 'required|exists:gifts,id',
            'receiver_id' => 'required|exists:chat_users,id',
            'quantity' => 'sometimes|integer|min:1|max:999',
            'context_type' => 'sometimes|in:chat,livestream,post,room',
            'context_id' => 'sometimes|integer',
            'is_public' => 'sometimes|boolean',
        ]);

        try {
            $sender = $request->user();
            $gift = Gift::findOrFail($validated['gift_id']);
            $receiver = ChatUser::findOrFail($validated['receiver_id']);
            $quantity = $validated['quantity'] ?? 1;

            // Check if sender has enough coins
            $totalCost = $gift->price * $quantity;

            if ($sender->balance < $totalCost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance',
                    'data' => [
                        'required' => $totalCost,
                        'current' => $sender->balance,
                        'deficit' => $totalCost - $sender->balance,
                    ],
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Deduct coins from sender (only if not free)
                if ($gift->price > 0) {
                    $sender->deductCoins($totalCost, "Sent gift: {$gift->name} x{$quantity} to {$receiver->username}");
                }

                // Add coins to receiver (40% of gift value for streamers if in livestream context)
                $contextType = $validated['context_type'] ?? 'chat';
                if ($contextType === 'livestream' && $gift->price > 0) {
                    $commissionRate = config('chat.streamer_commission_percent', 40) / 100;
                    $receiverEarnings = $totalCost * $commissionRate;
                    $receiver->addCoins($receiverEarnings, "Received gift: {$gift->name} x{$quantity} from {$sender->username}");
                }

                // Create gift transaction
                $transaction = GiftTransaction::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'gift_id' => $gift->id,
                    'quantity' => $quantity,
                    'total_price' => $totalCost,
                    'context_type' => $contextType,
                    'context_id' => $validated['context_id'] ?? null,
                    'is_public' => $validated['is_public'] ?? true,
                ]);

                // Update livestream stats if applicable
                if ($contextType === 'livestream' && isset($validated['context_id'])) {
                    $stream = LiveStream::find($validated['context_id']);
                    if ($stream) {
                        $stream->increment('total_gifts_value', $totalCost);
                    }
                }

                // Add EXP for sender
                $sender->addExp(intval($totalCost / 10)); // 10 coins = 1 EXP

                DB::commit();

                // TODO: Broadcast gift animation via WebSocket

                return response()->json([
                    'success' => true,
                    'message' => 'Gift sent successfully',
                    'data' => [
                        'transaction' => $transaction,
                        'sender_balance' => $sender->fresh()->balance,
                        'animation' => [
                            'url' => $gift->animation_url,
                            'type' => $gift->animation_type,
                            'duration_ms' => $gift->duration_ms,
                        ],
                    ],
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send gift',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gift history (sent)
     */
    public function sentHistory(Request $request): JsonResponse
    {
        try {
            $history = GiftTransaction::with(['gift', 'receiver'])
                ->where('sender_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch gift history',
            ], 500);
        }
    }

    /**
     * Get gift history (received)
     */
    public function receivedHistory(Request $request): JsonResponse
    {
        try {
            $history = GiftTransaction::with(['gift', 'sender'])
                ->where('receiver_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch received gifts',
            ], 500);
        }
    }

    /**
     * Get gift statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $totalSent = GiftTransaction::where('sender_id', $user->id)->sum('total_price');
            $totalReceived = GiftTransaction::where('receiver_id', $user->id)->sum('total_price');
            $totalGiftsSent = GiftTransaction::where('sender_id', $user->id)->sum('quantity');
            $totalGiftsReceived = GiftTransaction::where('receiver_id', $user->id)->sum('quantity');

            return response()->json([
                'success' => true,
                'data' => [
                    'sent' => [
                        'total_coins' => $totalSent,
                        'total_gifts' => $totalGiftsSent,
                    ],
                    'received' => [
                        'total_coins' => $totalReceived,
                        'total_gifts' => $totalGiftsReceived,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
            ], 500);
        }
    }

    /**
     * Get top gifters leaderboard
     */
    public function topGifters(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'all_time'); // daily, weekly, monthly, all_time

            $query = GiftTransaction::select('sender_id', DB::raw('SUM(total_price) as total_spent'))
                ->groupBy('sender_id');

            if ($period === 'daily') {
                $query->whereDate('created_at', today());
            } elseif ($period === 'weekly') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($period === 'monthly') {
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            }

            $topGifters = $query->orderBy('total_spent', 'desc')
                ->limit(100)
                ->get();

            // Load user data
            $users = ChatUser::whereIn('id', $topGifters->pluck('sender_id'))
                ->get()
                ->keyBy('id');

            $leaderboard = $topGifters->map(function($item, $index) use ($users) {
                $user = $users->get($item->sender_id);
                return [
                    'rank' => $index + 1,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'full_name' => $user->full_name,
                        'avatar' => $user->avatar ? url(\Storage::url($user->avatar)) : null,
                        'level' => $user->level,
                        'vip_level' => $user->vip_level,
                    ],
                    'total_spent' => $item->total_spent,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'leaderboard' => $leaderboard,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaderboard',
            ], 500);
        }
    }
}
