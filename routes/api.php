<?php

use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================================================
// API HEALTH CHECK & MONITORING
// ============================================================================

Route::get('/health', [HealthController::class, 'health'])->name('health');
Route::get('/health/detailed', [HealthController::class, 'detailed'])->name('health.detailed');
Route::get('/health/ready', [HealthController::class, 'ready'])->name('health.ready');
Route::get('/health/live', [HealthController::class, 'live'])->name('health.live');
Route::get('/metrics', [HealthController::class, 'metrics'])->name('metrics');

// ============================================================================
// MULTILINGUAL CHAT & SOCIAL NETWORK API
// ============================================================================

// TODO: Add Chat Platform API routes here
// - Authentication routes
// - Chat & messaging routes
// - User profile routes
// - Social features routes
// - Live streaming routes
// - Payment & VIP routes
// - etc.

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
