<?php

declare(strict_types=1);

use Cartino\Http\Controllers\Api\Auth\SocialAuthApiController;
use Cartino\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle OAuth authentication for SPA/API clients.
| They return JSON responses and API tokens instead of redirecting.
|
*/

// Standard authentication routes
Route::prefix('auth')->name('api.auth.')->group(function () {
    // Public routes
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
    });
});

// Social authentication routes
Route::prefix('auth/social')->name('api.auth.social.')->group(function () {

    // Public routes (no authentication required)
    Route::get('providers', [SocialAuthApiController::class, 'providers'])
        ->name('providers');

    Route::get('{provider}/redirect', [SocialAuthApiController::class, 'getRedirectUrl'])
        ->name('redirect')
        ->where('provider', '[a-z]+');

    Route::post('{provider}/callback', [SocialAuthApiController::class, 'callback'])
        ->name('callback')
        ->where('provider', '[a-z]+');

    // Protected routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('connected', [SocialAuthApiController::class, 'connectedProviders'])
            ->name('connected');

        Route::post('{provider}/link', [SocialAuthApiController::class, 'link'])
            ->name('link')
            ->where('provider', '[a-z]+');

        Route::delete('{provider}/unlink', [SocialAuthApiController::class, 'unlink'])
            ->name('unlink')
            ->where('provider', '[a-z]+');

        Route::post('{provider}/refresh-token', [SocialAuthApiController::class, 'refreshToken'])
            ->name('refresh-token')
            ->where('provider', '[a-z]+');
    });
});
