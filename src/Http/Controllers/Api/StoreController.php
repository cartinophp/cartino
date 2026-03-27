<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\DTO\PricingContext;
use Cartino\Http\Controllers\Controller;
use Cartino\Http\Resources\MarketResource;
use Cartino\Http\Resources\PricingContextResource;
use Cartino\Models\Market;
use Cartino\Support\MarketRouteHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Get current store configuration and context.
     */
    public function index(Request $request): JsonResponse
    {
        $market = MarketRouteHelper::current();
        $locale = MarketRouteHelper::currentLocale();

        try {
            $context = PricingContext::fromRequest();
        } catch (\Exception $e) {
            $context = new PricingContext(
                market: $market,
                locale: $locale,
            );
        }

        return response()->json([
            'data' => [
                // Current context
                'context' => new PricingContextResource($context),
                // Current market
                'market' => $market ? new MarketResource($market) : null,
                // Available options
                'available' => [
                    'markets' => MarketResource::collection(MarketRouteHelper::availableMarkets()),
                    'locales' => $this->getAvailableLocalesData($market),
                    'currencies' => $this->getAvailableCurrenciesData($market),
                ],
                // Market switcher data (for UI)
                'switcher' => MarketRouteHelper::getSwitcherData(),
                // Store info
                'store' => [
                    'name' => config('app.name'),
                    'url' => config('app.url'),
                    'environment' => app()->environment(),
                ],
            ],
        ]);
    }

    /**
     * Update store context (market, locale, currency, etc.).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'market_id' => ['sometimes', 'integer', 'exists:markets,id'],
            'market_code' => ['sometimes', 'string', 'exists:markets,code'],
            'site_id' => ['sometimes', 'integer', 'exists:sites,id'],
            'channel_id' => ['sometimes', 'integer', 'exists:channels,id'],
            'catalog_id' => ['sometimes', 'integer', 'exists:catalogs,id'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'country_code' => ['sometimes', 'string', 'size:2'],
        ]);

        // Resolve market
        $market = null;
        if (isset($validated['market_id'])) {
            $market = Market::find($validated['market_id']);
        } elseif (isset($validated['market_code'])) {
            $market = Market::where('code', $validated['market_code'])->first();
        }

        // Validate market exists if provided
        if ((isset($validated['market_id']) || isset($validated['market_code'])) && ! $market) {
            return response()->json([
                'message' => 'Market not found',
            ], 404);
        }

        // Validate currency is supported by market
        if (isset($validated['currency']) && $market && ! $market->supportsCurrency($validated['currency'])) {
            return response()->json([
                'message' => "Currency {$validated['currency']} is not supported by market {$market->code}",
                'supported_currencies' => $market->getCurrencies(),
            ], 422);
        }

        // Validate locale is supported by market
        if (isset($validated['locale']) && $market && ! $market->supportsLocale($validated['locale'])) {
            return response()->json([
                'message' => "Locale {$validated['locale']} is not supported by market {$market->code}",
                'supported_locales' => $market->getLocales(),
            ], 422);
        }

        // Create and save context
        $context = PricingContext::create(
            marketId: $market?->id ?? session('market_id'),
            siteId: $validated['site_id'] ?? session('site_id'),
            channelId: $validated['channel_id'] ?? session('channel_id'),
            catalogId: $validated['catalog_id'] ?? session('catalog_id'),
            currency: $validated['currency'] ?? null,
            locale: $validated['locale'] ?? null,
            countryCode: $validated['country_code'] ?? session('country_code'),
        );

        // Save to session
        $context->saveToSession();

        // Set app locale
        if ($context->locale) {
            app()->setLocale($context->locale);
        }

        return response()->json([
            'message' => 'Store context updated successfully',
            'data' => [
                'context' => new PricingContextResource($context),
                'market' => $market ? new MarketResource($market) : null,
            ],
        ]);
    }

    /**
     * Reset store context to defaults.
     */
    public function reset(Request $request): JsonResponse
    {
        // Clear session
        session()->forget(['market_id', 'site_id', 'channel_id', 'catalog_id', 'currency', 'locale', 'country_code']);

        // Get default market
        $defaultMarket = Market::default()->first();

        if ($defaultMarket) {
            session(['market_id' => $defaultMarket->id]);
        }

        return response()->json([
            'message' => 'Store context reset to defaults',
            'data' => [
                'market' => $defaultMarket ? new MarketResource($defaultMarket) : null,
            ],
        ]);
    }

    /**
     * Get available locales with display data.
     */
    protected function getAvailableLocalesData(?Market $market): array
    {
        $locales = MarketRouteHelper::availableLocales($market);
        $currentLocale = MarketRouteHelper::currentLocale();

        return collect($locales)->map(function ($locale) use ($currentLocale) {
            return [
                'code' => $locale,
                'name' => locale_get_display_name($locale, $currentLocale),
                'native_name' => locale_get_display_name($locale, $locale),
                'is_active' => $locale === $currentLocale,
            ];
        })->toArray();
    }

    /**
     * Get available currencies with display data.
     */
    protected function getAvailableCurrenciesData(?Market $market): array
    {
        if (! $market) {
            return [
                [
                    'code' => 'EUR',
                    'symbol' => '€',
                    'name' => 'Euro',
                    'is_active' => true,
                ],
            ];
        }

        $currencies = $market->getCurrencies();
        $currentCurrency = session('currency', $market->default_currency);

        return collect($currencies)->map(function ($currency) use ($currentCurrency) {
            return [
                'code' => $currency,
                'symbol' => $this->getCurrencySymbol($currency),
                'name' => $this->getCurrencyName($currency),
                'is_active' => $currency === $currentCurrency,
            ];
        })->toArray();
    }

    /**
     * Get currency symbol.
     */
    protected function getCurrencySymbol(string $currency): string
    {
        return match ($currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'JPY' => '¥',
            'CHF' => 'Fr',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CNY' => '¥',
            'INR' => '₹',
            default => $currency,
        };
    }

    /**
     * Get currency name.
     */
    protected function getCurrencyName(string $currency): string
    {
        return match ($currency) {
            'EUR' => 'Euro',
            'USD' => 'US Dollar',
            'GBP' => 'British Pound',
            'JPY' => 'Japanese Yen',
            'CHF' => 'Swiss Franc',
            'AUD' => 'Australian Dollar',
            'CAD' => 'Canadian Dollar',
            'CNY' => 'Chinese Yuan',
            'INR' => 'Indian Rupee',
            default => $currency,
        };
    }
}
