<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\KaraokeSession;
use App\Models\KaraokeScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KaraokeController extends Controller
{
    /**
     * Get available songs
     */
    public function songs(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'language' => 'nullable|in:vi,en,ko,ja,other',
                'genre' => 'nullable|string',
                'search' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $query = Song::query();

            // Filter by language
            if ($request->language) {
                $query->where('language', $request->language);
            }

            // Filter by genre
            if ($request->genre) {
                $query->where('genre', $request->genre);
            }

            // Search
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'LIKE', '%' . $request->search . '%')
                      ->orWhere('artist', 'LIKE', '%' . $request->search . '%');
                });
            }

            $songs = $query->orderBy('title', 'asc')->paginate(50);

            return response()->json([
                'success' => true,
                'songs' => $songs->items(),
                'pagination' => [
                    'current_page' => $songs->currentPage(),
                    'total' => $songs->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch songs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a karaoke session
     */
    public function startSession(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'song_id' => 'required|exists:songs,id',
                'mode' => 'required|in:solo,duet,battle',
                'opponent_id' => 'nullable|required_if:mode,battle,duet|exists:chat_users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $song = Song::find($request->song_id);

            $session = KaraokeSession::create([
                'user_id' => $user->id,
                'song_id' => $request->song_id,
                'opponent_id' => $request->opponent_id,
                'mode' => $request->mode,
                'status' => 'active',
                'started_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'session' => $session->load('song'),
                'lyrics' => $song->lyrics, // TODO: Parse and sync lyrics
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit karaoke score
     */
    public function submitScore(Request $request, int $sessionId): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'score' => 'required|numeric|min:0|max:100',
                'accuracy' => 'required|numeric|min:0|max:100',
                'combo' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $session = KaraokeSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found',
                ], 404);
            }

            // Calculate final score
            $finalScore = ($request->score * 0.7) + ($request->accuracy * 0.3);

            // Determine rank
            $rank = 'D';
            if ($finalScore >= 95) $rank = 'S';
            elseif ($finalScore >= 90) $rank = 'A';
            elseif ($finalScore >= 80) $rank = 'B';
            elseif ($finalScore >= 70) $rank = 'C';

            // Save score
            $karaokeScore = KaraokeScore::create([
                'session_id' => $sessionId,
                'user_id' => $user->id,
                'song_id' => $session->song_id,
                'score' => $finalScore,
                'accuracy' => $request->accuracy,
                'combo' => $request->combo,
                'rank' => $rank,
            ]);

            // Update session
            $session->update([
                'status' => 'completed',
                'ended_at' => now(),
                'final_score' => $finalScore,
            ]);

            // Award experience based on score
            $expGain = (int)($finalScore * 2);
            $user->addExp($expGain);

            return response()->json([
                'success' => true,
                'score' => $karaokeScore,
                'exp_gained' => $expGain,
                'new_level' => $user->fresh()->level,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit score',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get karaoke leaderboard
     */
    public function leaderboard(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'song_id' => 'nullable|exists:songs,id',
                'period' => 'nullable|in:daily,weekly,monthly,all',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $query = KaraokeScore::with(['user', 'song']);

            // Filter by song
            if ($request->song_id) {
                $query->where('song_id', $request->song_id);
            }

            // Filter by period
            if ($request->period) {
                switch ($request->period) {
                    case 'daily':
                        $query->whereDate('created_at', today());
                        break;
                    case 'weekly':
                        $query->where('created_at', '>=', now()->startOfWeek());
                        break;
                    case 'monthly':
                        $query->where('created_at', '>=', now()->startOfMonth());
                        break;
                }
            }

            $leaderboard = $query->orderBy('score', 'desc')
                ->limit(100)
                ->get();

            return response()->json([
                'success' => true,
                'leaderboard' => $leaderboard,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaderboard',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's karaoke history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $history = KaraokeScore::where('user_id', $user->id)
                ->with('song')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Calculate stats
            $stats = [
                'total_songs' => KaraokeScore::where('user_id', $user->id)->distinct('song_id')->count(),
                'average_score' => KaraokeScore::where('user_id', $user->id)->avg('score'),
                'best_score' => KaraokeScore::where('user_id', $user->id)->max('score'),
                's_rank_count' => KaraokeScore::where('user_id', $user->id)->where('rank', 'S')->count(),
            ];

            return response()->json([
                'success' => true,
                'history' => $history->items(),
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $history->currentPage(),
                    'total' => $history->total(),
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
