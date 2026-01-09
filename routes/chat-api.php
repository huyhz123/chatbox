<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SocialAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Chat API Routes
|--------------------------------------------------------------------------
|
| Multilingual Chat & Social Network Platform API Routes
| All routes are prefixed with /api/v1/chat
|
*/

// ============================================================================
// PUBLIC ROUTES - No authentication required
// ============================================================================

Route::prefix('v1/chat')->middleware('api')->group(function () {

    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'service' => 'chat-api',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // ========================================================================
    // AUTHENTICATION ROUTES
    // ========================================================================

    Route::prefix('auth')->name('chat.auth.')->group(function () {

        // Standard Authentication
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');

        // Email Verification
        Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->name('verification.verify');

        // Social OAuth
        Route::get('/oauth/{provider}', [SocialAuthController::class, 'redirectToProvider'])
            ->name('oauth.redirect')
            ->where('provider', 'google|facebook|apple');

        Route::get('/oauth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])
            ->name('oauth.callback')
            ->where('provider', 'google|facebook|apple');

        // Social OAuth with Token (for mobile apps)
        Route::post('/oauth/token', [SocialAuthController::class, 'loginWithToken'])
            ->name('oauth.token');

    });

    // ========================================================================
    // PROTECTED ROUTES - Requires authentication
    // ========================================================================

    Route::middleware(['auth:sanctum'])->group(function () {

        // Auth Management
        Route::prefix('auth')->name('chat.auth.')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
                ->name('verification.resend');
        });

        // User Profile & Settings
        Route::prefix('user')->name('chat.user.')->group(function () {
            // TODO: UserController routes
        });

        // Friends & Social
        Route::prefix('friends')->name('chat.friends.')->group(function () {
            // TODO: FriendController routes
        });

        // Chat & Messaging
        Route::prefix('conversations')->name('chat.conversations.')->group(function () {
            // TODO: ChatController routes
        });

        // Rooms
        Route::prefix('rooms')->name('chat.rooms.')->group(function () {
            // TODO: RoomController routes
        });

        // Live Streaming
        Route::prefix('streams')->name('chat.streams.')->group(function () {
            // TODO: LiveStreamController routes
        });

        // Gifts
        Route::prefix('gifts')->name('chat.gifts.')->group(function () {
            // TODO: GiftController routes
        });

        // Posts & Feed
        Route::prefix('posts')->name('chat.posts.')->group(function () {
            // TODO: PostController routes
        });

        // Videos (Short Videos)
        Route::prefix('videos')->name('chat.videos.')->group(function () {
            // TODO: VideoController routes
        });

        // Stories
        Route::prefix('stories')->name('chat.stories.')->group(function () {
            // TODO: StoryController routes
        });

        // Games
        Route::prefix('games')->name('chat.games.')->group(function () {
            // TODO: GameController routes
        });

        // Karaoke
        Route::prefix('karaoke')->name('chat.karaoke.')->group(function () {
            // TODO: KaraokeController routes
        });

        // Guilds
        Route::prefix('guilds')->name('chat.guilds.')->group(function () {
            // TODO: GuildController routes
        });

        // Dating
        Route::prefix('dating')->name('chat.dating.')->group(function () {
            // TODO: DatingController routes
        });

        // Rankings
        Route::prefix('rankings')->name('chat.rankings.')->group(function () {
            // TODO: RankingController routes
        });

        // Missions
        Route::prefix('missions')->name('chat.missions.')->group(function () {
            // TODO: MissionController routes
        });

        // Notifications
        Route::prefix('notifications')->name('chat.notifications.')->group(function () {
            // TODO: NotificationController routes
        });

        // Payment & Coins
        Route::prefix('payments')->name('chat.payments.')->group(function () {
            // TODO: PaymentController routes
        });

        // VIP Packages
        Route::prefix('vip')->name('chat.vip.')->group(function () {
            // TODO: VIPController routes
        });

    });

    // ========================================================================
    // ADMIN ROUTES - Requires admin role
    // ========================================================================

    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->name('chat.admin.')->group(function () {

        // Dashboard & Analytics
        Route::get('/dashboard', function () {
            // TODO: AdminController@dashboard
            return response()->json(['message' => 'Admin dashboard']);
        });

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            // TODO: Admin User Management
        });

        // Content Moderation
        Route::prefix('moderation')->name('moderation.')->group(function () {
            // TODO: Content Moderation
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            // TODO: Report Management
        });

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            // TODO: App Settings
        });

    });

});

// ============================================================================
// WEBHOOKS - Payment gateways
// ============================================================================

Route::prefix('webhooks')->name('chat.webhooks.')->group(function () {

    // VNPAY
    Route::post('/vnpay', function () {
        // TODO: VNPAY webhook handler
    })->name('vnpay');

    // MoMo
    Route::post('/momo', function () {
        // TODO: MoMo webhook handler
    })->name('momo');

    // ZaloPay
    Route::post('/zalopay', function () {
        // TODO: ZaloPay webhook handler
    })->name('zalopay');

});

// 404 Handler
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Chat API endpoint not found',
        'status' => 404
    ], 404);
});
