<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\UserMission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    /**
     * Get available missions
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $missions = Mission::where('is_active', true)
                ->orderBy('type', 'asc')
                ->orderBy('required_count', 'asc')
                ->get();

            // Get user's mission progress
            $userMissions = UserMission::where('user_id', $user->id)
                ->pluck('progress', 'mission_id')
                ->toArray();

            $userMissionStatuses = UserMission::where('user_id', $user->id)
                ->pluck('status', 'mission_id')
                ->toArray();

            // Format missions with progress
            $formattedMissions = $missions->map(function ($mission) use ($userMissions, $userMissionStatuses) {
                $progress = $userMissions[$mission->id] ?? 0;
                $status = $userMissionStatuses[$mission->id] ?? 'not_started';

                return [
                    'id' => $mission->id,
                    'name' => $mission->name,
                    'description' => $mission->description,
                    'type' => $mission->type,
                    'required_count' => $mission->required_count,
                    'current_progress' => $progress,
                    'status' => $status,
                    'rewards' => json_decode($mission->rewards, true),
                    'icon' => $mission->icon,
                    'is_completed' => $progress >= $mission->required_count,
                    'can_claim' => $progress >= $mission->required_count && $status !== 'claimed',
                ];
            });

            // Group by type
            $groupedMissions = $formattedMissions->groupBy('type');

            return response()->json([
                'success' => true,
                'missions' => $groupedMissions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch missions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's mission progress
     */
    public function myMissions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $userMissions = UserMission::where('user_id', $user->id)
                ->with('mission')
                ->get();

            return response()->json([
                'success' => true,
                'missions' => $userMissions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch missions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Claim mission reward
     */
    public function claim(Request $request, int $missionId): JsonResponse
    {
        try {
            $user = $request->user();

            $mission = Mission::find($missionId);
            if (!$mission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mission not found',
                ], 404);
            }

            $userMission = UserMission::where('user_id', $user->id)
                ->where('mission_id', $missionId)
                ->first();

            if (!$userMission) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have not started this mission',
                ], 400);
            }

            if ($userMission->status === 'claimed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward already claimed',
                ], 400);
            }

            if ($userMission->progress < $mission->required_count) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mission not completed yet',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Mark as claimed
                $userMission->update([
                    'status' => 'claimed',
                    'completed_at' => now(),
                ]);

                // Give rewards
                $rewards = json_decode($mission->rewards, true);

                if (isset($rewards['coins'])) {
                    $user->addCoins($rewards['coins'], "Mission reward: {$mission->name}");
                }

                if (isset($rewards['exp'])) {
                    $user->addExp($rewards['exp']);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Reward claimed successfully',
                    'rewards' => $rewards,
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
                'message' => 'Failed to claim reward',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update mission progress (internal use)
     */
    public function updateProgress(int $userId, string $missionType, int $increment = 1): void
    {
        try {
            // Find missions of this type
            $missions = Mission::where('type', $missionType)
                ->where('is_active', true)
                ->get();

            foreach ($missions as $mission) {
                $userMission = UserMission::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'mission_id' => $mission->id,
                    ],
                    [
                        'progress' => 0,
                        'status' => 'in_progress',
                    ]
                );

                // Only update if not claimed
                if ($userMission->status !== 'claimed') {
                    $newProgress = min($userMission->progress + $increment, $mission->required_count);
                    $userMission->update(['progress' => $newProgress]);
                }
            }
        } catch (\Exception $e) {
            // Silent fail for mission updates
            \Log::error('Failed to update mission progress: ' . $e->getMessage());
        }
    }

    /**
     * Get daily missions
     */
    public function dailyMissions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $missions = Mission::where('type', 'daily')
                ->where('is_active', true)
                ->get();

            // Get today's progress
            $userMissions = UserMission::where('user_id', $user->id)
                ->whereIn('mission_id', $missions->pluck('id'))
                ->whereDate('updated_at', today())
                ->get()
                ->keyBy('mission_id');

            $formattedMissions = $missions->map(function ($mission) use ($userMissions) {
                $userMission = $userMissions->get($mission->id);

                return [
                    'id' => $mission->id,
                    'name' => $mission->name,
                    'description' => $mission->description,
                    'required_count' => $mission->required_count,
                    'current_progress' => $userMission ? $userMission->progress : 0,
                    'status' => $userMission ? $userMission->status : 'not_started',
                    'rewards' => json_decode($mission->rewards, true),
                    'is_completed' => $userMission && $userMission->progress >= $mission->required_count,
                    'can_claim' => $userMission && $userMission->progress >= $mission->required_count && $userMission->status !== 'claimed',
                ];
            });

            return response()->json([
                'success' => true,
                'missions' => $formattedMissions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch daily missions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get achievement missions
     */
    public function achievements(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $missions = Mission::whereIn('type', ['achievement', 'milestone'])
                ->where('is_active', true)
                ->get();

            $userMissions = UserMission::where('user_id', $user->id)
                ->whereIn('mission_id', $missions->pluck('id'))
                ->get()
                ->keyBy('mission_id');

            $formattedMissions = $missions->map(function ($mission) use ($userMissions) {
                $userMission = $userMissions->get($mission->id);

                return [
                    'id' => $mission->id,
                    'name' => $mission->name,
                    'description' => $mission->description,
                    'required_count' => $mission->required_count,
                    'current_progress' => $userMission ? $userMission->progress : 0,
                    'status' => $userMission ? $userMission->status : 'not_started',
                    'rewards' => json_decode($mission->rewards, true),
                    'is_completed' => $userMission && $userMission->progress >= $mission->required_count,
                    'can_claim' => $userMission && $userMission->progress >= $mission->required_count && $userMission->status !== 'claimed',
                ];
            });

            return response()->json([
                'success' => true,
                'achievements' => $formattedMissions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch achievements',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
