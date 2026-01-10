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
use App\Http\Controllers\API\GuildController;
use App\Http\Controllers\API\DatingController;
use App\Http\Controllers\API\MissionController;
use App\Http\Controllers\API\NotificationController;
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
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            Route::put('/profile', [UserController::class, 'updateProfile'])->name('update');
            Route::post('/avatar', [UserController::class, 'uploadAvatar'])->name('avatar');
            Route::post('/cover-photo', [UserController::class, 'uploadCoverPhoto'])->name('cover');
        });

        // Friends & Social
        Route::prefix('friends')->name('chat.friends.')->group(function () {
            Route::get('/', [UserController::class, 'friends'])->name('index');
            Route::post('/{userId}', [UserController::class, 'sendFriendRequest'])->name('send');
            Route::post('/{userId}/accept', [UserController::class, 'acceptFriendRequest'])->name('accept');
            Route::delete('/{userId}', [UserController::class, 'removeFriend'])->name('remove');
            Route::post('/{userId}/follow', [UserController::class, 'follow'])->name('follow');
            Route::delete('/{userId}/unfollow', [UserController::class, 'unfollow'])->name('unfollow');
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
            Route::get('/', [GiftController::class, 'index'])->name('index');
            Route::post('/send', [GiftController::class, 'send'])->name('send');
            Route::get('/history', [GiftController::class, 'history'])->name('history');
            Route::get('/top-gifters', [GiftController::class, 'topGifters'])->name('top_gifters');
            Route::get('/top-receivers', [GiftController::class, 'topReceivers'])->name('top_receivers');
        });

        // Posts & Feed
        Route::prefix('posts')->name('chat.posts.')->group(function () {
            Route::get('/feed', [PostController::class, 'feed'])->name('feed');
            Route::post('/', [PostController::class, 'create'])->name('create');
            Route::get('/{id}', [PostController::class, 'show'])->name('show');
            Route::delete('/{id}', [PostController::class, 'delete'])->name('delete');
            Route::post('/{id}/react', [PostController::class, 'react'])->name('react');
            Route::post('/{id}/comment', [PostController::class, 'comment'])->name('comment');
            Route::get('/{id}/comments', [PostController::class, 'comments'])->name('comments');
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
            Route::get('/', [GuildController::class, 'index'])->name('index');
            Route::post('/', [GuildController::class, 'store'])->name('store');
            Route::get('/{id}', [GuildController::class, 'show'])->name('show');
            Route::post('/{id}/join', [GuildController::class, 'join'])->name('join');
            Route::post('/{id}/leave', [GuildController::class, 'leave'])->name('leave');
            Route::delete('/{guildId}/members/{userId}', [GuildController::class, 'kickMember'])->name('kick');
            Route::post('/{guildId}/members/{userId}/promote', [GuildController::class, 'promoteMember'])->name('promote');
            Route::post('/{id}/donate', [GuildController::class, 'donate'])->name('donate');
            Route::get('/{id}/members', [GuildController::class, 'members'])->name('members');
        });

        // Dating
        Route::prefix('dating')->name('chat.dating.')->group(function () {
            Route::get('/profile', [DatingController::class, 'getProfile'])->name('profile');
            Route::put('/profile', [DatingController::class, 'updateProfile'])->name('update_profile');
            Route::get('/discover', [DatingController::class, 'discover'])->name('discover');
            Route::post('/swipe', [DatingController::class, 'swipe'])->name('swipe');
            Route::get('/matches', [DatingController::class, 'matches'])->name('matches');
            Route::delete('/matches/{id}', [DatingController::class, 'unmatch'])->name('unmatch');
            Route::get('/likes', [DatingController::class, 'likesReceived'])->name('likes');
        });

        // Rankings
        Route::prefix('rankings')->name('chat.rankings.')->group(function () {
            Route::get('/', [RankingController::class, 'index'])->name('index');
            Route::get('/me', [RankingController::class, 'userRank'])->name('user');
        });

        // Missions
        Route::prefix('missions')->name('chat.missions.')->group(function () {
            Route::get('/', [MissionController::class, 'index'])->name('index');
            Route::get('/my', [MissionController::class, 'myMissions'])->name('my');
            Route::post('/{id}/claim', [MissionController::class, 'claim'])->name('claim');
            Route::get('/daily', [MissionController::class, 'dailyMissions'])->name('daily');
            Route::get('/achievements', [MissionController::class, 'achievements'])->name('achievements');
        });

        // Notifications
        Route::prefix('notifications')->name('chat.notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read_all');
            Route::delete('/{id}', [NotificationController::class, 'delete'])->name('delete');
            Route::delete('/read/all', [NotificationController::class, 'deleteAllRead'])->name('delete_read');
            Route::get('/settings', [NotificationController::class, 'getSettings'])->name('settings');
            Route::put('/settings', [NotificationController::class, 'updateSettings'])->name('update_settings');
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
            Route::get('/packages', [PaymentController::class, 'vipPackages'])->name('packages');
            Route::get('/my-status', [PaymentController::class, 'myVipStatus'])->name('status');
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
