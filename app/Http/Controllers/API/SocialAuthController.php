<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatUser;
use App\Models\UserPrivacySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to social provider
     */
    public function redirectToProvider(string $provider): JsonResponse
    {
        try {
            $this->validateProvider($provider);

            $redirectUrl = Socialite::driver($provider)
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'success' => true,
                'data' => [
                    'redirect_url' => $redirectUrl,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to redirect to provider',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle callback from social provider
     */
    public function handleProviderCallback(string $provider, Request $request): JsonResponse
    {
        try {
            $this->validateProvider($provider);

            // Get user from provider
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Find or create user
            $user = $this->findOrCreateUser($provider, $socialUser);

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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login with social token (for mobile apps)
     */
    public function loginWithToken(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,apple',
            'access_token' => 'required|string',
        ]);

        try {
            $provider = $request->provider;
            $this->validateProvider($provider);

            // Get user from provider using token
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->userFromToken($request->access_token);

            // Find or create user
            $user = $this->findOrCreateUser($provider, $socialUser);

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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find or create user from social provider
     */
    private function findOrCreateUser(string $provider, $socialUser): ChatUser
    {
        // Try to find user by email
        $user = null;
        if ($socialUser->getEmail()) {
            $user = ChatUser::where('email', $socialUser->getEmail())->first();
        }

        // If user not found, create new one
        if (!$user) {
            // Generate unique username from email or name
            $username = $this->generateUniqueUsername(
                $socialUser->getNickname()
                ?? $socialUser->getName()
                ?? explode('@', $socialUser->getEmail())[0]
            );

            $user = ChatUser::create([
                'username' => $username,
                'email' => $socialUser->getEmail(),
                'full_name' => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
                'password' => Hash::make(Str::random(32)), // Random password
                'email_verified_at' => now(), // Social accounts are pre-verified
                'level' => 1,
                'exp' => 0,
                'vip_level' => 0,
                'balance' => 0,
                'is_verified' => true,
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
            $user->addExp(100);
        }

        return $user;
    }

    /**
     * Generate unique username
     */
    private function generateUniqueUsername(string $base): string
    {
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $base));
        $username = substr($username, 0, 20); // Limit length

        // Check if username exists
        $originalUsername = $username;
        $counter = 1;

        while (ChatUser::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Validate provider
     */
    private function validateProvider(string $provider): void
    {
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            throw new \InvalidArgumentException('Invalid provider');
        }
    }

    /**
     * Format user response
     */
    private function formatUserResponse(ChatUser $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'avatar' => $user->avatar ? url($user->avatar) : null,
            'level' => $user->level,
            'exp' => $user->exp,
            'vip_level' => $user->vip_level,
            'is_vip' => $user->isVip(),
            'balance' => (float) $user->balance,
            'is_verified' => $user->is_verified,
            'is_online' => $user->is_online,
        ];
    }
}
