<?php

declare(strict_types=1);

namespace Cartino\Support;

use Cartino\Models\Market;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class MarketRouteHelper
{
    /**
     * Generate a URL for a specific market and locale.
     */
    public static function url(string $path, ?Market $market = null, ?string $locale = null): string
    {
        $market = $market ?? app('market');
        $locale = $locale ?? app()->getLocale();

        if (! $market) {
            return url($path);
        }

        // Build URL: /{market}/{locale}/{path}
        $segments = [
            $market->code,
        ];

        // Only include locale if it's not the market's default
        if ($locale !== $market->default_locale) {
            $segments[] = $locale;
        }

        $segments[] = ltrim($path, '/');

        return url(implode('/', $segments));
    }

    /**
     * Generate a route for a specific market and locale.
     */
    public static function route(
        string $name,
        array $parameters = [],
        ?Market $market = null,
        ?string $locale = null,
    ): string {
        $market = $market ?? app('market');
        $locale = $locale ?? app()->getLocale();

        $parameters = array_merge([
            'market' => $market?->code,
            'locale' => $locale !== $market?->default_locale ? $locale : null,
        ], $parameters);

        // Remove null values
        $parameters = array_filter($parameters, fn ($value) => $value !== null);

        return route($name, $parameters);
    }

    /**
     * Register market-aware routes.
     */
    public static function group(callable $callback): void
    {
        // Register routes with market prefix
        Route::prefix('{market}')->group(function () use ($callback) {
            // Optional locale segment
            Route::prefix('{locale?}')->group($callback);
        });
    }

    /**
     * Get current market from request.
     */
    public static function current(): ?Market
    {
        return app('market') ?? request()->attributes->get('market');
    }

    /**
     * Get current locale from request.
     */
    public static function currentLocale(): string
    {
        return app()->getLocale() ?? request()->attributes->get('locale') ?? 'en';
    }

    /**
     * Get available markets.
     */
    public static function availableMarkets(): Collection
    {
        return Market::active()->published()->get();
    }

    /**
     * Get available locales for current market.
     */
    public static function availableLocales(?Market $market = null): array
    {
        $market = $market ?? self::current();

        if (! $market) {
            return config('app.available_locales', ['en']);
        }

        return $market->getLocales();
    }

    /**
     * Switch to a different market/locale.
     */
    public static function switchTo(
        ?Market $market = null,
        ?string $locale = null,
        ?string $returnUrl = null,
    ): string {
        $market = $market ?? self::current();
        $locale = $locale ?? self::currentLocale();
        $returnUrl = $returnUrl ?? request()->path();

        // Store in session
        if ($market) {
            session(['market_id' => $market->id]);
        }

        if ($locale) {
            session(['locale' => $locale]);
        }

        // Generate URL with new market/locale
        return self::url($returnUrl, $market, $locale);
    }

    /**
     * Check if a market is currently active.
     */
    public static function isActive(Market $market): bool
    {
        $current = self::current();

        return $current && $current->id === $market->id;
    }

    /**
     * Check if a locale is currently active.
     */
    public static function isLocaleActive(string $locale): bool
    {
        return self::currentLocale() === $locale;
    }

    /**
     * Get market switcher data for UI.
     */
    public static function getSwitcherData(): array
    {
        $currentMarket = self::current();
        $currentLocale = self::currentLocale();

        return [
            'current' => [
                'market' => $currentMarket?->only(['id', 'code', 'name', 'type']),
                'locale' => $currentLocale,
            ],
            'available' => [
                'markets' => self::availableMarkets()
                    ->map(fn ($m) => [
                        'id' => $m->id,
                        'code' => $m->code,
                        'name' => $m->name,
                        'type' => $m->type,
                        'is_active' => self::isActive($m),
                        'url' => self::switchTo($m, $currentLocale),
                    ]),
                'locales' => collect(self::availableLocales($currentMarket))
                    ->map(fn ($locale) => [
                        'code' => $locale,
                        'name' => locale_get_display_name($locale, $currentLocale),
                        'is_active' => self::isLocaleActive($locale),
                        'url' => self::switchTo($currentMarket, $locale),
                    ]),
            ],
        ];
    }
}
