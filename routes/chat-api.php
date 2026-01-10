<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SocialAuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\GiftController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\LiveStreamController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\API\StoryController;
use App\Http\Controllers\API\KaraokeController;
use App\Http\Controllers\API\GameController;
use App\Http\Controllers\API\RankingController;
use App\Http\Controllers\API\PaymentController;
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
            Route::get('/', [ChatController::class, 'conversations'])->name('index');
            Route::get('/unread-count', [ChatController::class, 'unreadCount'])->name('unread');
            Route::post('/{userId}', [ChatController::class, 'getOrCreateConversation'])->name('create');
            Route::get('/{id}/messages', [ChatController::class, 'getMessages'])->name('messages');
            Route::post('/{id}/messages', [ChatController::class, 'sendMessage'])->name('send');
            Route::post('/{id}/read', [ChatController::class, 'markAsRead'])->name('read');
            Route::get('/{id}/search', [ChatController::class, 'searchMessages'])->name('search');
            Route::delete('/messages/{id}', [ChatController::class, 'deleteMessage'])->name('message.delete');
        });

        // Rooms
        Route::prefix('rooms')->name('chat.rooms.')->group(function () {
            Route::get('/', [RoomController::class, 'index'])->name('index');
            Route::post('/', [RoomController::class, 'store'])->name('store');
            Route::get('/{id}', [RoomController::class, 'show'])->name('show');
            Route::post('/{id}/join', [RoomController::class, 'join'])->name('join');
            Route::post('/{id}/leave', [RoomController::class, 'leave'])->name('leave');
            Route::post('/{id}/toggle-mute', [RoomController::class, 'toggleMute'])->name('mute');
            Route::delete('/{roomId}/users/{userId}', [RoomController::class, 'kickUser'])->name('kick');
        });

        // Live Streaming
        Route::prefix('streams')->name('chat.streams.')->group(function () {
            Route::get('/', [LiveStreamController::class, 'index'])->name('index');
            Route::get('/{id}', [LiveStreamController::class, 'show'])->name('show');
            Route::post('/', [LiveStreamController::class, 'start'])->name('start');
            Route::post('/{id}/end', [LiveStreamController::class, 'end'])->name('end');
            Route::post('/{id}/join', [LiveStreamController::class, 'join'])->name('join');
            Route::post('/{id}/gift', [LiveStreamController::class, 'sendGift'])->name('gift');
            Route::post('/{id}/pk', [LiveStreamController::class, 'startPK'])->name('pk');
            Route::get('/history', [LiveStreamController::class, 'history'])->name('history');
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
            Route::get('/feed', [VideoController::class, 'feed'])->name('feed');
            Route::get('/user/{userId}', [VideoController::class, 'userVideos'])->name('user');
            Route::post('/', [VideoController::class, 'upload'])->name('upload');
            Route::post('/{id}/like', [VideoController::class, 'toggleLike'])->name('like');
            Route::post('/{id}/view', [VideoController::class, 'incrementView'])->name('view');
            Route::get('/{id}/comments', [VideoController::class, 'getComments'])->name('comments');
            Route::post('/{id}/comments', [VideoController::class, 'addComment'])->name('comment');
            Route::delete('/{id}', [VideoController::class, 'delete'])->name('delete');
        });

        // Stories
        Route::prefix('stories')->name('chat.stories.')->group(function () {
            Route::get('/', [StoryController::class, 'index'])->name('index');
            Route::get('/user/{userId}', [StoryController::class, 'userStories'])->name('user');
            Route::post('/', [StoryController::class, 'store'])->name('store');
            Route::post('/{id}/view', [StoryController::class, 'view'])->name('view');
            Route::get('/{id}/viewers', [StoryController::class, 'viewers'])->name('viewers');
            Route::delete('/{id}', [StoryController::class, 'delete'])->name('delete');
        });

        // Games
        Route::prefix('games')->name('chat.games.')->group(function () {
            Route::get('/', [GameController::class, 'index'])->name('index');
            Route::get('/{id}', [GameController::class, 'show'])->name('show');
            Route::post('/{id}/start', [GameController::class, 'startSession'])->name('start');
            Route::post('/sessions/{id}/score', [GameController::class, 'submitScore'])->name('score');
            Route::get('/{id}/leaderboard', [GameController::class, 'leaderboard'])->name('leaderboard');
            Route::get('/history', [GameController::class, 'history'])->name('history');
        });

        // Karaoke
        Route::prefix('karaoke')->name('chat.karaoke.')->group(function () {
            Route::get('/songs', [KaraokeController::class, 'songs'])->name('songs');
            Route::post('/start', [KaraokeController::class, 'startSession'])->name('start');
            Route::post('/sessions/{id}/score', [KaraokeController::class, 'submitScore'])->name('score');
            Route::get('/leaderboard', [KaraokeController::class, 'leaderboard'])->name('leaderboard');
            Route::get('/history', [KaraokeController::class, 'history'])->name('history');
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
            Route::get('/', [RankingController::class, 'index'])->name('index');
            Route::get('/me', [RankingController::class, 'userRank'])->name('user');
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
            Route::get('/packages/coins', [PaymentController::class, 'coinPackages'])->name('packages.coins');
            Route::post('/purchase/coins', [PaymentController::class, 'purchaseCoins'])->name('purchase.coins');
            Route::post('/purchase/vip', [PaymentController::class, 'purchaseVip'])->name('purchase.vip');
            Route::get('/history', [PaymentController::class, 'transactionHistory'])->name('history');
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
    Route::any('/vnpay', [PaymentController::class, 'vnpayCallback'])->name('vnpay');

    // MoMo
    Route::any('/momo', [PaymentController::class, 'momoCallback'])->name('momo');

    // ZaloPay
    Route::any('/zalopay', [PaymentController::class, 'zaloPayCallback'])->name('zalopay');

});

// 404 Handler
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Chat API endpoint not found',
        'status' => 404
    ], 404);
});
