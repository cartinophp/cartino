<?php

declare(strict_types=1);

namespace Cartino\Http\Middleware;

use Cartino\Models\Market;
use Cartino\Models\Site;
use Closure;
use Illuminate\Http\Request;

class AcceptMarketHeaders
{
    /**
     * Handle incoming request and parse market-related headers.
     *
     * Supported headers:
     * - X-Market: Market code (e.g., "IT-B2C", "US-B2B")
     * - X-Market-ID: Market ID
     * - X-Site: Site handle
     * - X-Site-ID: Site ID
     * - X-Channel: Channel slug
     * - X-Channel-ID: Channel ID
     * - X-Catalog: Catalog slug
     * - X-Catalog-ID: Catalog ID
     * - X-Currency: Currency code (e.g., "EUR", "USD")
     * - X-Locale: Locale code (e.g., "it_IT", "en_US")
     * - X-Country: Country code (e.g., "IT", "US")
     * - Accept-Language: Browser language (fallback for locale)
     * - Accept-Currency: Preferred currency (custom header)
     */
    public function handle(Request $request, Closure $next)
    {
        $this->parseMarketHeaders($request);
        $this->parseSiteHeaders($request);
        $this->parseChannelHeaders($request);
        $this->parseCatalogHeaders($request);
        $this->parseCurrencyHeaders($request);
        $this->parseLocaleHeaders($request);
        $this->parseCountryHeaders($request);

        return $next($request);
    }

    /**
     * Parse market headers.
     */
    protected function parseMarketHeaders(Request $request): void
    {
        $market = null;

        // Try X-Market header (code)
        if ($request->hasHeader('X-Market')) {
            $marketCode = strtoupper($request->header('X-Market'));
            $market = Market::where('code', $marketCode)->active()->first();
        }

        // Try X-Market-ID header
        if (! $market && $request->hasHeader('X-Market-ID')) {
            $marketId = (int) $request->header('X-Market-ID');
            $market = Market::find($marketId);
        }

        if ($market && $market->status === 'active') {
            session(['market_id' => $market->id]);
            $request->attributes->set('market', $market);
            $request->merge(['market_id' => $market->id]);
        }
    }

    /**
     * Parse site headers.
     */
    protected function parseSiteHeaders(Request $request): void
    {
        $site = null;

        // Try X-Site header (handle)
        if ($request->hasHeader('X-Site')) {
            $siteHandle = $request->header('X-Site');
            $site = Site::where('handle', $siteHandle)->active()->first();
        }

        // Try X-Site-ID header
        if (! $site && $request->hasHeader('X-Site-ID')) {
            $siteId = (int) $request->header('X-Site-ID');
            $site = Site::find($siteId);
        }

        if ($site && $site->status === 'active') {
            session(['site_id' => $site->id]);
            $request->attributes->set('site', $site);
            $request->merge(['site_id' => $site->id]);
        }
    }

    /**
     * Parse channel headers.
     */
    protected function parseChannelHeaders(Request $request): void
    {
        // Try X-Channel header (slug)
        if ($request->hasHeader('X-Channel')) {
            $channelSlug = $request->header('X-Channel');
            session(['channel_slug' => $channelSlug]);
            $request->merge(['channel_slug' => $channelSlug]);
        }

        // Try X-Channel-ID header
        if ($request->hasHeader('X-Channel-ID')) {
            $channelId = (int) $request->header('X-Channel-ID');
            session(['channel_id' => $channelId]);
            $request->merge(['channel_id' => $channelId]);
        }
    }

    /**
     * Parse catalog headers.
     */
    protected function parseCatalogHeaders(Request $request): void
    {
        // Try X-Catalog header (slug)
        if ($request->hasHeader('X-Catalog')) {
            $catalogSlug = $request->header('X-Catalog');
            session(['catalog_slug' => $catalogSlug]);
            $request->merge(['catalog_slug' => $catalogSlug]);
        }

        // Try X-Catalog-ID header
        if ($request->hasHeader('X-Catalog-ID')) {
            $catalogId = (int) $request->header('X-Catalog-ID');
            session(['catalog_id' => $catalogId]);
            $request->merge(['catalog_id' => $catalogId]);
        }
    }

    /**
     * Parse currency headers.
     */
    protected function parseCurrencyHeaders(Request $request): void
    {
        $currency = null;

        // Try X-Currency header
        if ($request->hasHeader('X-Currency')) {
            $currency = strtoupper($request->header('X-Currency'));
        }

        // Try Accept-Currency header (custom)
        if (! $currency && $request->hasHeader('Accept-Currency')) {
            $currency = strtoupper($request->header('Accept-Currency'));
        }

        if ($currency && strlen($currency) === 3) {
            // Validate against market if available
            $market = $request->attributes->get('market');
            if ($market && ! $market->supportsCurrency($currency)) {
                // Invalid currency for this market, ignore
                return;
            }

            session(['currency' => $currency]);
            $request->merge(['currency' => $currency]);
        }
    }

    /**
     * Parse locale headers.
     */
    protected function parseLocaleHeaders(Request $request): void
    {
        $locale = null;

        // Try X-Locale header
        if ($request->hasHeader('X-Locale')) {
            $locale = $request->header('X-Locale');
        }

        // Try Accept-Language header (standard)
        if (! $locale && $request->hasHeader('Accept-Language')) {
            $locale = $this->parseAcceptLanguage($request->header('Accept-Language'));
        }

        if ($locale) {
            // Normalize locale format (it-IT → it_IT)
            $locale = str_replace('-', '_', $locale);

            // Validate against market if available
            $market = $request->attributes->get('market');
            if ($market && ! $market->supportsLocale($locale)) {
                // Try language part only (it_IT → it)
                if (str_contains($locale, '_')) {
                    $language = explode('_', $locale)[0];
                    if ($market->supportsLocale($language)) {
                        $locale = $language;
                    } else {
                        // Invalid locale for this market, ignore
                        return;
                    }
                } else {
                    return;
                }
            }

            session(['locale' => $locale]);
            $request->merge(['locale' => $locale]);
            app()->setLocale($locale);
        }
    }

    /**
     * Parse country headers.
     */
    protected function parseCountryHeaders(Request $request): void
    {
        // Try X-Country header
        if ($request->hasHeader('X-Country')) {
            $countryCode = strtoupper($request->header('X-Country'));

            if (strlen($countryCode) === 2) {
                session(['country_code' => $countryCode]);
                $request->merge(['country_code' => $countryCode]);
            }
        }
    }

    /**
     * Parse Accept-Language header.
     */
    protected function parseAcceptLanguage(string $acceptLanguage): ?string
    {
        // Parse Accept-Language header (e.g., "it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7")
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $language) {
            $locale = trim(explode(';', $language)[0]);
            $locale = str_replace('-', '_', $locale);

            // Return first valid locale
            if (preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $locale)) {
                return $locale;
            }
        }

        return null;
    }
}
