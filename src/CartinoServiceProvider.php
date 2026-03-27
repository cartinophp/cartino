<?php

namespace Cartino;

use Cartino\Console\Commands\BuildAssetsCommand;
use Cartino\Console\Commands\ExpireFidelityPoints;
use Cartino\Console\Commands\GenerateApiKey;
use Cartino\Console\Commands\InstallShopperCommand;
use Cartino\Console\Commands\OptimizeCommand;
use Cartino\Console\Commands\ShowAdminUsersCommand;
use Cartino\Console\CreateAdminUserCommand;
use Cartino\Contracts\ProductRepositoryInterface;
use Cartino\Contracts\SupplierRepositoryInterface;
use Cartino\Exceptions\ApiHandler;
use Cartino\Http\Middleware\Authenticate;
use Cartino\Http\Middleware\AuthenticateSanctumOrApiKey;
use Cartino\Http\Middleware\ControlPanelMiddleware;
use Cartino\Http\Middleware\ForceJsonResponse;
use Cartino\Http\Middleware\HandleInertiaRequests;
use Cartino\Http\Middleware\MonitorQueryPerformance;
use Cartino\Http\Middleware\ValidateApiKey;
use Cartino\Http\View\Composers\TranslationComposer;
use Cartino\Models\Address;
use Cartino\Models\AnalyticsEvent;
use Cartino\Models\ApiKey;
use Cartino\Models\App;
use Cartino\Models\AppApiToken;
use Cartino\Models\AppInstallation;
use Cartino\Models\AppReview;
use Cartino\Models\AppWebhook;
use Cartino\Models\Asset;
use Cartino\Models\AssetContainer;
use Cartino\Models\AssetFolder;
use Cartino\Models\AssetTransformation;
use Cartino\Models\Brand;
use Cartino\Models\Cart;
use Cartino\Models\CartLine;
use Cartino\Models\Catalog;
use Cartino\Models\Category;
use Cartino\Models\Channel;
use Cartino\Models\Country;
use Cartino\Models\Currency;
use Cartino\Models\Customer;
use Cartino\Models\CustomerAddress;
use Cartino\Models\CustomerGroup;
use Cartino\Models\Discount;
use Cartino\Models\DiscountApplication;
use Cartino\Models\Entry;
use Cartino\Models\Favorite;
use Cartino\Models\FidelityCard;
use Cartino\Models\FidelityTransaction;
use Cartino\Models\GlobalSet;
use Cartino\Models\Menu;
use Cartino\Models\MenuItem;
use Cartino\Models\Order;
use Cartino\Models\OrderLine;
use Cartino\Models\Page;
use Cartino\Models\PaymentGateway;
use Cartino\Models\Product;
use Cartino\Models\ProductOption;
use Cartino\Models\ProductReview;
use Cartino\Models\ProductSupplier;
use Cartino\Models\ProductType;
use Cartino\Models\ProductVariant;
use Cartino\Models\PurchaseOrder;
use Cartino\Models\PurchaseOrderItem;
use Cartino\Models\ReviewMedia;
use Cartino\Models\ReviewVote;
use Cartino\Models\Setting;
use Cartino\Models\ShippingMethod;
use Cartino\Models\ShippingRate;
use Cartino\Models\ShippingZone;
use Cartino\Models\ShopperPage;
use Cartino\Models\Site;
use Cartino\Models\SocialAccount;
use Cartino\Models\StockNotification;
use Cartino\Models\StorefrontSection;
use Cartino\Models\StorefrontTemplate;
use Cartino\Models\StorefrontTemplateSection;
use Cartino\Models\Supplier;
use Cartino\Models\TaxRate;
use Cartino\Models\Transaction;
use Cartino\Models\User;
use Cartino\Models\UserGroup;
use Cartino\Models\UserPreference;
use Cartino\Models\VariantPrice;
use Cartino\Models\Wishlist;
use Cartino\Models\WishlistItem;
use Cartino\Policies\AddressPolicy;
use Cartino\Policies\AnalyticsEventPolicy;
use Cartino\Policies\ApiKeyPolicy;
use Cartino\Policies\AppApiTokenPolicy;
use Cartino\Policies\AppInstallationPolicy;
use Cartino\Policies\AppPolicy;
use Cartino\Policies\AppReviewPolicy;
use Cartino\Policies\AppWebhookPolicy;
use Cartino\Policies\AssetContainerPolicy;
use Cartino\Policies\AssetFolderPolicy;
use Cartino\Policies\AssetPolicy;
use Cartino\Policies\AssetTransformationPolicy;
use Cartino\Policies\BrandPolicy;
use Cartino\Policies\CartLinePolicy;
use Cartino\Policies\CartPolicy;
use Cartino\Policies\CatalogPolicy;
use Cartino\Policies\CategoryPolicy;
use Cartino\Policies\ChannelPolicy;
use Cartino\Policies\ControlPanelPolicy;
use Cartino\Policies\CountryPolicy;
use Cartino\Policies\CurrencyPolicy;
use Cartino\Policies\CustomerAddressPolicy;
use Cartino\Policies\CustomerGroupPolicy;
use Cartino\Policies\CustomerPolicy;
use Cartino\Policies\DiscountApplicationPolicy;
use Cartino\Policies\DiscountPolicy;
use Cartino\Policies\EntryPolicy;
use Cartino\Policies\FavoritePolicy;
use Cartino\Policies\FidelityCardPolicy;
use Cartino\Policies\FidelityTransactionPolicy;
use Cartino\Policies\GlobalPolicy;
use Cartino\Policies\MenuItemPolicy;
use Cartino\Policies\MenuPolicy;
use Cartino\Policies\OrderLinePolicy;
use Cartino\Policies\OrderPolicy;
use Cartino\Policies\PagePolicy;
use Cartino\Policies\PaymentGatewayPolicy;
use Cartino\Policies\ProductOptionPolicy;
use Cartino\Policies\ProductPolicy;
use Cartino\Policies\ProductReviewPolicy;
use Cartino\Policies\ProductSupplierPolicy;
use Cartino\Policies\ProductTypePolicy;
use Cartino\Policies\ProductVariantPolicy;
use Cartino\Policies\PurchaseOrderItemPolicy;
use Cartino\Policies\PurchaseOrderPolicy;
use Cartino\Policies\ReviewMediaPolicy;
use Cartino\Policies\ReviewVotePolicy;
use Cartino\Policies\SettingPolicy;
use Cartino\Policies\ShippingMethodPolicy;
use Cartino\Policies\ShippingRatePolicy;
use Cartino\Policies\ShippingZonePolicy;
use Cartino\Policies\ShopperPagePolicy;
use Cartino\Policies\SitePolicy;
use Cartino\Policies\SocialAccountPolicy;
use Cartino\Policies\StockNotificationPolicy;
use Cartino\Policies\StorefrontSectionPolicy;
use Cartino\Policies\StorefrontTemplatePolicy;
use Cartino\Policies\StorefrontTemplateSectionPolicy;
use Cartino\Policies\SupplierPolicy;
use Cartino\Policies\TaxRatePolicy;
use Cartino\Policies\TransactionPolicy;
use Cartino\Policies\UserGroupPolicy;
use Cartino\Policies\UserPolicy;
use Cartino\Policies\UserPreferencePolicy;
use Cartino\Policies\VariantPricePolicy;
use Cartino\Policies\WishlistItemPolicy;
use Cartino\Policies\WishlistPolicy;
use Cartino\Providers\InertiaServiceProvider;
use Cartino\Repositories\BrandRepository;
use Cartino\Repositories\CategoryRepository;
use Cartino\Repositories\ChannelRepository;
use Cartino\Repositories\CountryRepository;
use Cartino\Repositories\CurrencyRepository;
use Cartino\Repositories\CustomerRepository;
use Cartino\Repositories\OrderRepository;
use Cartino\Repositories\PaymentGatewayRepository;
use Cartino\Repositories\ProductRepository;
use Cartino\Repositories\SettingRepository;
use Cartino\Repositories\ShippingMethodRepository;
use Cartino\Repositories\SupplierRepository;
use Cartino\Repositories\TaxRateRepository;
use Cartino\Services\CacheService;
use Cartino\Services\FidelityService;
use Cartino\Services\InventoryService;
use Cartino\Services\NotificationService;
use Cartino\Services\WebhookService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Intervention\Image\ImageManager;

class CartinoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cartino.php', 'cartino');
        $this->mergeConfigFrom(__DIR__.'/../config/permission.php', 'permission');

        // Register OAuth services configuration
        if (file_exists(__DIR__.'/../config/services.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');
        }

        // Register Inertia Service Provider
        $this->app->register(InertiaServiceProvider::class);

        // Register Intervention Image Manager
        $this->app->singleton(ImageManager::class, function ($app) {
            return ImageManager::gd();
        });

        // Register services
        $this->app->singleton(CacheService::class);
        $this->app->singleton(FidelityService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(WebhookService::class);

        // Use Cartino API exception handler by default (overrides app handler)
        $this->app->singleton(ExceptionHandler::class, ApiHandler::class);

        // Register repositories
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(
            SupplierRepositoryInterface::class,
            SupplierRepository::class,
        );
        $this->app->singleton(BrandRepository::class);
        $this->app->singleton(ChannelRepository::class);
        $this->app->singleton(CountryRepository::class);
        $this->app->singleton(CurrencyRepository::class);
        $this->app->singleton(CategoryRepository::class);
        $this->app->singleton(CustomerRepository::class);
        $this->app->singleton(OrderRepository::class);
        $this->app->singleton(SettingRepository::class);
        $this->app->singleton(PaymentGatewayRepository::class);
        $this->app->singleton(TaxRateRepository::class);
        $this->app->singleton(ShippingMethodRepository::class);
        $this->app->singleton(BrandRepository::class);

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExpireFidelityPoints::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->bootRoutes();
        $this->configureAuthentication();
        $this->registerMiddleware();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Ensure package factory classes are loaded in consuming apps
        $factoriesPath = __DIR__.'/../database/factories';
        if (is_dir($factoriesPath)) {
            foreach (glob($factoriesPath.'/*.php') as $factoryFile) {
                require_once $factoryFile;
            }
        }

        // Configure factory namespace guessing for Cartino models
        if (class_exists(Factory::class)) {
            Factory::guessFactoryNamesUsing(function (string $modelName) {
                $base = class_basename($modelName);

                return 'Cartino\\Database\\Factories\\'.$base.'Factory';
            });
        }

        // Ensure Spatie Permission guard defaults align with app guard
        $defaultGuard = config('auth.defaults.guard', 'web');
        config(['permission.defaults.guard' => $defaultGuard]);

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cartino');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cartino.php' => config_path('cartino.php'),
            ], 'cartino-config');

            // Publish permission configuration
            $this->publishes([
                __DIR__.'/../config/permission.php' => config_path('permission.php'),
            ], 'cartino-permission-config');

            // Publish OAuth services configuration
            $this->publishes([
                __DIR__.'/../config/services.php' => config_path('services-oauth.php'),
            ], 'cartino-oauth-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/cartino'),
            ], 'cartino-views');

            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/cartino'),
            ], 'cartino-assets');

            // Publish built assets to public/vendor/cartino
            $this->publishes([
                __DIR__.'/../public/vendor/cartino' => public_path('vendor/cartino'),
            ], 'cartino-assets-built');

            // Publish Vue components
            $this->publishes([
                __DIR__.'/../resources/js/Components' => resource_path('js/Components/Cartino'),
            ], 'cartino-components');

            // Publish translations
            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path(),
            ], 'cartino-lang');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'cartino-migrations');

            // Publish OpenAPI documentation
            $this->publishes([
                __DIR__.'/../openapi.yaml' => base_path('openapi.yaml'),
            ], 'cartino-docs');

            // Register commands
            $this->commands([
                InstallShopperCommand::class,
                BuildAssetsCommand::class,
                CreateAdminUserCommand::class,
                ShowAdminUsersCommand::class,
                OptimizeCommand::class,
                GenerateApiKey::class,
            ]);
        }

        // Register middleware aliases (always, not just in console)
        $router = $this->app['router'];
        $router->aliasMiddleware('cp', ControlPanelMiddleware::class);
        $router->aliasMiddleware('cartino.inertia', HandleInertiaRequests::class);
        $router->aliasMiddleware('cartino.auth', Authenticate::class);
        $router->aliasMiddleware('cartino.performance', MonitorQueryPerformance::class);

        // Register policies
        $this->registerPolicies();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cartino');

        // Register View Composers (Statamic-style translations injection)
        $this->app['view']->composer('cartino::app', TranslationComposer::class);
    }

    protected function configureAuthentication(): void
    {
        // Configure the default login route for redirects
        config(['auth.defaults.login_route' => 'cp.login']);

        // Configure Inertia root view
        if (class_exists(Inertia::class)) {
            Inertia::setRootView('cartino::app');
        }

        // Set the login path for unauthenticated redirects
        if (method_exists($this->app['auth'], 'setDefaultDriver')) {
            $this->app['auth']->viaRequest('api', function ($request) {
                // Custom auth logic if needed
                return null;
            });
        }
    }

    protected function bootRoutes(): void
    {
        Route::group(
            [
                'middleware' => ['web'],
            ],
            function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/cp.php');
            },
        );

        // Load OAuth routes if they exist
        if (file_exists(__DIR__.'/../routes/auth.php')) {
            Route::group(
                [
                    'middleware' => ['web'],
                ],
                function () {
                    $this->loadRoutesFrom(__DIR__.'/../routes/auth.php');
                },
            );
        }

        // Load API OAuth routes if they exist
        if (file_exists(__DIR__.'/../routes/api-auth.php')) {
            Route::group(
                [
                    'middleware' => ['api'],
                    'prefix' => 'api',
                ],
                function () {
                    $this->loadRoutesFrom(__DIR__.'/../routes/api-auth.php');
                },
            );
        }
    }

    /**
     * Register the package policies.
     */
    protected function registerPolicies(): void
    {
        $gate = $this->app[Gate::class];

        // Register policies for Cartino models
        $policies = [
            Address::class => AddressPolicy::class,
            AnalyticsEvent::class => AnalyticsEventPolicy::class,
            ApiKey::class => ApiKeyPolicy::class,
            App::class => AppPolicy::class,
            AppApiToken::class => AppApiTokenPolicy::class,
            AppInstallation::class => AppInstallationPolicy::class,
            AppReview::class => AppReviewPolicy::class,
            AppWebhook::class => AppWebhookPolicy::class,
            Asset::class => AssetPolicy::class,
            AssetContainer::class => AssetContainerPolicy::class,
            AssetFolder::class => AssetFolderPolicy::class,
            AssetTransformation::class => AssetTransformationPolicy::class,
            Brand::class => BrandPolicy::class,
            Cart::class => CartPolicy::class,
            CartLine::class => CartLinePolicy::class,
            Catalog::class => CatalogPolicy::class,
            Category::class => CategoryPolicy::class,
            Channel::class => ChannelPolicy::class,
            Country::class => CountryPolicy::class,
            Currency::class => CurrencyPolicy::class,
            Customer::class => CustomerPolicy::class,
            CustomerAddress::class => CustomerAddressPolicy::class,
            CustomerGroup::class => CustomerGroupPolicy::class,
            Discount::class => DiscountPolicy::class,
            DiscountApplication::class => DiscountApplicationPolicy::class,
            Entry::class => EntryPolicy::class,
            Favorite::class => FavoritePolicy::class,
            FidelityCard::class => FidelityCardPolicy::class,
            FidelityTransaction::class => FidelityTransactionPolicy::class,
            GlobalSet::class => GlobalPolicy::class,
            Menu::class => MenuPolicy::class,
            MenuItem::class => MenuItemPolicy::class,
            Order::class => OrderPolicy::class,
            OrderLine::class => OrderLinePolicy::class,
            Page::class => PagePolicy::class,
            PaymentGateway::class => PaymentGatewayPolicy::class,
            Product::class => ProductPolicy::class,
            ProductOption::class => ProductOptionPolicy::class,
            ProductReview::class => ProductReviewPolicy::class,
            ProductSupplier::class => ProductSupplierPolicy::class,
            ProductType::class => ProductTypePolicy::class,
            ProductVariant::class => ProductVariantPolicy::class,
            PurchaseOrder::class => PurchaseOrderPolicy::class,
            PurchaseOrderItem::class => PurchaseOrderItemPolicy::class,
            ReviewMedia::class => ReviewMediaPolicy::class,
            ReviewVote::class => ReviewVotePolicy::class,
            Setting::class => SettingPolicy::class,
            ShippingMethod::class => ShippingMethodPolicy::class,
            ShippingRate::class => ShippingRatePolicy::class,
            ShippingZone::class => ShippingZonePolicy::class,
            ShopperPage::class => ShopperPagePolicy::class,
            Site::class => SitePolicy::class,
            SocialAccount::class => SocialAccountPolicy::class,
            StockNotification::class => StockNotificationPolicy::class,
            StorefrontSection::class => StorefrontSectionPolicy::class,
            StorefrontTemplate::class => StorefrontTemplatePolicy::class,
            StorefrontTemplateSection::class => StorefrontTemplateSectionPolicy::class,
            Supplier::class => SupplierPolicy::class,
            TaxRate::class => TaxRatePolicy::class,
            Transaction::class => TransactionPolicy::class,
            User::class => UserPolicy::class,
            UserGroup::class => UserGroupPolicy::class,
            UserPreference::class => UserPreferencePolicy::class,
            VariantPrice::class => VariantPricePolicy::class,
            Wishlist::class => WishlistPolicy::class,
            WishlistItem::class => WishlistItemPolicy::class,
        ];

        foreach ($policies as $model => $policy) {
            $gate->policy($model, $policy);
        }

        // Register control panel gates
        $gate->define('access-cp', ControlPanelPolicy::class.'@access');
        $gate->define('view-dashboard', ControlPanelPolicy::class.'@viewDashboard');
        $gate->define('view-analytics', ControlPanelPolicy::class.'@viewAnalytics');
        $gate->define('view-reports', ControlPanelPolicy::class.'@viewReports');
        $gate->define('manage-settings', ControlPanelPolicy::class.'@manageSettings');
        $gate->define('edit-settings', ControlPanelPolicy::class.'@editSettings');
        $gate->define('manage-users', ControlPanelPolicy::class.'@manageUsers');
        $gate->define('manage-roles', ControlPanelPolicy::class.'@manageRoles');
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('force.json', ForceJsonResponse::class);
        $router->aliasMiddleware('api.key', ValidateApiKey::class);
        $router->aliasMiddleware('auth.flexible', AuthenticateSanctumOrApiKey::class);
    }
}
