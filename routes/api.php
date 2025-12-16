<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================================================
// API HEALTH CHECK & VERSION
// ============================================================================

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
    ]);
})->name('health');

// ============================================================================
// PUBLIC API ROUTES - No authentication required
// ============================================================================

Route::middleware('api')->group(function () {

    // Authentication Routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
        Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email');
        Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('resend-verification');
    });

    // Public Products Endpoint
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/category/{category}', [ProductController::class, 'byCategory'])->name('by-category');
        Route::get('/search/{query}', [ProductController::class, 'search'])->name('search');
        Route::get('/{product}/reviews', [ProductController::class, 'reviews'])->name('reviews');
    });

    // Public Services Endpoint
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('/{service}', [ServiceController::class, 'show'])->name('show');
    });

    // Public Courses Endpoint
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/{course}', [CourseController::class, 'show'])->name('show');
        Route::get('/{course}/lessons', [CourseController::class, 'lessons'])->name('lessons');
    });

    // Public Files Endpoint
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', [FileController::class, 'index'])->name('index');
        Route::get('/{file}', [FileController::class, 'show'])->name('show');
    });

    // ========================================================================
    // CHATBOT API - Public endpoint for chatbot interactions
    // ========================================================================

    Route::prefix('chatbot')->name('chatbot.')->group(function () {
        Route::post('/message', [ChatbotController::class, 'sendMessage'])->name('message');
        Route::get('/conversation/{id}', [ChatbotController::class, 'getConversation'])->name('conversation');
        Route::post('/conversation', [ChatbotController::class, 'createConversation'])->name('create-conversation');
        Route::delete('/conversation/{id}', [ChatbotController::class, 'deleteConversation'])->name('delete-conversation');
        Route::post('/feedback', [ChatbotController::class, 'submitFeedback'])->name('feedback');
    });

    // ========================================================================
    // WEBHOOK ENDPOINTS - Payment systems and external integrations
    // ========================================================================

    // Stripe Webhook
    Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhook.stripe');

    // Payment Status Webhook
    Route::post('/webhooks/payment-status', [WebhookController::class, 'paymentStatus'])->name('webhook.payment-status');

    // DHRU Payment Gateway Webhook
    Route::post('/webhooks/dhru', [WebhookController::class, 'dhru'])->name('webhook.dhru');

    // GSM Payment Gateway Webhook
    Route::post('/webhooks/gsm', [WebhookController::class, 'gsm'])->name('webhook.gsm');

    // Generic Webhook Handler
    Route::post('/webhooks/{provider}', [WebhookController::class, 'handle'])->name('webhook.generic');

    // ========================================================================
    // AUTHENTICATED API ROUTES - Requires bearer token (Sanctum)
    // ========================================================================

    Route::middleware(['auth:sanctum'])->group(function () {

        // User Profile & Account
        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/', [UserController::class, 'profile'])->name('profile');
            Route::put('/', [UserController::class, 'updateProfile'])->name('update');
            Route::post('/avatar', [UserController::class, 'updateAvatar'])->name('avatar');
            Route::post('/password', [UserController::class, 'changePassword'])->name('password');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/tokens', [AuthController::class, 'tokens'])->name('tokens');
            Route::post('/tokens', [AuthController::class, 'createToken'])->name('create-token');
            Route::delete('/tokens/{token}', [AuthController::class, 'deleteToken'])->name('delete-token');
        });

        // Cart Management
        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add', [CartController::class, 'add'])->name('add');
            Route::put('/{item}', [CartController::class, 'update'])->name('update');
            Route::delete('/{item}', [CartController::class, 'remove'])->name('remove');
            Route::delete('/', [CartController::class, 'clear'])->name('clear');
        });

        // Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice');
        });

        // Payments
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::post('/{payment}/retry', [PaymentController::class, 'retry'])->name('retry');
            Route::post('/{payment}/refund', [PaymentController::class, 'refund'])->name('refund');
            Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
        });

        // Course Enrollment & Learning
        Route::prefix('courses')->name('courses.')->group(function () {
            Route::get('/my-courses', [CourseController::class, 'myCourses'])->name('my-courses');
            Route::post('/{course}/enroll', [CourseController::class, 'enroll'])->name('enroll');
            Route::get('/{course}/progress', [CourseController::class, 'progress'])->name('progress');
            Route::post('/{course}/lesson/{lesson}/complete', [CourseController::class, 'completeLesson'])->name('lesson.complete');
            Route::get('/{course}/certificate', [CourseController::class, 'certificate'])->name('certificate');
        });

        // File Downloads
        Route::prefix('files')->name('files.')->group(function () {
            Route::get('/my-files', [FileController::class, 'myFiles'])->name('my-files');
            Route::post('/{file}/download', [FileController::class, 'download'])->name('download');
        });

        // Chatbot - Authenticated User Context
        Route::prefix('chatbot')->name('chatbot.')->group(function () {
            Route::get('/my-conversations', [ChatbotController::class, 'myConversations'])->name('my-conversations');
            Route::get('/conversation/{id}', [ChatbotController::class, 'getConversation'])->name('conversation');
            Route::post('/conversation', [ChatbotController::class, 'createConversation'])->name('create-conversation');
            Route::delete('/conversation/{id}', [ChatbotController::class, 'deleteConversation'])->name('delete-conversation');
        });

    });

});

