<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\GameScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    /**
     * Get available games
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'nullable|in:card,board,casual,puzzle,arcade',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $query = Game::query();

            if ($request->category) {
                $query->where('category', $request->category);
            }

            $games = $query->orderBy('name', 'asc')->get();

            return response()->json([
                'success' => true,
                'games' => $games,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch games',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific game
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $game = Game::find($id);

            if (!$game) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game not found',
                ], 404);
            }

            // Get online players count (sessions in last 5 minutes)
            $onlinePlayers = GameSession::where('game_id', $id)
                ->where('updated_at', '>=', now()->subMinutes(5))
                ->distinct('user_id')
                ->count();

            return response()->json([
                'success' => true,
                'game' => array_merge($game->toArray(), [
                    'online_players' => $onlinePlayers,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch game',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a game session
     */
    public function startSession(Request $request, int $gameId): JsonResponse
    {
        try {
            $user = $request->user();

            $game = Game::find($gameId);
            if (!$game) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'mode' => 'required|in:solo,multiplayer',
                'bet_amount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // If bet amount specified, check balance
            if ($request->bet_amount && $user->balance < $request->bet_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance',
                ], 400);
            }

            // Deduct bet amount if specified
            if ($request->bet_amount) {
                $user->deductCoins($request->bet_amount, "Bet on game: {$game->name}");
            }

            $session = GameSession::create([
                'game_id' => $gameId,
                'user_id' => $user->id,
                'mode' => $request->mode,
                'bet_amount' => $request->bet_amount ?? 0,
                'status' => 'active',
                'started_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'session' => $session,
                'game' => $game,
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
     * Submit game score
     */
    public function submitScore(Request $request, int $sessionId): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'score' => 'required|integer|min:0',
                'result' => 'required|in:win,lose,draw',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $session = GameSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found or already completed',
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Save score
                $gameScore = GameScore::create([
                    'session_id' => $sessionId,
                    'user_id' => $user->id,
                    'game_id' => $session->game_id,
                    'score' => $request->score,
                    'result' => $request->result,
                ]);

                // Update session
                $session->update([
                    'status' => 'completed',
                    'ended_at' => now(),
                    'final_score' => $request->score,
                ]);

                // Process winnings
                $winnings = 0;
                if ($session->bet_amount > 0 && $request->result === 'win') {
                    $winnings = $session->bet_amount * 1.8; // 80% return
                    $user->addCoins($winnings, "Won game: {$session->game->name}");
                }

                // Award experience
                $expGain = match($request->result) {
                    'win' => 50,
                    'draw' => 25,
                    'lose' => 10,
                };
                $user->addExp($expGain);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'score' => $gameScore,
                    'winnings' => $winnings,
                    'exp_gained' => $expGain,
                    'new_balance' => $user->fresh()->balance,
                    'new_level' => $user->fresh()->level,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit score',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get game leaderboard
     */
    public function leaderboard(Request $request, int $gameId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'period' => 'nullable|in:daily,weekly,monthly,all',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $query = GameScore::where('game_id', $gameId)
                ->with('user');

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
     * Get user's game history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $history = GameScore::where('user_id', $user->id)
                ->with('game')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Calculate stats
            $stats = [
                'total_games' => GameScore::where('user_id', $user->id)->count(),
                'wins' => GameScore::where('user_id', $user->id)->where('result', 'win')->count(),
                'losses' => GameScore::where('user_id', $user->id)->where('result', 'lose')->count(),
                'draws' => GameScore::where('user_id', $user->id)->where('result', 'draw')->count(),
                'best_score' => GameScore::where('user_id', $user->id)->max('score'),
            ];

            $stats['win_rate'] = $stats['total_games'] > 0
                ? round(($stats['wins'] / $stats['total_games']) * 100, 2)
                : 0;

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
