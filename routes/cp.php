<?php

declare(strict_types=1);

use Cartino\Http\Controllers\Api\CartController;
use Cartino\Http\Controllers\Cp\AddressController;
use Cartino\Http\Controllers\Cp\Analytics\AnalyticsController;
use Cartino\Http\Controllers\Cp\AppsController;
use Cartino\Http\Controllers\Cp\AssetBrowserController;
use Cartino\Http\Controllers\Cp\Auth\AuthenticatedSessionController;
use Cartino\Http\Controllers\Cp\Auth\NewPasswordController;
use Cartino\Http\Controllers\Cp\Auth\PasswordResetLinkController;
use Cartino\Http\Controllers\Cp\BrandsController;
use Cartino\Http\Controllers\Cp\CategoriesController;
use Cartino\Http\Controllers\Cp\ChannelController;
use Cartino\Http\Controllers\Cp\CustomersController;
use Cartino\Http\Controllers\Cp\DashboardController;
use Cartino\Http\Controllers\Cp\DiscountController;
use Cartino\Http\Controllers\Cp\EntriesController;
use Cartino\Http\Controllers\Cp\MenuController;
use Cartino\Http\Controllers\Cp\OrdersController;
use Cartino\Http\Controllers\Cp\PaymentGatewaysController;
use Cartino\Http\Controllers\Cp\ProductsController;
use Cartino\Http\Controllers\Cp\ProductTypesController;
use Cartino\Http\Controllers\Cp\ReviewsController;
use Cartino\Http\Controllers\Cp\Settings\SettingsController;
use Cartino\Http\Controllers\Cp\ShippingMethodsController;
use Cartino\Http\Controllers\Cp\SiteController;
use Cartino\Http\Controllers\Cp\TaxRatesController;
use Cartino\Http\Controllers\Cp\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Control Panel Routes
|--------------------------------------------------------------------------
|
| These routes handle the Shopper Control Panel interface, similar to
| Statamic CMS. All routes are prefixed with the CP route prefix.
|
*/

require_once __DIR__.'/api.php';
require_once __DIR__.'/auth.php';

// CP prefix from config (default: 'cp')
$cpPrefix = config('cp.route_prefix', 'cp');