// ============================================================================
// OPTIONAL: MOBILE APP ROUTES
// ============================================================================

Route::prefix('mobile')->name('mobile.')->group(function () {

    // Mobile Authentication
    Route::middleware('api')->group(function () {
        Route::post('/auth/login', [AuthController::class, 'mobileLogin'])->name('auth.login');
        Route::post('/auth/register', [AuthController::class, 'mobileRegister'])->name('auth.register');
        Route::post('/auth/refresh', [AuthController::class, 'refreshToken'])->name('auth.refresh');
    });

    // Mobile Authenticated Routes
    Route::middleware(['auth:sanctum', 'api'])->group(function () {
        Route::get('/dashboard', [UserController::class, 'mobileDashboard'])->name('dashboard');
        Route::get('/notifications', [UserController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/{id}/read', [UserController::class, 'markNotificationRead'])->name('notification.read');
    });

});

// ============================================================================
// ADMIN API ROUTES - Requires API token with admin role
// ============================================================================

Route::middleware(['auth:sanctum', 'role:admin|super-admin', 'api'])->prefix('admin')->name('admin.')->group(function () {

    // Admin Dashboard Stats
    Route::get('/stats', [App\Http\Controllers\Api\Admin\DashboardController::class, 'stats'])->name('stats');
    Route::get('/activity-logs', [App\Http\Controllers\Api\Admin\DashboardController::class, 'activityLogs'])->name('activity-logs');

    // Admin User Management
    Route::apiResource('users', App\Http\Controllers\Api\Admin\UserController::class);
    Route::patch('/users/{user}/toggle-status', [App\Http\Controllers\Api\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Admin Product Management
    Route::apiResource('products', App\Http\Controllers\Api\Admin\ProductController::class);

    // Admin Order Management
    Route::apiResource('orders', App\Http\Controllers\Api\Admin\OrderController::class, ['except' => ['store']]);
    Route::patch('/orders/{order}/status', [App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Admin Payment Management
    Route::apiResource('payments', App\Http\Controllers\Api\Admin\PaymentController::class, ['except' => ['store']]);
    Route::patch('/payments/{payment}/status', [App\Http\Controllers\Api\Admin\PaymentController::class, 'updateStatus'])->name('payments.update-status');

});

// ============================================================================
// ERROR HANDLING
// ============================================================================

// 404 Handler
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint not found',
        'status' => 404
    ], 404);
});
