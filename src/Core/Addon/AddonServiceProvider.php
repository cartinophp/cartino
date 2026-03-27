<?php

declare(strict_types=1);

namespace Cartino\Core\Addon;

use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    /**
     * Register addon services
     */
    public function register(): void
    {
        $this->app->singleton(AddonRepository::class);

        $this->app->singleton(AddonManager::class, function ($app) {
            return new AddonManager($app->make(AddonRepository::class));
        });

        $this->app->alias(AddonManager::class, 'addon.manager');

        // Register active addons
        $this->app->booting(function () {
            $manager = $this->app->make(AddonManager::class);
            $manager->registerActive();
        });
    }

    /**
     * Bootstrap addon services
     */
    public function boot(): void
    {
        // Boot active addons after application is booted
        $this->app->booted(function () {
            $manager = $this->app->make(AddonManager::class);
            $manager->bootActive();
        });

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
    }
}
