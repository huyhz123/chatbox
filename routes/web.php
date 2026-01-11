<?php

use Illuminate\Support\Facades\Route;

// ============================================================================
// MULTILINGUAL CHAT & SOCIAL NETWORK PLATFORM - WEB ROUTES
// ============================================================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Language Switcher
Route::get('/language/{lang}', function ($lang) {
    if (in_array($lang, ['vi', 'en', 'zh', 'ja', 'ko', 'th', 'id'])) {
        session(['locale' => $lang]);
        if (auth()->check()) {
            auth()->user()->update(['preferred_language' => $lang]);
        }
    }
    return redirect()->back();
})->name('language.switch');

// TODO: Add web routes for chat platform
// - Authentication pages (login, register)
// - User profile pages
// - Chat interface
// - Social feed
// - Live streaming interface
// - Admin dashboard
// - etc.
