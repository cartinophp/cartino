<?php

declare(strict_types=1);

use Cartino\Http\Controllers\Api\AddressesController;
use Cartino\Http\Controllers\Api\Admin\FidelityAdminController;
use Cartino\Http\Controllers\Api\ApiKeysController;
use Cartino\Http\Controllers\Api\AssetableController;
use Cartino\Http\Controllers\Api\AssetContainerController;
use Cartino\Http\Controllers\Api\AssetController;
use Cartino\Http\Controllers\Api\AuthController;
use Cartino\Http\Controllers\Api\BrandsController;
use Cartino\Http\Controllers\Api\CartController;
use Cartino\Http\Controllers\Api\CatalogController;
use Cartino\Http\Controllers\Api\CategoriesController;
use Cartino\Http\Controllers\Api\ChannelController;
use Cartino\Http\Controllers\Api\CountryController;
use Cartino\Http\Controllers\Api\CouriersController;
use Cartino\Http\Controllers\Api\CurrencyController;
use Cartino\Http\Controllers\Api\CustomerGroupsController;
use Cartino\Http\Controllers\Api\CustomersController;
use Cartino\Http\Controllers\Api\Data\DataController;
use Cartino\Http\Controllers\Api\Data\DictionaryItemsController;
use Cartino\Http\Controllers\Api\Data\StatusController;
use Cartino\Http\Controllers\Api\DiscountController;
use Cartino\Http\Controllers\Api\DiscountsController;
use Cartino\Http\Controllers\Api\EntriesController;
use Cartino\Http\Controllers\Api\FidelityController;
use Cartino\Http\Controllers\Api\GlobalsController;
use Cartino\Http\Controllers\Api\MarketController;
use Cartino\Http\Controllers\Api\OrderController;
use Cartino\Http\Controllers\Api\OrdersController;
use Cartino\Http\Controllers\Api\PaymentMethodsController;
use Cartino\Http\Controllers\Api\PermissionBuilderController;
use Cartino\Http\Controllers\Api\PermissionController;
use Cartino\Http\Controllers\Api\PriceController;
use Cartino\Http\Controllers\Api\ProductController;
use Cartino\Http\Controllers\Api\ProductTypesController;
use Cartino\Http\Controllers\Api\ReportsController;
use Cartino\Http\Controllers\Api\RoleController;
use Cartino\Http\Controllers\Api\SearchController;
use Cartino\Http\Controllers\Api\ShippingMethodController;
use Cartino\Http\Controllers\Api\SitesController;
use Cartino\Http\Controllers\Api\StoreController;
use Cartino\Http\Controllers\Api\SubscriptionsController;
use Cartino\Http\Controllers\Api\SuppliersController;
use Cartino\Http\Controllers\Api\TaxRateController;
use Cartino\Http\Controllers\Api\UserController;
use Cartino\Http\Controllers\Api\UserGroupController;
use Cartino\Http\Middleware\AcceptMarketHeaders;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for Cartino with support for:
| - Public product/collection browsing
| - Authentication (Sanctum)
| - Cart management
| - Admin operations with permissions
| - Multi-site support
|
*/

