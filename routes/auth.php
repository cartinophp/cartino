<?php

declare(strict_types=1);

use Cartino\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Social Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle OAuth authentication for traditional web applications.
| They use redirects and session-based authentication.
|
*/

Route::prefix('auth/social')->name('auth.social.')->group(function () {

    // OAuth redirect routes (public)
    Route::get('{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->name('redirect')
        ->where('provider', '[a-z]+');

    Route::get('{provider}/callback', [SocialAuthController::class, 'callback'])
        ->name('callback')
        ->where('provider', '[a-z]+');

    // Account linking routes (require authentication)
    Route::middleware(['auth'])->group(function () {
        Route::post('{provider}/link', [SocialAuthController::class, 'linkAccount'])
            ->name('link')
            ->where('provider', '[a-z]+');

        Route::delete('{provider}/unlink', [SocialAuthController::class, 'unlinkAccount'])
            ->name('unlink')
            ->where('provider', '[a-z]+');
    });
});