Route::prefix($cpPrefix)->name('cp.')->group(function () { // ->middleware(['web', 'cartino.inertia'])

    // Authentication Routes (Guest only)
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('login');

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->name('password.store');
    });

    // Authenticated Routes (CP access required)
    Route::middleware([])->group(function () { // ['cartino.auth', 'cp', 'cartino.inertia']
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard.index');

        Route::get('/assets', [AssetBrowserController::class, 'index'])->name('assets.index');

        Route::prefix('collections/{collection}')->name('collections.')->group(function () {
            Route::get('/entries', [EntriesController::class, 'index'])->name('index');
            Route::get('/entries/create', [EntriesController::class, 'create'])->name('create');
            Route::get('/entries/{entry}', [EntriesController::class, 'show'])->name('show');
            Route::get('/entries/{entry}/edit', [EntriesController::class, 'edit'])->name('edit');
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoriesController::class, 'index'])->name('index');
            Route::get('/create', [CategoriesController::class, 'create'])->name('create');
            Route::get('/{id}', [CategoriesController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [CategoriesController::class, 'edit'])->name('edit');
        });
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        });

        Route::prefix('navigations')->name('navigations.')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('index');
            Route::get('/create', [MenuController::class, 'create'])->name('create');
            Route::post('/', [MenuController::class, 'store'])->name('store');
            Route::get('/{menu}', [MenuController::class, 'show'])->name('show');
            Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
            Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
            Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
            Route::post('/{menu}/duplicate', [MenuController::class, 'duplicate'])->name('duplicate');
        });

        Route::prefix('product-types')->name('product-types.')->group(function () {
            Route::get('/', [ProductTypesController::class, 'index'])->name('index');
            Route::get('/create', [ProductTypesController::class, 'create'])->name('create');
            Route::post('/', [ProductTypesController::class, 'store'])->name('store');
            Route::get('/{productType}', [ProductTypesController::class, 'show'])->name('show');
            Route::get('/{productType}/edit', [ProductTypesController::class, 'edit'])->name('edit');
            Route::put('/{productType}', [ProductTypesController::class, 'update'])->name('update');
            Route::delete('/{productType}', [ProductTypesController::class, 'destroy'])->name('destroy');
            Route::post('/bulk', [ProductTypesController::class, 'bulk'])->name('bulk');
            Route::post('/{productType}/duplicate', [ProductTypesController::class, 'duplicate'])->name('duplicate');
            Route::get('/export', [ProductTypesController::class, 'export'])->name('export');
        });

        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewsController::class, 'index'])->name('index');
            Route::post('/', [ReviewsController::class, 'store'])->name('store');
            Route::get('/analytics', [ReviewsController::class, 'analytics'])->name('analytics');
            Route::get('/export', [ReviewsController::class, 'export'])->name('export');
            Route::get('/{review}', [ReviewsController::class, 'show'])->name('show');
            Route::put('/{review}', [ReviewsController::class, 'update'])->name('update');
            Route::delete('/{review}', [ReviewsController::class, 'destroy'])->name('destroy');
            Route::put('/{review}/approve', [ReviewsController::class, 'approve'])->name('approve');
            Route::put('/{review}/unapprove', [ReviewsController::class, 'unapprove'])->name('unapprove');
            Route::post('/bulk-approve', [ReviewsController::class, 'bulkApprove'])->name('bulk.approve');
            Route::post('/bulk-delete', [ReviewsController::class, 'bulkDelete'])->name('bulk.delete');
        });

        // Utilities
        Route::get('/utilities', function () {
            return inertia('Utilities/Index');
        })->name('utilities.index');

        Route::get('/utilities/import', function () {
            return inertia('Utilities/Import');
        })->name('utilities.import');

        Route::get('/utilities/export', function () {
            return inertia('Utilities/Export');
        })->name('utilities.export');

        // Apps Management
        Route::prefix('apps')->name('apps.')->group(function () {
            Route::get('/', [AppsController::class, 'store'])->name('store');
            Route::get('/installed', [AppsController::class, 'installed'])->name('installed');
            Route::get('/submit', [AppsController::class, 'submit'])->name('submit');
            Route::get('/{app}/configure', [AppsController::class, 'configure'])->name('configure');
            Route::post('/install', [AppsController::class, 'install'])->name('install');
            Route::delete('/{app}/uninstall', [AppsController::class, 'uninstall'])->name('uninstall');
            Route::post('/{installation}/activate', [AppsController::class, 'activate'])->name('activate');
            Route::post('/{installation}/deactivate', [AppsController::class, 'deactivate'])->name('deactivate');
            Route::post('/bulk-activate', [AppsController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [AppsController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::delete('/bulk-uninstall', [AppsController::class, 'bulkUninstall'])->name('bulk-uninstall');
            Route::get('/{installation}/analytics', [AppsController::class, 'analytics'])->name('analytics');
            Route::post('/{app}/reviews', [AppsController::class, 'storeReview'])->name('reviews.store');
        });

        // Customers Management
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomersController::class, 'index'])->name('index');
            Route::get('/create', [CustomersController::class, 'create'])->name('create');
            Route::post('/', [CustomersController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomersController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomersController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomersController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomersController::class, 'destroy'])->name('destroy');

            // Additional customer actions
            Route::post('/bulk-action', [CustomersController::class, 'bulkAction'])->name('bulk-action');
            Route::post('/{id}/restore', [CustomersController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [CustomersController::class, 'forceDelete'])->name('force-delete');

            // Customer Addresses
            Route::prefix('{customer}/addresses')->name('addresses.')->group(function () {
                Route::get('/', [AddressController::class, 'index'])->name('index');
                Route::get('/create', [AddressController::class, 'create'])->name('create');
                Route::post('/', [AddressController::class, 'store'])->name('store');
                Route::get('/{address}', [AddressController::class, 'show'])->name('show');
                Route::get('/{address}/edit', [AddressController::class, 'edit'])->name('edit');
                Route::put('/{address}', [AddressController::class, 'update'])->name('update');
                Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
                Route::post('/{address}/set-default', [AddressController::class, 'setDefault'])->name('set-default');
            });

            // Customer Wishlists
            Route::prefix('{customer}/wishlists')->name('wishlists.')->group(function () {
                Route::get('/', [WishlistController::class, 'index'])->name('index');
                Route::post('/', [WishlistController::class, 'store'])->name('store');
                Route::get('/{wishlist}', [WishlistController::class, 'show'])->name('show');
                Route::put('/{wishlist}', [WishlistController::class, 'update'])->name('update');
                Route::delete('/{wishlist}', [WishlistController::class, 'destroy'])->name('destroy');
                Route::post('/{wishlist}/items', [WishlistController::class, 'addItem'])->name('items.store');
                Route::delete('/{wishlist}/items/{item}', [WishlistController::class, 'removeItem'])->name('items.destroy');
            });

            // // Customer Favorites
            // Route::prefix('{customer}/favorites')->name('favorites.')->group(function () {
            //     Route::get('/', [FavoriteController::class, 'index'])->name('index');
            //     Route::post('/toggle', [FavoriteController::class, 'toggle'])->name('toggle');
            //     Route::delete('/{favorite}', [FavoriteController::class, 'destroy'])->name('destroy');
            // });
        });

        // Abandoned Carts Management
        Route::prefix('abandoned-carts')->name('abandoned-carts.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::get('/{abandonedCart}', [CartController::class, 'show'])->name('show');
            Route::post('/{abandonedCart}/send-recovery-email', [CartController::class, 'sendRecoveryEmail'])->name('send-recovery-email');
            Route::post('/bulk-send-recovery-emails', [CartController::class, 'bulkSendRecoveryEmails'])->name('bulk-send-recovery-emails');
            Route::post('/{abandonedCart}/mark-recovered', [CartController::class, 'markAsRecovered'])->name('mark-recovered');
            Route::delete('/{abandonedCart}', [CartController::class, 'destroy'])->name('destroy');
            Route::delete('/bulk-delete', [CartController::class, 'bulkDelete'])->name('bulk-delete');
        });

        // // Stock Notifications Management
        // Route::prefix('stock-notifications')->name('stock-notifications.')->group(function () {
        //     Route::get('/', [StockNotificationController::class, 'index'])->name('index');
        //     Route::get('/{stockNotification}', [StockNotificationController::class, 'show'])->name('show');
        //     Route::post('/notify-for-product', [StockNotificationController::class, 'notifyForProduct'])->name('notify-for-product');
        //     Route::post('/{stockNotification}/send-notification', [StockNotificationController::class, 'sendNotification'])->name('send-notification');
        //     Route::delete('/{stockNotification}', [StockNotificationController::class, 'destroy'])->name('destroy');
        //     Route::post('/bulk-notify', [StockNotificationController::class, 'bulkNotify'])->name('bulk-notify');
        // });

        // Bulk Product Edit
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductsController::class, 'index'])->name('index');
            Route::get('/{id}', [ProductsController::class, 'show'])->name('show');
            Route::get('/{id}/variants', [ProductsController::class, 'variants'])->name('variants');
            Route::get('/{id}/inventory', [ProductsController::class, 'inventory'])->name('inventory');
        });

        // Orders Management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrdersController::class, 'index'])->name('index');
            Route::post('/', [OrdersController::class, 'store'])->name('store');
            Route::get('/{order}', [OrdersController::class, 'show'])->name('show');
            Route::put('/{order}', [OrdersController::class, 'update'])->name('update');
            Route::patch('/{order}/status', [OrdersController::class, 'updateStatus'])->name('update-status');
            Route::delete('/{order}', [OrdersController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/', [BrandsController::class, 'index'])->name('index');
            Route::post('/', [BrandsController::class, 'store'])->name('store');
            Route::get('/{brand}', [BrandsController::class, 'show'])->name('show');
            Route::put('/{brand}', [BrandsController::class, 'update'])->name('update');
            Route::delete('/{brand}', [BrandsController::class, 'destroy'])->name('destroy');
        });

        // Sites Management
        Route::prefix('sites')->name('sites.')->group(function () {
            Route::get('/', [SiteController::class, 'index'])->name('index');
            Route::get('/create', [SiteController::class, 'create'])->name('create');
            Route::get('/{site}/edit', [SiteController::class, 'edit'])->name('edit');

            // Channels nested under sites
            Route::prefix('{site}/channels')->name('channels.')->group(function () {
                Route::get('/', [ChannelController::class, 'index'])->name('index');
                Route::get('/create', [ChannelController::class, 'create'])->name('create');
                Route::get('/{channel}/edit', [ChannelController::class, 'edit'])->name('edit');
            });
        });

        // Discounts Management
        Route::prefix('discounts')->name('discounts.')->group(function () {
            Route::get('/', [DiscountController::class, 'index'])->name('index');
            Route::get('/create', [DiscountController::class, 'create'])->name('create');
            Route::post('/', [DiscountController::class, 'store'])->name('store');
            Route::get('/{discount}', [DiscountController::class, 'show'])->name('show');
            Route::get('/{discount}/edit', [DiscountController::class, 'edit'])->name('edit');
            Route::put('/{discount}', [DiscountController::class, 'update'])->name('update');
            Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');
        });

        // Settings Management
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');

            // Payment Gateways
            Route::prefix('payment-gateways')->name('payment-gateways.')->group(function () {
                Route::get('/', [PaymentGatewaysController::class, 'index'])->name('index');
                Route::post('/', [PaymentGatewaysController::class, 'store'])->name('store');
                Route::get('/{paymentGateway}', [PaymentGatewaysController::class, 'show'])->name('show');
                Route::put('/{paymentGateway}', [PaymentGatewaysController::class, 'update'])->name('update');
                Route::patch('/{paymentGateway}/toggle-status', [PaymentGatewaysController::class, 'toggleStatus'])->name('toggle-status');
                Route::patch('/{paymentGateway}/set-default', [PaymentGatewaysController::class, 'setDefault'])->name('set-default');
                Route::patch('/{paymentGateway}/config', [PaymentGatewaysController::class, 'updateConfig'])->name('update-config');
                Route::post('/sort-order', [PaymentGatewaysController::class, 'updateSortOrder'])->name('sort-order');
                Route::delete('/{paymentGateway}', [PaymentGatewaysController::class, 'destroy'])->name('destroy');
            });

            // Tax Rates
            Route::prefix('tax-rates')->name('tax-rates.')->group(function () {
                Route::get('/', [TaxRatesController::class, 'index'])->name('index');
                Route::post('/', [TaxRatesController::class, 'store'])->name('store');
                Route::get('/{taxRate}', [TaxRatesController::class, 'show'])->name('show');
                Route::put('/{taxRate}', [TaxRatesController::class, 'update'])->name('update');
                Route::patch('/{taxRate}/toggle-status', [TaxRatesController::class, 'toggleStatus'])->name('toggle-status');
                Route::post('/{taxRate}/duplicate', [TaxRatesController::class, 'duplicate'])->name('duplicate');
                Route::post('/priorities', [TaxRatesController::class, 'updatePriorities'])->name('priorities');
                Route::post('/calculate', [TaxRatesController::class, 'calculateTax'])->name('calculate');
                Route::delete('/{taxRate}', [TaxRatesController::class, 'destroy'])->name('destroy');
            });

            // Shipping Methods
            Route::prefix('shipping-methods')->name('shipping-methods.')->group(function () {
                Route::get('/', [ShippingMethodsController::class, 'index'])->name('index');
                Route::post('/', [ShippingMethodsController::class, 'store'])->name('store');
                Route::get('/{shippingMethod}', [ShippingMethodsController::class, 'show'])->name('show');
                Route::put('/{shippingMethod}', [ShippingMethodsController::class, 'update'])->name('update');
                Route::patch('/{shippingMethod}/toggle-status', [ShippingMethodsController::class, 'toggleStatus'])->name('toggle-status');
                Route::post('/{shippingMethod}/duplicate', [ShippingMethodsController::class, 'duplicate'])->name('duplicate');
                Route::post('/sort-order', [ShippingMethodsController::class, 'updateSortOrder'])->name('sort-order');
                Route::post('/calculate', [ShippingMethodsController::class, 'calculateShipping'])->name('calculate');
                Route::delete('/{shippingMethod}', [ShippingMethodsController::class, 'destroy'])->name('destroy');
            });
        });
    });
});

