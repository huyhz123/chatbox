<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\ChatUser;
use App\Models\UserPrivacySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = ChatUser::create([
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'full_name' => $request->full_name,
                'gender' => $request->gender,
                'birthday' => $request->birthday,
                'country_code' => $request->country_code ?? 'VN',
                'language' => $request->language ?? 'vi',
                'level' => 1,
                'exp' => 0,
                'vip_level' => 0,
                'balance' => 0,
                'is_online' => false,
            ]);

            // Create default privacy settings
            UserPrivacySetting::create([
                'user_id' => $user->id,
                'profile_visibility' => 'public',
                'who_can_message' => 'everyone',
                'who_can_call' => 'friends',
                'show_online_status' => true,
                'show_last_seen' => true,
                'show_read_receipts' => true,
                'allow_tags' => true,
                'allow_mentions' => true,
                'show_location' => false,
                'allow_friend_requests' => true,
            ]);

            // Give welcome bonus
            $user->addCoins(100, 'Welcome bonus');

            // Send email verification (if configured)
            if (config('chat.email_verification_required')) {
                $user->sendEmailVerificationNotification();
            }

            // Create access token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Find user by username, email, or phone
            $user = ChatUser::where(function ($query) use ($request) {
                $query->where('username', $request->login)
                    ->orWhere('email', $request->login)
                    ->orWhere('phone', $request->login);
            })->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'login' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Check if user is banned
            if ($user->is_banned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been banned. Please contact support.',
                ], 403);
            }

            // Update online status
            $user->update([
                'is_online' => true,
                'last_seen' => now(),
            ]);

            // Create access token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Update online status
            $user->update([
                'is_online' => false,
                'last_seen' => now(),
            ]);

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Load relationships
            $user->load([
                'privacySettings',
                'badges' => function ($query) {
                    $query->where('is_equipped', true);
                },
                'datingProfile',
                'guild.leader',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $this->formatUserResponse($user, true),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Revoke old token
            $request->user()->currentAccessToken()->delete();

            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $user = ChatUser::findOrFail($request->id);

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified',
                ], 400);
            }

            $user->markEmailAsVerified();

            // Give verification bonus
            $user->addCoins(50, 'Email verification bonus');
            $user->addExp(100);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => [
                    'bonus_coins' => 50,
                    'bonus_exp' => 100,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email verification failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified',
                ], 400);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format user response
     */
    private function formatUserResponse(ChatUser $user, bool $detailed = false): array
    {
        $data = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'full_name' => $user->full_name,
            'avatar' => $user->avatar ? url($user->avatar) : null,
            'cover_photo' => $user->cover_photo ? url($user->cover_photo) : null,
            'bio' => $user->bio,
            'gender' => $user->gender,
            'birthday' => $user->birthday?->format('Y-m-d'),
            'country_code' => $user->country_code,
            'language' => $user->language,
            'level' => $user->level,
            'exp' => $user->exp,
            'vip_level' => $user->vip_level,
            'vip_expire_at' => $user->vip_expire_at?->toIso8601String(),
            'is_vip' => $user->isVip(),
            'balance' => (float) $user->balance,
            'status_text' => $user->status_text,
            'online_status' => $user->online_status,
            'is_online' => $user->is_online,
            'last_seen' => $user->last_seen?->diffForHumans(),
            'is_verified' => $user->is_verified,
            'created_at' => $user->created_at->toIso8601String(),
        ];

        if ($detailed) {
            $data['privacy_settings'] = $user->privacySettings;
            $data['equipped_badges'] = $user->badges;
            $data['dating_profile'] = $user->datingProfile;
            $data['guild'] = $user->guild->first();
            $data['stats'] = [
                'total_spent' => (float) $user->total_spent,
                'friends_count' => $user->friends()->count(),
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
            ];
        }

        return $data;
    }
}
