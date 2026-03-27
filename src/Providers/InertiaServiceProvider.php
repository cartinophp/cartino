<?php

namespace Cartino\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class InertiaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Inertia::share([
            // Flash messages
            'flash' => function () {
                return [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                    'warning' => Session::get('warning'),
                    'info' => Session::get('info'),
                ];
            },
            // Authentication
            'auth' => function () {
                return [
                    'user' => auth()->user(),
                ];
            },
            // Translations - Direct file loading approach
            'translations' => function () {
                $locale = App::getLocale();

                // Helper function to load translation files directly
                $loadTranslations = function ($filename, $locale) {
                    // Use the package's lang directory, not the application's
                    $path = __DIR__."/../../resources/lang/{$locale}/{$filename}.php";

                    if (file_exists($path)) {
                        $translations = include $path;

                        return $translations;
                    }

                    // Fallback to English if locale file doesn't exist
                    $fallbackPath = __DIR__."/../../resources/lang/en/{$filename}.php";

                    if (file_exists($fallbackPath)) {
                        $translations = include $fallbackPath;

                        return $translations;
                    }

                    return [];
                };

                return [
                    // Shopper specific translations (load directly)
                    'cartino' => $loadTranslations('cartino', $locale),
                    // Core admin translations
                    'admin' => $loadTranslations('admin', $locale),
                    // Module-specific translations
                    'products' => $loadTranslations('products', $locale),
                    'categories' => $loadTranslations('categories', $locale),
                    'brands' => $loadTranslations('brands', $locale),
                    'pages' => $loadTranslations('pages', $locale),
                    // Laravel system translations (use Lang facade)
                    'validation' => Lang::get('validation', [], $locale),
                    'auth' => Lang::get('auth', [], $locale),
                    'pagination' => Lang::get('pagination', [], $locale),
                ];
            },
            // Application config
            'config' => function () {
                return [
                    'app_name' => config('app.name'),
                    'app_url' => config('app.url'),
                    'currency' => config('cartino.currency', 'EUR'),
                    'timezone' => config('app.timezone'),
                ];
            },
        ]);
    }
}
