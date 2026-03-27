<?php

declare(strict_types=1);

use Cartino\Http\Controllers\StorefrontController;
use Cartino\Http\Middleware\StorefrontTemplateMiddleware;
use Illuminate\Support\Facades\Route;

// Storefront Routes with Template Support
Route::middleware([
    'web',
    StorefrontTemplateMiddleware::class,
])->group(function () {

    // Home page
    Route::get('/', [StorefrontController::class, 'home'])
        ->name('storefront.home');

    // Product routes with handle support
    Route::prefix('products')->name('storefront.products.')->group(function () {
        Route::get('/', [StorefrontController::class, 'productIndex'])
            ->name('index');

        Route::get('/{handle}', [StorefrontController::class, 'productShow'])
            ->name('show')
            ->where('handle', '[a-zA-Z0-9\-_]+');
    });

    // Category routes
    Route::prefix('collections')->name('storefront.collections.')->group(function () {
        Route::get('/', [StorefrontController::class, 'collectionIndex'])
            ->name('index');

        Route::get('/{handle}', [StorefrontController::class, 'collectionShow'])
            ->name('show')
            ->where('handle', '[a-zA-Z0-9\-_]+');
    });

    // Pages
    Route::prefix('pages')->name('storefront.pages.')->group(function () {
        Route::get('/{handle}', [StorefrontController::class, 'pageShow'])
            ->name('show')
            ->where('handle', '[a-zA-Z0-9\-_]+');
    });

    // Blog
    Route::prefix('blog')->name('storefront.blog.')->group(function () {
        Route::get('/', [StorefrontController::class, 'blogIndex'])
            ->name('index');

        Route::get('/{handle}', [StorefrontController::class, 'blogShow'])
            ->name('show')
            ->where('handle', '[a-zA-Z0-9\-_]+');
    });

    // Search
    Route::get('/search', [StorefrontController::class, 'search'])
        ->name('storefront.search');

    // Cart
    Route::prefix('cart')->name('storefront.cart.')->group(function () {
        Route::get('/', [StorefrontController::class, 'cartShow'])
            ->name('show');

        Route::post('/add', [StorefrontController::class, 'cartAdd'])
            ->name('add');

        Route::put('/update/{line}', [StorefrontController::class, 'cartUpdate'])
            ->name('update');

        Route::delete('/remove/{line}', [StorefrontController::class, 'cartRemove'])
            ->name('remove');

        Route::post('/apply-coupon', [StorefrontController::class, 'cartApplyCoupon'])
            ->middleware('throttle:5,1')
            ->name('apply-coupon');
    });

    // Checkout
    Route::prefix('checkout')->middleware('throttle:10,1')->name('storefront.checkout.')->group(function () {
        Route::get('/', [StorefrontController::class, 'checkoutShow'])
            ->name('show');

        Route::post('/process', [StorefrontController::class, 'checkoutProcess'])
            ->name('process');
    });

    // Customer Authentication
    Route::get('/login', [StorefrontController::class, 'loginShow'])
        ->name('login')
        ->middleware('guest:customers');

    Route::post('/login', [StorefrontController::class, 'loginProcess'])
        ->middleware(['guest:customers', 'throttle:5,1']);

    Route::get('/register', [StorefrontController::class, 'registerShow'])
        ->name('register')
        ->middleware('guest:customers');

    Route::post('/register', [StorefrontController::class, 'registerProcess'])
        ->middleware('guest:customers');

    Route::post('/logout', [StorefrontController::class, 'logout'])
        ->name('logout')
        ->middleware('auth:customers');
});

// Customer Account
Route::prefix('account')->middleware('auth:customers')->name('storefront.account.')->group(function () {
    Route::get('/', [StorefrontController::class, 'accountDashboard'])
        ->name('dashboard');

    Route::get('/orders', [StorefrontController::class, 'accountOrders'])
        ->name('orders');

    Route::get('/orders/{order}', [StorefrontController::class, 'accountOrderShow'])
        ->name('orders.show');

    Route::get('/orders/{order}/track', [StorefrontController::class, 'accountOrderTrack'])
        ->name('orders.track');

    Route::get('/orders/{order}/invoice', [StorefrontController::class, 'accountOrderInvoice'])
        ->name('orders.invoice');

    Route::get('/addresses', [StorefrontController::class, 'accountAddresses'])
        ->name('addresses');

    Route::post('/addresses', [StorefrontController::class, 'accountAddressStore'])
        ->name('addresses.store');

    Route::put('/addresses/{address}', [StorefrontController::class, 'accountAddressUpdate'])
        ->name('addresses.update');

    Route::delete('/addresses/{address}', [StorefrontController::class, 'accountAddressDestroy'])
        ->name('addresses.destroy');

    Route::get('/settings', [StorefrontController::class, 'accountSettings'])
        ->name('settings');

    Route::put('/settings', [StorefrontController::class, 'accountSettingsUpdate'])
        ->name('settings.update');
});

Route::post('/newsletter/subscribe', [StorefrontController::class, 'newsletterSubscribe'])
    ->middleware('throttle:3,1')
    ->name('newsletter.subscribe');

// Template Preview Routes (Admin only)
Route::prefix('admin/template-preview')->middleware('auth')->name('admin.template.preview.')->group(function () {
    Route::get('/{template}', [StorefrontController::class, 'templatePreview'])
        ->name('show');
});
