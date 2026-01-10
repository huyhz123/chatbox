<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\ChatUser;
use App\Models\Gift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LiveStreamController extends Controller
{
    /**
     * Get list of active live streams
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'nullable|in:music,gaming,talk,pk,other',
                'status' => 'nullable|in:live,scheduled,ended',
                'sort' => 'nullable|in:viewers,created_at,gifts',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $query = LiveStream::with(['streamer'])
                ->where('status', 'live');

            // Filter by category
            if ($request->category) {
                $query->where('category', $request->category);
            }

            // Sort
            $sortBy = $request->sort ?? 'viewers';
            if ($sortBy === 'viewers') {
                $query->orderBy('viewers_count', 'desc');
            } elseif ($sortBy === 'gifts') {
                $query->orderBy('total_gifts_value', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $streams = $query->paginate(20);

            return response()->json([
                'success' => true,
                'streams' => $streams->items(),
                'pagination' => [
                    'current_page' => $streams->currentPage(),
                    'total' => $streams->total(),
                    'per_page' => $streams->perPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch streams',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific live stream
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $stream = LiveStream::with(['streamer', 'pkOpponent'])
                ->find($id);

            if (!$stream) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream not found',
                ], 404);
            }

            // Increment viewer count if user is authenticated
            if ($request->user()) {
                $stream->increment('viewers_count');
                $stream->increment('total_views');
            }

            return response()->json([
                'success' => true,
                'stream' => $stream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stream',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a new live stream
     */
    public function start(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:200',
                'description' => 'nullable|string|max:1000',
                'category' => 'required|in:music,gaming,talk,pk,other',
                'thumbnail' => 'nullable|url',
                'is_private' => 'boolean',
                'password' => 'nullable|required_if:is_private,true|string|min:4',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check if user already has an active stream
            $existingStream = LiveStream::where('streamer_id', $user->id)
                ->where('status', 'live')
                ->first();

            if ($existingStream) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active stream',
                ], 400);
            }

            // Generate Agora.io channel name and token
            $channelName = 'stream_' . $user->id . '_' . time();

            // TODO: Generate Agora RTC token
            // $agoraToken = $this->generateAgoraToken($channelName, $user->id);

            $stream = LiveStream::create([
                'streamer_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'thumbnail' => $request->thumbnail ?? $user->avatar,
                'is_private' => $request->is_private ?? false,
                'password' => $request->is_private ? bcrypt($request->password) : null,
                'channel_name' => $channelName,
                'agora_token' => 'temp_token', // TODO: Replace with real Agora token
                'status' => 'live',
                'started_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stream started successfully',
                'stream' => $stream,
                'agora' => [
                    'app_id' => env('AGORA_APP_ID'),
                    'channel' => $channelName,
                    'token' => 'temp_token', // TODO: Real token
                    'uid' => $user->id,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start stream',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * End a live stream
     */
    public function end(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $stream = LiveStream::where('id', $id)
                ->where('streamer_id', $user->id)
                ->where('status', 'live')
                ->first();

            if (!$stream) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream not found or already ended',
                ], 404);
            }

            $stream->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);

            // Calculate stream duration in minutes
            $duration = $stream->started_at->diffInMinutes($stream->ended_at);

            return response()->json([
                'success' => true,
                'message' => 'Stream ended',
                'stats' => [
                    'duration_minutes' => $duration,
                    'total_views' => $stream->total_views,
                    'peak_viewers' => $stream->viewers_count,
                    'total_gifts' => $stream->total_gifts_value,
                    'streamer_earnings' => $stream->total_gifts_value * 0.4, // 40% commission
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to end stream',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Join a live stream
     */
    public function join(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $stream = LiveStream::with('streamer')->find($id);

            if (!$stream) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream not found',
                ], 404);
            }

            if ($stream->status !== 'live') {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream is not live',
                ], 400);
            }

            // Check password for private streams
            if ($stream->is_private) {
                $validator = Validator::make($request->all(), [
                    'password' => 'required|string',
                ]);

                if ($validator->fails() || !password_verify($request->password, $stream->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password',
                    ], 401);
                }
            }

            // TODO: Generate viewer Agora token
            // $agoraToken = $this->generateAgoraToken($stream->channel_name, $user->id);

            return response()->json([
                'success' => true,
                'stream' => $stream,
                'agora' => [
                    'app_id' => env('AGORA_APP_ID'),
                    'channel' => $stream->channel_name,
                    'token' => 'temp_viewer_token', // TODO: Real token
                    'uid' => $user->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to join stream',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send gift in live stream
     */
    public function sendGift(Request $request, int $streamId): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'gift_id' => 'required|exists:gifts,id',
                'quantity' => 'required|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $stream = LiveStream::where('id', $streamId)
                ->where('status', 'live')
                ->first();

            if (!$stream) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream not found or not live',
                ], 404);
            }

            $gift = Gift::find($request->gift_id);
            $totalCost = $gift->price * $request->quantity;

            // Check balance
            if ($user->balance < $totalCost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Deduct from sender
                $user->deductCoins($totalCost, "Sent {$request->quantity}x {$gift->name} in live stream");

                // Credit streamer (40% commission)
                $streamerEarning = $totalCost * 0.4;
                $stream->streamer->addCoins($streamerEarning, "Received gift in live stream from {$user->username}");

                // Update stream stats
                $stream->increment('total_gifts_value', $totalCost);

                // TODO: Broadcast gift animation via WebSocket
                // broadcast(new GiftSent($stream, $user, $gift, $request->quantity));

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Gift sent successfully',
                    'data' => [
                        'gift' => $gift,
                        'quantity' => $request->quantity,
                        'total_cost' => $totalCost,
                        'new_balance' => $user->fresh()->balance,
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
     * Start PK battle
     */
    public function startPK(Request $request, int $streamId): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'opponent_stream_id' => 'required|exists:live_streams,id',
                'duration_minutes' => 'required|integer|min:5|max:30',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $stream = LiveStream::where('id', $streamId)
                ->where('streamer_id', $user->id)
                ->where('status', 'live')
                ->first();

            if (!$stream) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your stream not found',
                ], 404);
            }

            $opponentStream = LiveStream::where('id', $request->opponent_stream_id)
                ->where('status', 'live')
                ->first();

            if (!$opponentStream) {
                return response()->json([
                    'success' => false,
                    'message' => 'Opponent stream not found',
                ], 404);
            }

            // Start PK battle
            $stream->update([
                'is_pk_battle' => true,
                'pk_opponent_id' => $opponentStream->id,
                'pk_start_time' => now(),
                'pk_end_time' => now()->addMinutes($request->duration_minutes),
                'pk_score' => 0,
            ]);

            $opponentStream->update([
                'is_pk_battle' => true,
                'pk_opponent_id' => $stream->id,
                'pk_start_time' => now(),
                'pk_end_time' => now()->addMinutes($request->duration_minutes),
                'pk_score' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PK Battle started',
                'pk' => [
                    'duration_minutes' => $request->duration_minutes,
                    'end_time' => now()->addMinutes($request->duration_minutes),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start PK',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get streamer's stream history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $streams = LiveStream::where('streamer_id', $user->id)
                ->where('status', 'ended')
                ->orderBy('ended_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'streams' => $streams->items(),
                'pagination' => [
                    'current_page' => $streams->currentPage(),
                    'total' => $streams->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
