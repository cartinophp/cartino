<?php

use Cartino\Models\Market;
use Cartino\Support\MarketRouteHelper;
use Illuminate\Support\Collection;

if (! function_exists('market')) {
    /**
     * Get current market instance.
     */
    function market(): ?Market
    {
        return MarketRouteHelper::current();
    }
}

if (! function_exists('market_url')) {
    /**
     * Generate a market-aware URL.
     */
    function market_url(string $path, ?Market $market = null, ?string $locale = null): string
    {
        return MarketRouteHelper::url($path, $market, $locale);
    }
}

if (! function_exists('market_route')) {
    /**
     * Generate a market-aware route.
     */
    function market_route(
        string $name,
        array $parameters = [],
        ?Market $market = null,
        ?string $locale = null,
    ): string {
        return MarketRouteHelper::route($name, $parameters, $market, $locale);
    }
}

if (! function_exists('switch_market')) {
    /**
     * Switch to a different market/locale.
     */
    function switch_market(
        ?Market $market = null,
        ?string $locale = null,
        ?string $returnUrl = null,
    ): string {
        return MarketRouteHelper::switchTo($market, $locale, $returnUrl);
    }
}

if (! function_exists('available_markets')) {
    /**
     * Get available markets.
     */
    function available_markets(): Collection
    {
        return MarketRouteHelper::availableMarkets();
    }
}

if (! function_exists('available_locales')) {
    /**
     * Get available locales for current market.
     */
    function available_locales(?Market $market = null): array
    {
        return MarketRouteHelper::availableLocales($market);
    }
}
