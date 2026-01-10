<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatUser;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RankingController extends Controller
{
    /**
     * Get rankings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:level,gifts_sent,gifts_received,followers,streams,wealth',
                'period' => 'nullable|in:daily,weekly,monthly,all',
                'limit' => 'nullable|integer|min:10|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $type = $request->type;
            $period = $request->period ?? 'all';
            $limit = $request->limit ?? 100;

            $rankings = match($type) {
                'level' => $this->getLevelRankings($limit),
                'gifts_sent' => $this->getGiftsSentRankings($period, $limit),
                'gifts_received' => $this->getGiftsReceivedRankings($period, $limit),
                'followers' => $this->getFollowersRankings($limit),
                'streams' => $this->getStreamersRankings($period, $limit),
                'wealth' => $this->getWealthRankings($limit),
            };

            return response()->json([
                'success' => true,
                'rankings' => $rankings,
                'type' => $type,
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rankings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get level rankings
     */
    private function getLevelRankings(int $limit): array
    {
        return ChatUser::select('id', 'username', 'avatar', 'level', 'exp', 'vip_level', 'is_verified')
            ->orderBy('level', 'desc')
            ->orderBy('exp', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'value' => $user->level,
                    'display_value' => "Level {$user->level}",
                ];
            })
            ->toArray();
    }

    /**
     * Get gifts sent rankings
     */
    private function getGiftsSentRankings(string $period, int $limit): array
    {
        $query = Transaction::select('user_id', DB::raw('SUM(amount) as total_sent'))
            ->where('type', 'gift_sent')
            ->where('amount', '<', 0);

        // Apply period filter
        $query = $this->applyPeriodFilter($query, $period);

        return $query->groupBy('user_id')
            ->orderBy('total_sent', 'asc') // Negative values, so ascending
            ->limit($limit)
            ->get()
            ->map(function ($transaction, $index) {
                $user = ChatUser::select('id', 'username', 'avatar', 'level', 'vip_level', 'is_verified')
                    ->find($transaction->user_id);

                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'value' => abs($transaction->total_sent),
                    'display_value' => number_format(abs($transaction->total_sent)) . ' coins',
                ];
            })
            ->toArray();
    }

    /**
     * Get gifts received rankings
     */
    private function getGiftsReceivedRankings(string $period, int $limit): array
    {
        $query = Transaction::select('user_id', DB::raw('SUM(amount) as total_received'))
            ->where('type', 'gift_received')
            ->where('amount', '>', 0);

        // Apply period filter
        $query = $this->applyPeriodFilter($query, $period);

        return $query->groupBy('user_id')
            ->orderBy('total_received', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($transaction, $index) {
                $user = ChatUser::select('id', 'username', 'avatar', 'level', 'vip_level', 'is_verified')
                    ->find($transaction->user_id);

                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'value' => $transaction->total_received,
                    'display_value' => number_format($transaction->total_received) . ' coins',
                ];
            })
            ->toArray();
    }

    /**
     * Get followers rankings
     */
    private function getFollowersRankings(int $limit): array
    {
        return ChatUser::select('id', 'username', 'avatar', 'level', 'vip_level', 'is_verified')
            ->withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'value' => $user->followers_count,
                    'display_value' => number_format($user->followers_count) . ' followers',
                ];
            })
            ->toArray();
    }

    /**
     * Get streamers rankings
     */
    private function getStreamersRankings(string $period, int $limit): array
    {
        $query = DB::table('live_streams')
            ->select('streamer_id', DB::raw('SUM(total_views) as total_stream_views'))
            ->where('status', 'ended');

        // Apply period filter
        $query = $this->applyPeriodFilter($query, $period);

        return $query->groupBy('streamer_id')
            ->orderBy('total_stream_views', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($stream, $index) {
                $user = ChatUser::select('id', 'username', 'avatar', 'level', 'vip_level', 'is_verified')
                    ->find($stream->streamer_id);

                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'value' => $stream->total_stream_views,
                    'display_value' => number_format($stream->total_stream_views) . ' views',
                ];
            })
            ->toArray();
    }

    /**
     * Get wealth rankings
     */
    private function getWealthRankings(int $limit): array
    {
        return ChatUser::select('id', 'username', 'avatar', 'level', 'vip_level', 'is_verified', 'balance')
            ->orderBy('balance', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'value' => $user->balance,
                    'display_value' => number_format($user->balance) . ' coins',
                ];
            })
            ->toArray();
    }

    /**
     * Apply period filter to query
     */
    private function applyPeriodFilter($query, string $period)
    {
        switch ($period) {
            case 'daily':
                return $query->whereDate('created_at', today());
            case 'weekly':
                return $query->where('created_at', '>=', now()->startOfWeek());
            case 'monthly':
                return $query->where('created_at', '>=', now()->startOfMonth());
            default:
                return $query;
        }
    }

    /**
     * Get user's rank in specific ranking
     */
    public function userRank(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:level,gifts_sent,gifts_received,followers,streams,wealth',
                'period' => 'nullable|in:daily,weekly,monthly,all',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $type = $request->type;
            $period = $request->period ?? 'all';

            // Get full rankings
            $rankings = match($type) {
                'level' => $this->getLevelRankings(10000),
                'gifts_sent' => $this->getGiftsSentRankings($period, 10000),
                'gifts_received' => $this->getGiftsReceivedRankings($period, 10000),
                'followers' => $this->getFollowersRankings(10000),
                'streams' => $this->getStreamersRankings($period, 10000),
                'wealth' => $this->getWealthRankings(10000),
            };

            // Find user's rank
            $userRank = collect($rankings)->firstWhere('user.id', $user->id);

            if (!$userRank) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not ranked yet',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'rank' => $userRank,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user rank',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