Route::group([
    'prefix' => 'api',
    'middleware' => ['api', 'force.json', AcceptMarketHeaders::class],
], function () {

    /*
    |--------------------------------------------------------------------------
    | Multi-Market & Store Context (Public)
    |--------------------------------------------------------------------------
    */

    // Store Configuration & Context
    Route::prefix('store')->name('store.')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::post('/', [StoreController::class, 'store'])->name('store');
        Route::post('/reset', [StoreController::class, 'reset'])->name('reset');
    });

    // Markets API
    Route::prefix('markets')->name('markets.')->group(function () {
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::get('/current', [MarketController::class, 'current'])->name('current');
        Route::get('/{market}', [MarketController::class, 'show'])->name('show');
        Route::post('/set-context', [MarketController::class, 'setContext'])->name('set-context');
        Route::get('/context', [MarketController::class, 'getContext'])->name('context');
        Route::post('/switch', [MarketController::class, 'switch'])->name('switch');
        Route::get('/{market}/configuration', [MarketController::class, 'configuration'])->name('configuration');
        Route::get('/{market}/payment-methods', [MarketController::class, 'paymentMethods'])->name('payment-methods');
        Route::get('/{market}/shipping-methods', [MarketController::class, 'shippingMethods'])->name('shipping-methods');
        Route::post('/{market}/calculate-tax', [MarketController::class, 'calculateTax'])->name('calculate-tax');
    });

    // Prices API
    Route::prefix('prices')->name('prices.')->group(function () {
        Route::get('/show', [PriceController::class, 'show'])->name('show');
        Route::post('/bulk', [PriceController::class, 'bulk'])->name('bulk');
        Route::get('/tiers', [PriceController::class, 'tiers'])->name('tiers');
        Route::post('/calculate', [PriceController::class, 'calculate'])->name('calculate');
    });

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    // Brands resource with additional custom methods
    Route::apiResource('brands', BrandsController::class, [
        'names' => 'api.brands',
    ]);
    Route::apiResource('orders', OrdersController::class, [
        'names' => 'api.orders',
    ]);
    Route::apiResource('addresses', AddressesController::class, [
        'names' => 'api.addresses',
    ]);
    Route::apiResource('channels', ChannelController::class, [
        'names' => 'api.channels',
    ]);
    Route::apiResource('products', ProductController::class, [
        'names' => 'api.products',
    ]);
    Route::apiResource('product-types', ProductTypesController::class, [
        'names' => 'api.product-types',
    ]);
    Route::apiResource('sites', SitesController::class, [
        'names' => 'api.sites',
    ]);

    // Catalogs custom routes (before resource to avoid conflicts)
    Route::get('catalogs/active', [CatalogController::class, 'active'])->name('api.catalogs.active');
    Route::get('catalogs/published', [CatalogController::class, 'published'])->name('api.catalogs.published');

    Route::apiResource('catalogs', CatalogController::class, [
        'names' => 'api.catalogs',
    ]);

    // Categories custom routes (before resource to avoid conflicts)
    Route::get('categories/tree', [CategoriesController::class, 'tree'])->name('api.categories.tree');
    Route::get('categories/root', [CategoriesController::class, 'root'])->name('api.categories.root');

    Route::apiResource('categories', CategoriesController::class, [
        'names' => 'api.categories',
    ]);
    Route::apiResource('payment-methods', PaymentMethodsController::class, [
        'names' => 'api.payment-methods',
    ]);
    Route::apiResource('customers', CustomersController::class, [
        'names' => 'api.customers',
    ]);
    Route::apiResource('customer-groups', CustomerGroupsController::class, [
        'names' => 'api.customer-groups',
    ]);
    Route::apiResource('suppliers', SuppliersController::class, [
        'names' => 'api.suppliers',
    ]);
    Route::apiResource('discounts', DiscountsController::class, [
        'names' => 'api.discounts',
    ]);

    // Couriers resource (public read-only access)
    Route::get('couriers', [CouriersController::class, 'index'])->name('api.couriers.index');
    Route::get('couriers/{courier}', [CouriersController::class, 'show'])->name('api.couriers.show');
    Route::get('couriers-enabled', [CouriersController::class, 'enabled'])->name('api.couriers.enabled.public');

    // Subscriptions resource (public read access)
    Route::get('subscriptions', [SubscriptionsController::class, 'index'])->name('api.subscriptions.index');
    Route::get('subscriptions/{subscription}', [SubscriptionsController::class, 'show'])->name('api.subscriptions.show');

    // Authentication
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');

    // Data Endpoints (Statuses, Dictionaries, etc.)
    Route::prefix('data')->name('api.data.')->group(function () {
        Route::get('/', [DataController::class, 'index'])->name('index');

        // Statuses
        Route::get('/statuses', [StatusController::class, 'index'])->name('statuses.index');
        Route::get('/statuses/{type}', [StatusController::class, 'show'])->name('statuses.show');

        // Dictionaries (extensible reference data) - Public read access
        Route::get('/dictionaries', [DataController::class, 'dictionaries'])->name('dictionaries.index');
        Route::get('/dictionaries/search', [DataController::class, 'searchDictionaries'])->name('dictionaries.search');
        Route::get('/dictionaries/{handle}', [DataController::class, 'dictionary'])->name('dictionaries.show');
        Route::get('/dictionaries/{handle}/{key}', [DataController::class, 'dictionaryItem'])->name('dictionaries.item');

        // Dictionary Items Management (Admin only) - CRUD for custom items
        Route::prefix('dictionaries/{handle}/items')->name('dictionary-items.')->middleware(['auth.flexible'])->group(function () {
            Route::get('/', [DictionaryItemsController::class, 'index'])->name('index');
            Route::post('/', [DictionaryItemsController::class, 'store'])->name('store');
            Route::put('/{id}', [DictionaryItemsController::class, 'update'])->name('update');
            Route::delete('/{id}', [DictionaryItemsController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [DictionaryItemsController::class, 'reorder'])->name('reorder');
            Route::post('/{id}/toggle', [DictionaryItemsController::class, 'toggle'])->name('toggle');
        });
    });

    // Public Countries/Currencies
    Route::prefix('countries')->name('api.countries.')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('index');
        Route::get('/{country}', [CountryController::class, 'show'])->name('show');
        Route::get('/{country}/states', [CountryController::class, 'states'])->name('states');
        Route::get('/{country}/cities', [CountryController::class, 'cities'])->name('cities');
    });

    Route::prefix('currencies')->name('api.currencies.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::get('/{currency}', [CurrencyController::class, 'show'])->name('show');
        Route::post('/convert', [CurrencyController::class, 'convert'])->name('convert');
    });

    // Public Shipping Methods
    Route::prefix('shipping-methods')->name('api.shipping-methods.')->group(function () {
        Route::get('/', [ShippingMethodController::class, 'index'])->name('index');
        Route::post('/calculate', [ShippingMethodController::class, 'calculate'])->name('calculate');
    });

    // Headless Search API - Optimized dynamic search with breadcrumbs and filters
    Route::post('/search', [SearchController::class, 'search'])->name('api.search');

    // Public Tax Rates
    Route::prefix('tax-rates')->name('api.tax-rates.')->group(function () {
        Route::get('/', [TaxRateController::class, 'index'])->name('index');
        Route::post('/calculate', [TaxRateController::class, 'calculate'])->name('calculate');
    });

    // Fidelity System Configuration (Public)
    Route::get('/fidelity/configuration', [FidelityController::class, 'configuration'])->name('api.fidelity.configuration');
    Route::post('/fidelity/calculate-points', [FidelityController::class, 'calculatePoints'])->name('api.fidelity.calculate-points');
    Route::post('/fidelity/find-card', [FidelityController::class, 'findByCardNumber'])->name('api.fidelity.find-card');

    // Authentication
    Route::prefix('auth')->name('api.auth.')->group(function () {
        Route::middleware(['auth.flexible'])->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    // Customer Authentication and Management
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::post('/register', [CustomersController::class, 'register'])->name('register');
        Route::post('/login', [CustomersController::class, 'login'])->name('login');
        Route::post('/logout', [CustomersController::class, 'logout'])->name('logout')->middleware('auth:customer');
        Route::post('/forgot-password', [CustomersController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password', [CustomersController::class, 'resetPassword'])->name('reset-password');
        Route::get('/verify/{token}', [CustomersController::class, 'verify'])->name('verify');

        // Authenticated Customer Routes
        Route::middleware(['auth:customer'])->group(function () {
            Route::get('/profile', [CustomersController::class, 'profile'])->name('profile');
            Route::put('/profile', [CustomersController::class, 'updateProfile'])->name('update-profile');
            Route::get('/addresses', [CustomersController::class, 'addresses'])->name('addresses');
            Route::post('/addresses', [CustomersController::class, 'storeAddress'])->name('store-address');
            Route::put('/addresses/{address}', [CustomersController::class, 'updateAddress'])->name('update-address');
            Route::delete('/addresses/{address}', [CustomersController::class, 'destroyAddress'])->name('destroy-address');
            Route::get('/orders', [CustomersController::class, 'customerOrders'])->name('orders');
            Route::get('/orders/{order}', [CustomersController::class, 'customerOrder'])->name('order');
        });
    });

    // Order Management for Customers
    Route::prefix('orders')->name('orders.')->middleware(['auth:customer'])->group(function () {
        Route::get('/', [OrderController::class, 'customerIndex'])->name('customer.index');
        Route::post('/', [OrderController::class, 'customerStore'])->name('customer.store');
        Route::get('/{order}', [OrderController::class, 'customerShow'])->name('customer.show');
        Route::post('/{order}/cancel', [OrderController::class, 'customerCancel'])->name('customer.cancel');
        Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('customer.invoice');
        Route::get('/{order}/track', [OrderController::class, 'track'])->name('customer.track');
    });

    // Enhanced Cart Management
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'show'])->name('show');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/{item}', [CartController::class, 'update'])->name('update');
        Route::delete('/{item}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
        Route::post('/apply-discount', [CartController::class, 'applyDiscount'])->name('apply-discount');
        Route::delete('/remove-discount', [CartController::class, 'removeDiscount'])->name('remove-discount');
        Route::get('/totals', [CartController::class, 'totals'])->name('totals');
        Route::post('/estimate-shipping', [CartController::class, 'estimateShipping'])->name('estimate-shipping');
    });

    // Enhanced Fidelity System
    Route::prefix('fidelity')->name('fidelity.')->group(function () {
        // Public endpoints
        Route::get('/configuration', [FidelityController::class, 'configuration'])->name('configuration');
        Route::post('/calculate-points', [FidelityController::class, 'calculatePoints'])->name('calculate-points');
        Route::post('/find-card', [FidelityController::class, 'findByCardNumber'])->name('find-card');

        // Customer authenticated endpoints
        Route::middleware(['auth:customer'])->group(function () {
            Route::get('/card', [FidelityController::class, 'card'])->name('card');
            Route::get('/transactions', [FidelityController::class, 'transactions'])->name('transactions');
            Route::get('/balance', [FidelityController::class, 'balance'])->name('balance');
            Route::post('/redeem', [FidelityController::class, 'redeem'])->name('redeem');
            Route::get('/offers', [FidelityController::class, 'offers'])->name('offers');
        });
    });

    // Discount/Coupon Validation
    Route::prefix('discounts')->name('discounts.')->group(function () {
        Route::post('/validate', [DiscountController::class, 'validate'])->name('validate');
        Route::get('/public', [DiscountController::class, 'public'])->name('public');
    });

    /*
        |--------------------------------------------------------------------------
        | Admin Routes (Permissions Required)
        |--------------------------------------------------------------------------
        */

    // Admin routes group - requires authentication via Sanctum OR API Key
    Route::middleware(['auth.flexible'])->group(function () {

        // Permission Management
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/roles/{role}/permissions', [PermissionController::class, 'rolePermissions'])->name('role.permissions');
            Route::put('/roles/{role}/permissions', [PermissionController::class, 'updateRolePermissions'])->name('role.update');
            Route::post('/generate', [PermissionController::class, 'generatePermissions'])->name('generate');
            Route::post('/super-role', [PermissionController::class, 'createSuperRole'])->name('super.create');
            Route::get('/tree', [PermissionController::class, 'permissionTree'])->name('tree');
        });

        // Custom role actions
        Route::post('roles/{role}/assign-users', [RoleController::class, 'assignUsers'])->name('api.roles.assign.users');
        Route::post('roles/{role}/remove-users', [RoleController::class, 'removeUsers'])->name('api.roles.remove.users');
        Route::post('roles/{role}/clone', [RoleController::class, 'clone'])->name('api.roles.clone');
        Route::get('roles/statistics', [RoleController::class, 'statistics'])->name('api.roles.statistics');

        // Customer Management (Standardized with apiResource)
        Route::apiResource('customers', CustomersController::class, [
            'names' => 'api.customers',
        ]);

        // Custom customer actions
        Route::get('customers/with-fidelity', [CustomersController::class, 'indexWithFidelity'])->name('api.customers.index-with-fidelity');
        Route::get('customers/{customer}/fidelity', [CustomersController::class, 'fidelityCard'])->name('api.customers.fidelity');
        Route::post('customers/{customer}/fidelity', [CustomersController::class, 'createFidelityCard'])->name('api.customers.fidelity.create');
        Route::get('customers/{customer}/orders', [CustomersController::class, 'orders'])->name('api.customers.orders');
        Route::get('customers/{customer}/addresses', [CustomersController::class, 'addresses'])->name('api.customers.addresses');
        Route::post('customers/{customer}/addresses', [CustomersController::class, 'addAddress'])->name('api.customers.addresses.add');
        Route::get('customers/{customer}/statistics', [CustomersController::class, 'statistics'])->name('api.customers.statistics');
        Route::post('customers/bulk', [CustomersController::class, 'bulk'])->name('api.customers.bulk');

        // Fidelity System Management
        Route::prefix('fidelity')->name('fidelity.')->group(function () {
            Route::post('/redeem-points', [FidelityController::class, 'redeemPoints'])->name('redeem-points');
            Route::get('/cards', [FidelityAdminController::class, 'index'])->name('cards.index');
            Route::get('/cards/{card}', [FidelityAdminController::class, 'show'])->name('cards.show');
            Route::put('/cards/{card}', [FidelityAdminController::class, 'update'])->name('cards.update');
            Route::post('/cards/{card}/add-points', [FidelityAdminController::class, 'addPoints'])->name('cards.add-points');
            Route::get('/statistics', [FidelityAdminController::class, 'statistics'])->name('statistics');
            Route::post('/expire-points', [FidelityAdminController::class, 'expirePoints'])->name('expire-points');
        });

        // User Group Management (Standardized with apiResource)
        Route::apiResource('user-groups', UserGroupController::class, [
            'names' => 'api.user-groups',
        ]);

        // Custom user-group actions
        Route::post('user-groups/{group}/assign-users', [UserGroupController::class, 'assignUsers'])->name('api.user-groups.assign.users');
        Route::post('user-groups/{group}/remove-users', [UserGroupController::class, 'removeUsers'])->name('api.user-groups.remove.users');
        Route::get('user-groups/{group}/permissions', [UserGroupController::class, 'permissions'])->name('api.user-groups.permissions');

        // User Management (Standardized with apiResource)
        Route::apiResource('users', UserController::class, [
            'names' => 'api.users',
        ]);

        // Custom user actions
        Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('api.users.activate');
        Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('api.users.deactivate');
        Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('api.users.assign.roles');
        Route::post('users/{user}/assign-permissions', [UserController::class, 'assignPermissions'])->name('api.users.assign.permissions');
        Route::get('users/{user}/activity', [UserController::class, 'activity'])->name('api.users.activity');
        Route::post('users/bulk', [UserController::class, 'bulk'])->name('api.users.bulk');

        // Brand Management (Admin) resource
        Route::apiResource('brands', BrandsController::class, [
            'names' => 'api.brands',
        ]);

        // Additional admin brand operations
        Route::post('brands/{brand}/toggle-status', [BrandsController::class, 'toggleStatus'])->name('api.brands.toggleStatus');
        Route::get('brands/{brand}/products', [BrandsController::class, 'products'])->name('api.brands.products');

        // Courier Management (Admin)
        // Specific routes first to avoid conflicts with {courier} parameter
        Route::get('couriers/enabled', [CouriersController::class, 'enabled'])->name('api.couriers.enabled');

        Route::apiResource('couriers', CouriersController::class, [
            'names' => 'api.couriers',
        ]);

        // Additional admin courier operations
        Route::post('couriers/{courier}/toggle-status', [CouriersController::class, 'toggleStatus'])->name('api.couriers.toggleStatus');
        Route::post('couriers/{courier}/toggle-enabled', [CouriersController::class, 'toggleEnabled'])->name('api.couriers.toggleEnabled');
        Route::get('couriers/{courier}/orders', [CouriersController::class, 'orders'])->name('api.couriers.orders');

        // Subscription Management (Admin)
        Route::apiResource('subscriptions', SubscriptionsController::class, [
            'names' => 'api.subscriptions',
        ]);

        // Additional admin subscription operations
        Route::post('subscriptions/{subscription}/pause', [SubscriptionsController::class, 'pause'])->name('api.subscriptions.pause');
        Route::post('subscriptions/{subscription}/resume', [SubscriptionsController::class, 'resume'])->name('api.subscriptions.resume');
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionsController::class, 'cancel'])->name('api.subscriptions.cancel');
        Route::get('subscriptions/{subscription}/orders', [SubscriptionsController::class, 'orders'])->name('api.subscriptions.orders');
        Route::get('subscriptions/active', [SubscriptionsController::class, 'active'])->name('api.subscriptions.active');
        Route::get('subscriptions/due-for-billing', [SubscriptionsController::class, 'dueForBilling'])->name('api.subscriptions.due-for-billing');

        // Order Management (Admin)
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'adminShow'])->name('show');
            Route::put('/{order}', [OrderController::class, 'update'])->name('update');
            Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
            Route::post('/{order}/fulfill', [OrderController::class, 'fulfill'])->name('fulfill');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('refund');
            Route::post('/{order}/archive', [OrderController::class, 'archive'])->name('archive');
            Route::get('/{order}/timeline', [OrderController::class, 'timeline'])->name('timeline');
            Route::get('/statistics', [OrderController::class, 'statistics'])->name('statistics');
            Route::post('/bulk', [OrderController::class, 'bulk'])->name('bulk');
        });
        // Country Management
        Route::prefix('countries')->name('countries.')->group(function () {
            Route::get('/', [CountryController::class, 'index'])->name('index');
            Route::post('/', [CountryController::class, 'store'])->name('store');
            Route::get('/{country}', [CountryController::class, 'adminShow'])->name('show');
            Route::put('/{country}', [CountryController::class, 'update'])->name('update');
            Route::delete('/{country}', [CountryController::class, 'destroy'])->name('destroy');
            Route::post('/{country}/toggle-status', [CountryController::class, 'toggleStatus'])->name('toggle.status');
            Route::post('/bulk', [CountryController::class, 'bulk'])->name('bulk');
        });

        // Currency Management
        Route::prefix('currencies')->name('currencies.')->group(function () {
            Route::get('/', [CurrencyController::class, 'index'])->name('index');
            Route::post('/', [CurrencyController::class, 'store'])->name('store');
            Route::get('/{currency}', [CurrencyController::class, 'adminShow'])->name('show');
            Route::put('/{currency}', [CurrencyController::class, 'update'])->name('update');
            Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('destroy');
            Route::post('/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('set.default');
            Route::post('/bulk', [CurrencyController::class, 'bulk'])->name('bulk');
        });

        // Shipping Method Management
        Route::prefix('shipping-methods')->name('shipping-methods.')->group(function () {
            Route::get('/', [ShippingMethodController::class, 'index'])->name('index');
            Route::post('/', [ShippingMethodController::class, 'store'])->name('store');
            Route::get('/{method}', [ShippingMethodController::class, 'adminShow'])->name('show');
            Route::put('/{method}', [ShippingMethodController::class, 'update'])->name('update');
            Route::delete('/{method}', [ShippingMethodController::class, 'destroy'])->name('destroy');
            Route::post('/{method}/toggle-status', [ShippingMethodController::class, 'toggleStatus'])->name('toggle.status');
            Route::post('/bulk', [ShippingMethodController::class, 'bulk'])->name('bulk');
        });

        // Tax Rate Management
        Route::prefix('tax-rates')->name('tax-rates.')->group(function () {
            Route::get('/', [TaxRateController::class, 'index'])->name('index');
            Route::post('/', [TaxRateController::class, 'store'])->name('store');
            Route::get('/{rate}', [TaxRateController::class, 'adminShow'])->name('show');
            Route::put('/{rate}', [TaxRateController::class, 'update'])->name('update');
            Route::delete('/{rate}', [TaxRateController::class, 'destroy'])->name('destroy');
            Route::post('/{rate}/toggle-status', [TaxRateController::class, 'toggleStatus'])->name('toggle.status');
            Route::post('/bulk', [TaxRateController::class, 'bulk'])->name('bulk');
        });

        // Discount Management (Admin)
        Route::prefix('discounts')->name('discounts.')->group(function () {
            Route::get('/', [DiscountController::class, 'index'])->name('index');
            Route::post('/', [DiscountController::class, 'store'])->name('store');
            Route::get('/{discount}', [DiscountController::class, 'adminShow'])->name('show');
            Route::put('/{discount}', [DiscountController::class, 'update'])->name('update');
            Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');
            Route::post('/{discount}/toggle', [DiscountController::class, 'toggle'])->name('toggle');
            Route::post('/{discount}/duplicate', [DiscountController::class, 'duplicate'])->name('duplicate');
            Route::post('/validate-code', [DiscountController::class, 'validateCode'])->name('validate-code');
            Route::get('/statistics', [DiscountController::class, 'statistics'])->name('statistics');
            Route::post('/bulk', [DiscountController::class, 'bulk'])->name('bulk');
        });

        // Permission Builder
        Route::prefix('permission-builder')->name('builder.')->group(function () {
            Route::get('/', [PermissionBuilderController::class, 'builder'])->name('index');
            Route::put('/matrix', [PermissionBuilderController::class, 'updateMatrix'])->name('matrix.update');
            Route::post('/apply-template', [PermissionBuilderController::class, 'applyTemplate'])->name('template.apply');
            Route::post('/generate-resource', [PermissionBuilderController::class, 'generateResourcePermissions'])->name('resource.generate');
            Route::get('/export', [PermissionBuilderController::class, 'export'])->name('export');
            Route::post('/import', [PermissionBuilderController::class, 'import'])->name('import');
        });

        // Asset Container Management
        Route::prefix('asset-containers')->name('asset-containers.')->group(function () {
            Route::get('/', [AssetContainerController::class, 'index'])->name('index');
            Route::post('/', [AssetContainerController::class, 'store'])->name('store');
            Route::get('/{container}', [AssetContainerController::class, 'show'])->name('show');
            Route::put('/{container}', [AssetContainerController::class, 'update'])->name('update');
            Route::delete('/{container}', [AssetContainerController::class, 'destroy'])->name('destroy');
        });

        // Asset Management
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [AssetController::class, 'index'])->name('index');
            Route::post('/upload', [AssetController::class, 'upload'])->name('upload');
            Route::post('/upload-multiple', [AssetController::class, 'uploadMultiple'])->name('upload.multiple');
            Route::get('/{asset}', [AssetController::class, 'show'])->name('show');
            Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
            Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');
            Route::post('/{asset}/move', [AssetController::class, 'move'])->name('move');
            Route::post('/{asset}/rename', [AssetController::class, 'rename'])->name('rename');
            Route::get('/{asset}/download', [AssetController::class, 'download'])->name('download');
            Route::post('/bulk-delete', [AssetController::class, 'bulkDelete'])->name('bulk.delete');
        });

        // Asset Relations (Polymorphic - Products, Categories, Brands, etc.)
        // Format: /api/{model_type}/{id}/assets
        Route::prefix('{model_type}/{model_id}/assets')->name('assetables.')->group(function () {
            Route::get('/', [AssetableController::class, 'index'])->name('index');
            Route::post('/', [AssetableController::class, 'attach'])->name('attach');
            Route::post('/bulk', [AssetableController::class, 'attachBulk'])->name('attach.bulk');
            Route::put('/sync', [AssetableController::class, 'sync'])->name('sync');
            Route::post('/reorder', [AssetableController::class, 'reorder'])->name('reorder');
            Route::patch('/{asset}', [AssetableController::class, 'update'])->name('update');
            Route::delete('/{asset}', [AssetableController::class, 'detach'])->name('detach');
            Route::delete('/', [AssetableController::class, 'detachAll'])->name('detach.all');
            Route::post('/{asset}/set-primary', [AssetableController::class, 'setPrimary'])->name('set-primary');
        });

        // Globals Management (like Statamic)
        Route::prefix('globals')->name('globals.')->group(function () {
            Route::get('/', [GlobalsController::class, 'index'])->name('index');
            Route::post('/', [GlobalsController::class, 'store'])->name('store');
            Route::get('/handle/{handle}', [GlobalsController::class, 'byHandle'])->name('by-handle');
            Route::put('/handle/{handle}', [GlobalsController::class, 'updateByHandle'])->name('update-by-handle');
            Route::post('/handle/{handle}/set-value', [GlobalsController::class, 'setValue'])->name('set-value');
            Route::get('/handle/{handle}/get-value/{key}', [GlobalsController::class, 'getValue'])->name('get-value');
            Route::get('/{global}', [GlobalsController::class, 'show'])->name('show');
            Route::put('/{global}', [GlobalsController::class, 'update'])->name('update');
            Route::delete('/{global}', [GlobalsController::class, 'destroy'])->name('destroy');
        });

        // Entries Management (like Statamic Collections)
        Route::prefix('entries')->name('entries.')->group(function () {
            Route::get('/', [EntriesController::class, 'index'])->name('index');
            Route::post('/', [EntriesController::class, 'store'])->name('store');
            Route::post('/reorder', [EntriesController::class, 'reorder'])->name('reorder');
            Route::get('/collection/{collection}', [EntriesController::class, 'byCollection'])->name('by-collection');
            Route::get('/collection/{collection}/tree', [EntriesController::class, 'tree'])->name('tree');
            Route::get('/collection/{collection}/{slug}', [EntriesController::class, 'bySlug'])->name('by-slug');
            Route::get('/{entry}', [EntriesController::class, 'show'])->name('show');
            Route::put('/{entry}', [EntriesController::class, 'update'])->name('update');
            Route::delete('/{entry}', [EntriesController::class, 'destroy'])->name('destroy');
            Route::post('/{entry}/publish', [EntriesController::class, 'publish'])->name('publish');
            Route::post('/{entry}/unpublish', [EntriesController::class, 'unpublish'])->name('unpublish');
            Route::post('/{entry}/schedule', [EntriesController::class, 'schedule'])->name('schedule');
            Route::post('/{entry}/duplicate', [EntriesController::class, 'duplicate'])->name('duplicate');
        });

        // API Keys Management
        Route::prefix('api-keys')->name('api-keys.')->group(function () {
            Route::get('/', [ApiKeysController::class, 'index'])->name('index');
            Route::post('/', [ApiKeysController::class, 'store'])->name('store');
            Route::get('/{apiKey}', [ApiKeysController::class, 'show'])->name('show');
            Route::put('/{apiKey}', [ApiKeysController::class, 'update'])->name('update');
            Route::delete('/{apiKey}', [ApiKeysController::class, 'destroy'])->name('destroy');
            Route::post('/{apiKey}/revoke', [ApiKeysController::class, 'revoke'])->name('revoke');
            Route::post('/{apiKey}/activate', [ApiKeysController::class, 'activate'])->name('activate');
            Route::post('/{apiKey}/regenerate', [ApiKeysController::class, 'regenerate'])->name('regenerate');
        });

        // Reports & Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/dashboard', [ReportsController::class, 'dashboard'])->name('dashboard');
            Route::get('/sales', [ReportsController::class, 'sales'])->name('sales');
            Route::get('/customers', [ReportsController::class, 'customers'])->name('customers');
            Route::get('/products', [ReportsController::class, 'products'])->name('products');
            Route::get('/revenue', [ReportsController::class, 'revenue'])->name('revenue');
            Route::get('/inventory', [ReportsController::class, 'inventory'])->name('inventory');
            Route::get('/orders-by-status', [ReportsController::class, 'ordersByStatus'])->name('orders-by-status');
            Route::get('/export', [ReportsController::class, 'export'])->name('export');
        });

    }); // End admin routes group (auth.flexible)
});
