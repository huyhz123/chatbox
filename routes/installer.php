<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Installer\InstallController;

/*
|--------------------------------------------------------------------------
| Installer Routes
|--------------------------------------------------------------------------
|
| Web-based installer routes for easy setup
|
*/

Route::prefix('install')->name('installer.')->group(function () {
    Route::get('/', [InstallController::class, 'welcome'])->name('welcome');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('/environment', [InstallController::class, 'environment'])->name('environment');
    Route::post('/environment', [InstallController::class, 'environmentSave'])->name('environment.save');
    Route::get('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/install', [InstallController::class, 'install'])->name('install');
    Route::get('/complete', [InstallController::class, 'complete'])->name('complete');
});