// // Control Panel API Routes
// Route::prefix('cp/api')->name('cp.api.')->middleware(['web', 'cartino.auth'])->group(function () {

//     // Dashboard API
//     Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

//     // Collections API
//     // Route::apiResource('collections', CollectionsController::class);

//     // Entries API
//     Route::prefix('collections/{collection}')->group(function () {
//         Route::get('/entries', [EntriesController::class, 'index'])->name('index');
//         Route::post('/entries', [EntriesController::class, 'store'])->name('store');
//         Route::get('/entries/{entry}', [EntriesController::class, 'show'])->name('show');
//         Route::put('/entries/{entry}', [EntriesController::class, 'update'])->name('update');
//         Route::delete('/entries/{entry}', [EntriesController::class, 'destroy'])->name('destroy');
//         Route::post('/entries/bulk', [EntriesController::class, 'bulk'])->name('bulk');
//     });

//     // Import/Export API
//     Route::post('/import', function () {
//         return response()->json(['message' => 'Import started']);
//     })->name('import');

//     Route::post('/export', function () {
//         return response()->json(['download_url' => '/cp/api/download/export.csv']);
//     })->name('export');

//     // Global Search API
//     Route::get('/search', function () {
//         $query = request('q');

//         return response()->json([
//             'results' => [
//                 [
//                     'type' => 'collection',
//                     'title' => 'Products',
//                     'url' => '/cp/collections/products',
//                 ],
//                 [
//                     'type' => 'entry',
//                     'title' => 'Premium Headphones',
//                     'url' => '/cp/collections/products/entries/1',
//                 ],
//             ],
//         ]);
//     })->name('search');
// });

// // CMS Blueprint System Routes (for testing)
// Route::prefix('blueprints')->name('blueprints.')->group(function () {
//     Route::get('/', [App\Http\Controllers\Admin\BlueprintTestController::class, 'index'])->name('index');
//     Route::get('/{handle}', [App\Http\Controllers\Admin\BlueprintTestController::class, 'show'])->name('show');
//     Route::get('/{handle}/form', [App\Http\Controllers\Admin\BlueprintTestController::class, 'form'])->name('form');
//     Route::post('/{handle}/submit', [App\Http\Controllers\Admin\BlueprintTestController::class, 'submit'])->name('submit');
//     Route::post('/validate', [App\Http\Controllers\Admin\BlueprintTestController::class, 'validateBlueprint'])->name('validate');
// });
