<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatUser;
use App\Models\Friendship;
use App\Models\Follow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = ChatUser::with(['badges', 'guild.leader', 'datingProfile'])
                ->findOrFail($id);

            $currentUser = $request->user();
            $isOwnProfile = $currentUser && $currentUser->id == $user->id;
            $isFriend = $currentUser && $currentUser->friends()->where('friend_id', $user->id)->exists();
            $isFollowing = $currentUser && $currentUser->following()->where('following_id', $user->id)->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $this->formatUserProfile($user, $isOwnProfile),
                    'stats' => [
                        'friends_count' => $user->friends()->count(),
                        'followers_count' => $user->followers()->count(),
                        'following_count' => $user->following()->count(),
                        'posts_count' => $user->posts()->count(),
                        'videos_count' => $user->videos()->count(),
                    ],
                    'relationship' => [
                        'is_own_profile' => $isOwnProfile,
                        'is_friend' => $isFriend,
                        'is_following' => $isFollowing,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'full_name' => 'sometimes|string|max:100',
                'bio' => 'sometimes|string|max:500',
                'gender' => 'sometimes|in:male,female,other',
                'birthday' => 'sometimes|date|before:today',
                'country_code' => 'sometimes|string|size:2',
                'language' => 'sometimes|in:vi,en',
                'status_text' => 'sometimes|string|max:255',
                'online_status' => 'sometimes|in:online,busy,away,invisible',
            ]);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => ['user' => $this->formatUserProfile($user, true)],
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
     * Upload avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ]);

        try {
            $user = $request->user();

            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'data' => [
                    'avatar_url' => url(Storage::url($path)),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload cover photo
     */
    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB
        ]);

        try {
            $user = $request->user();

            if ($user->cover_photo) {
                Storage::disk('public')->delete($user->cover_photo);
            }

            $path = $request->file('cover')->store('covers', 'public');
            $user->update(['cover_photo' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Cover photo uploaded successfully',
                'data' => ['cover_url' => url(Storage::url($path))],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload cover photo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send friend request
     */
    public function sendFriendRequest(Request $request, $userId): JsonResponse
    {
        try {
            $currentUser = $request->user();
            $targetUser = ChatUser::findOrFail($userId);

            if ($currentUser->id == $targetUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send friend request to yourself',
                ], 400);
            }

            // Check if already friends or pending
            $existing = Friendship::where(function($q) use ($currentUser, $targetUser) {
                $q->where('user_id', $currentUser->id)->where('friend_id', $targetUser->id);
            })->orWhere(function($q) use ($currentUser, $targetUser) {
                $q->where('user_id', $targetUser->id)->where('friend_id', $currentUser->id);
            })->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Friend request already exists or already friends',
                ], 400);
            }

            Friendship::create([
                'user_id' => $currentUser->id,
                'friend_id' => $targetUser->id,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Friend request sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send friend request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept friend request
     */
    public function acceptFriendRequest(Request $request, $friendshipId): JsonResponse
    {
        try {
            $friendship = Friendship::where('friend_id', $request->user()->id)
                ->where('id', $friendshipId)
                ->where('status', 'pending')
                ->firstOrFail();

            $friendship->update(['status' => 'accepted']);

            return response()->json([
                'success' => true,
                'message' => 'Friend request accepted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept friend request',
            ], 404);
        }
    }

    /**
     * Follow user
     */
    public function follow(Request $request, $userId): JsonResponse
    {
        try {
            $currentUser = $request->user();
            $targetUser = ChatUser::findOrFail($userId);

            if ($currentUser->id == $targetUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot follow yourself',
                ], 400);
            }

            Follow::firstOrCreate([
                'follower_id' => $currentUser->id,
                'following_id' => $targetUser->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully followed user',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to follow user',
            ], 500);
        }
    }

    /**
     * Unfollow user
     */
    public function unfollow(Request $request, $userId): JsonResponse
    {
        try {
            Follow::where('follower_id', $request->user()->id)
                ->where('following_id', $userId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Successfully unfollowed user',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unfollow user',
            ], 500);
        }
    }

    /**
     * Get friends list
     */
    public function friends(Request $request): JsonResponse
    {
        try {
            $friends = $request->user()
                ->friends()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $friends,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch friends',
            ], 500);
        }
    }

    /**
     * Get followers list
     */
    public function followers(Request $request): JsonResponse
    {
        try {
            $followers = $request->user()
                ->followers()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $followers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch followers',
            ], 500);
        }
    }

    /**
     * Format user profile response
     */
    private function formatUserProfile(ChatUser $user, bool $detailed = false): array
    {
        $data = [
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'avatar' => $user->avatar ? url(Storage::url($user->avatar)) : null,
            'cover_photo' => $user->cover_photo ? url(Storage::url($user->cover_photo)) : null,
            'bio' => $user->bio,
            'level' => $user->level,
            'exp' => $user->exp,
            'vip_level' => $user->vip_level,
            'is_vip' => $user->isVip(),
            'is_verified' => $user->is_verified,
            'is_online' => $user->is_online,
            'last_seen' => $user->last_seen?->diffForHumans(),
        ];

        if ($detailed) {
            $data['email'] = $user->email;
            $data['phone'] = $user->phone;
            $data['gender'] = $user->gender;
            $data['birthday'] = $user->birthday?->format('Y-m-d');
            $data['country_code'] = $user->country_code;
            $data['language'] = $user->language;
            $data['balance'] = (float) $user->balance;
            $data['total_spent'] = (float) $user->total_spent;
            $data['status_text'] = $user->status_text;
            $data['online_status'] = $user->online_status;
        }

        return $data;
    }
}
