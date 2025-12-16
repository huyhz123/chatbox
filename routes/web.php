<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\ServiceController;
use App\Http\Controllers\Frontend\FileController;
use App\Http\Controllers\Frontend\CourseController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\ChatbotController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\FileController as AdminFileController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// PUBLIC ROUTES - No authentication required
// ============================================================================

Route::middleware(['web', 'locale'])->group(function () {

    // Language Switcher
    Route::get('/language/{lang}', function ($lang) {
        if (in_array($lang, ['vi', 'en', 'zh'])) {
            session(['locale' => $lang]);
            auth()->user()?->update(['preferred_language' => $lang]);
        }
        return redirect()->back();
    })->name('language.switch');

    // Homepage & General Pages
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::post('/contact', [HomeController::class, 'storeContact'])->name('contact.store');

    // Public Product Browsing
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/category/{category}', [ProductController::class, 'byCategory'])->name('products.category');
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

    // Public Service Browsing
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

    // Public File Browsing
    Route::get('/files', [FileController::class, 'index'])->name('files.index');
    Route::get('/files/{file}', [FileController::class, 'show'])->name('files.show');

    // Public Course Browsing
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

    // Public Chatbot
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');

    // ========================================================================
    // AUTHENTICATION ROUTES
    // ========================================================================

    Route::middleware('guest')->group(function () {
        // Login Routes
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');

        // Register Routes
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');

        // Password Reset Routes
        Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    // ========================================================================
    // AUTHENTICATED CUSTOMER ROUTES
    // ========================================================================

    Route::middleware(['auth', 'verified'])->group(function () {

        // Dashboard & Profile
        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
        Route::put('/profile', [HomeController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/avatar', [HomeController::class, 'updateAvatar'])->name('profile.avatar.update');

        // Shopping Cart
        Route::prefix('/cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add', [CartController::class, 'add'])->name('add');
            Route::patch('/{item}', [CartController::class, 'update'])->name('update');
            Route::delete('/{item}', [CartController::class, 'remove'])->name('remove');
            Route::delete('/', [CartController::class, 'clear'])->name('clear');
        });

        // Checkout & Orders
        Route::prefix('/checkout')->name('checkout.')->group(function () {
            Route::get('/', [CheckoutController::class, 'index'])->name('index');
            Route::post('/', [CheckoutController::class, 'store'])->name('store');
            Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
            Route::get('/cancel/{order}', [CheckoutController::class, 'cancel'])->name('cancel');
        });

        // Orders
        Route::prefix('/orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice');
        });

        // Payments
        Route::prefix('/payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        });

        // Course Enrollment & Learning
        Route::prefix('/courses')->name('courses.')->group(function () {
            Route::post('/{course}/enroll', [CourseController::class, 'enroll'])->name('enroll');
            Route::get('/{course}/learn', [CourseController::class, 'learn'])->name('learn');
            Route::get('/{course}/lesson/{lesson}', [CourseController::class, 'lesson'])->name('lesson');
            Route::post('/{course}/lesson/{lesson}/complete', [CourseController::class, 'completeLesson'])->name('lesson.complete');
        });

        // File Downloads
        Route::prefix('/files')->name('files.')->group(function () {
            Route::get('/my-files', [FileController::class, 'myFiles'])->name('my-files');
            Route::post('/{file}/download', [FileController::class, 'download'])->name('download');
        });

        // Chatbot Conversations
        Route::prefix('/chatbot')->name('chatbot.')->group(function () {
            Route::get('/conversations', [ChatbotController::class, 'conversations'])->name('conversations');
            Route::get('/conversation/{id}', [ChatbotController::class, 'show'])->name('show');
            Route::post('/conversation', [ChatbotController::class, 'startConversation'])->name('start');
        });

        // Support Tickets
        Route::prefix('/tickets')->name('tickets.')->group(function () {
            Route::get('/', [App\Http\Controllers\Frontend\TicketController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Frontend\TicketController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Frontend\TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [App\Http\Controllers\Frontend\TicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [App\Http\Controllers\Frontend\TicketController::class, 'reply'])->name('reply');
        });
    });

    // ========================================================================
    // PAYMENT CALLBACKS - Should be handled with proper validation
    // ========================================================================

    Route::post('/webhook/stripe', [PaymentController::class, 'stripeWebhook'])->name('webhook.stripe');
    Route::post('/webhook/payment-status', [PaymentController::class, 'paymentStatusUpdate'])->name('webhook.payment.status');

    // Payment callback redirects (after payment gateway redirects back)
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');

    // ========================================================================
    // ADMIN ROUTES - Requires authentication and admin role
    // ========================================================================

    Route::middleware(['auth', 'verified', 'role:admin|super-admin'])->prefix('/admin')->name('admin.')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');

        // User Management
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');

        // Product Management
        Route::resource('products', AdminProductController::class);
        Route::post('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::get('/products/{product}/analytics', [AdminProductController::class, 'analytics'])->name('products.analytics');

        // Service Management
        Route::resource('services', AdminServiceController::class);
        Route::post('/services/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus'])->name('services.toggle-status');

        // Course Management
        Route::resource('courses', AdminCourseController::class);
        Route::post('/courses/{course}/toggle-status', [AdminCourseController::class, 'toggleStatus'])->name('courses.toggle-status');
        Route::resource('courses.lessons', AdminCourseController::class)->shallow();

        // File Management
        Route::resource('files', AdminFileController::class);
        Route::post('/files/{file}/toggle-status', [AdminFileController::class, 'toggleStatus'])->name('files.toggle-status');

        // Order Management
        Route::resource('orders', AdminOrderController::class, ['except' => ['create', 'store']]);
        Route::patch('/orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/orders/{order}/details', [AdminOrderController::class, 'details'])->name('orders.details');

        // Payment Management
        Route::resource('payments', AdminPaymentController::class, ['except' => ['create', 'store']]);
        Route::patch('/payments/{payment}/update-status', [AdminPaymentController::class, 'updateStatus'])->name('payments.update-status');
        Route::get('/payments/{payment}/details', [AdminPaymentController::class, 'details'])->name('payments.details');

        // Support Ticket Management
        Route::resource('tickets', TicketController::class, ['except' => ['create', 'store']]);
        Route::patch('/tickets/{ticket}/update-status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
        Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');

        // Reports & Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/products', [ReportController::class, 'products'])->name('products');
            Route::get('/users', [ReportController::class, 'users'])->name('users');
            Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::post('/export', [ReportController::class, 'export'])->name('export');
        });

        // System Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
            Route::get('/notifications', [SettingController::class, 'notifications'])->name('notifications');
            Route::post('/notifications', [SettingController::class, 'updateNotifications'])->name('notifications.update');
            Route::get('/payment', [SettingController::class, 'payment'])->name('payment');
            Route::post('/payment', [SettingController::class, 'updatePayment'])->name('payment.update');
        });

        // Activity Logs
        Route::get('/activity-logs', [DashboardController::class, 'activityLogs'])->name('activity-logs');

        // Webhook Management (for DHRU/GSM)
        Route::prefix('webhooks')->name('webhooks.')->group(function () {
            Route::get('/', [SettingController::class, 'webhooks'])->name('index');
            Route::post('/', [SettingController::class, 'storeWebhook'])->name('store');
            Route::delete('/{webhook}', [SettingController::class, 'deleteWebhook'])->name('delete');
        });
    });

});

// ============================================================================
// API WEBHOOK ROUTES - Should be accessible without web middleware
// ============================================================================

// External webhook endpoints for payment systems and integrations
Route::post('/api/webhook/dhru', [App\Http\Controllers\Api\WebhookController::class, 'dhru'])->name('webhook.dhru');
Route::post('/api/webhook/gsm', [App\Http\Controllers\Api\WebhookController::class, 'gsm'])->name('webhook.gsm');
Route::post('/api/webhook/stripe', [PaymentController::class, 'stripeWebhook'])->name('webhook.stripe.api');

// Fallback route for 404 errors
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
