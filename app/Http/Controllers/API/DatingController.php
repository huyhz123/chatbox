<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DatingProfile;
use App\Models\DatingSwipe;
use App\Models\DatingMatch;
use App\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DatingController extends Controller
{
    /**
     * Get or create user's dating profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $profile = DatingProfile::where('user_id', $user->id)->first();

            if (!$profile) {
                // Create default profile
                $profile = DatingProfile::create([
                    'user_id' => $user->id,
                    'bio' => '',
                    'photos' => json_encode([]),
                    'looking_for' => 'all',
                    'age_min' => 18,
                    'age_max' => 99,
                    'max_distance' => 100,
                    'is_active' => false,
                ]);
            }

            return response()->json([
                'success' => true,
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update dating profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'bio' => 'nullable|string|max:500',
                'photos' => 'nullable|array|max:6',
                'photos.*' => 'url',
                'looking_for' => 'required|in:male,female,all',
                'age_min' => 'required|integer|min:18|max:99',
                'age_max' => 'required|integer|min:18|max:99|gte:age_min',
                'max_distance' => 'required|integer|min:1|max:500',
                'interests' => 'nullable|array',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $profile = DatingProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $request->bio,
                    'photos' => json_encode($request->photos ?? []),
                    'looking_for' => $request->looking_for,
                    'age_min' => $request->age_min,
                    'age_max' => $request->age_max,
                    'max_distance' => $request->max_distance,
                    'interests' => json_encode($request->interests ?? []),
                    'is_active' => $request->is_active ?? true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get potential matches (swipe cards)
     */
    public function discover(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $profile = DatingProfile::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please activate your dating profile first',
                ], 400);
            }

            // Get users already swiped
            $swipedUserIds = DatingSwipe::where('user_id', $user->id)
                ->pluck('target_user_id')
                ->toArray();

            // Build query for potential matches
            $query = ChatUser::whereHas('datingProfile', function ($q) use ($profile) {
                $q->where('is_active', true);

                // Gender preference
                if ($profile->looking_for !== 'all') {
                    $q->whereHas('user', function ($uq) use ($profile) {
                        $uq->where('gender', $profile->looking_for);
                    });
                }
            })
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $swipedUserIds);

            // Age filter
            $query->whereBetween('age', [$profile->age_min, $profile->age_max]);

            // Get random potential matches
            $potentialMatches = $query->with('datingProfile')
                ->inRandomOrder()
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'matches' => $potentialMatches,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch matches',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Swipe on a user
     */
    public function swipe(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'target_user_id' => 'required|exists:chat_users,id',
                'action' => 'required|in:like,pass',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Cannot swipe on self
            if ($user->id == $request->target_user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot swipe on yourself',
                ], 400);
            }

            // Check if already swiped
            $existingSwipe = DatingSwipe::where('user_id', $user->id)
                ->where('target_user_id', $request->target_user_id)
                ->first();

            if ($existingSwipe) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already swiped on this user',
                ], 400);
            }

            // Record swipe
            $swipe = DatingSwipe::create([
                'user_id' => $user->id,
                'target_user_id' => $request->target_user_id,
                'action' => $request->action,
            ]);

            $isMatch = false;

            // Check for mutual like (match)
            if ($request->action === 'like') {
                $mutualSwipe = DatingSwipe::where('user_id', $request->target_user_id)
                    ->where('target_user_id', $user->id)
                    ->where('action', 'like')
                    ->first();

                if ($mutualSwipe) {
                    // Create match
                    DatingMatch::create([
                        'user1_id' => min($user->id, $request->target_user_id),
                        'user2_id' => max($user->id, $request->target_user_id),
                    ]);

                    $isMatch = true;
                }
            }

            return response()->json([
                'success' => true,
                'is_match' => $isMatch,
                'message' => $isMatch ? "It's a match!" : 'Swipe recorded',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to swipe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's matches
     */
    public function matches(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $matches = DatingMatch::where(function ($q) use ($user) {
                $q->where('user1_id', $user->id)
                  ->orWhere('user2_id', $user->id);
            })
            ->with(['user1', 'user2'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

            // Format matches to show the other user
            $formattedMatches = $matches->map(function ($match) use ($user) {
                $otherUser = $match->user1_id === $user->id ? $match->user2 : $match->user1;

                return [
                    'id' => $match->id,
                    'user' => $otherUser,
                    'matched_at' => $match->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'matches' => $formattedMatches,
                'pagination' => [
                    'current_page' => $matches->currentPage(),
                    'total' => $matches->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch matches',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unmatch with a user
     */
    public function unmatch(Request $request, int $matchId): JsonResponse
    {
        try {
            $user = $request->user();

            $match = DatingMatch::where('id', $matchId)
                ->where(function ($q) use ($user) {
                    $q->where('user1_id', $user->id)
                      ->orWhere('user2_id', $user->id);
                })
                ->first();

            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found',
                ], 404);
            }

            $match->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unmatched successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unmatch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get likes received (who liked you)
     */
    public function likesReceived(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Users who liked current user
            $likes = DatingSwipe::where('target_user_id', $user->id)
                ->where('action', 'like')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Check if current user already swiped on them
            $likesWithStatus = $likes->map(function ($like) use ($user) {
                $userSwipe = DatingSwipe::where('user_id', $user->id)
                    ->where('target_user_id', $like->user_id)
                    ->first();

                return [
                    'user' => $like->user,
                    'liked_at' => $like->created_at,
                    'already_swiped' => $userSwipe !== null,
                    'is_match' => $userSwipe && $userSwipe->action === 'like',
                ];
            });

            return response()->json([
                'success' => true,
                'likes' => $likesWithStatus,
                'pagination' => [
                    'current_page' => $likes->currentPage(),
                    'total' => $likes->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch likes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
