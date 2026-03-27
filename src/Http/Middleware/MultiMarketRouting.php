<?php

declare(strict_types=1);

namespace Cartino\Http\Middleware;

use Cartino\Models\Market;
use Cartino\Services\LocaleResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class MultiMarketRouting
{
    public function __construct(
        protected LocaleResolver $localeResolver,
    ) {}

    /**
     * Handle incoming request with multi-market routing.
     * Supports patterns like: /{market}/{locale}/{slug} or /{market}/{slug}
     */
    public function handle(Request $request, Closure $next)
    {
        $this->resolveMarket($request);
        $this->resolveLocale($request);
        $this->configureSite($request);

        return $next($request);
    }

    /**
     * Resolve market from URL segment or session.
     */
    protected function resolveMarket(Request $request): void
    {
        // Try to get market from URL segment (e.g., /it-b2c/...)
        $marketSegment = $request->segment(1);

        $market = null;

        // Check if first segment is a market code
        if ($marketSegment) {
            $market = Market::where('code', strtoupper($marketSegment))
                ->orWhere('handle', $marketSegment)
                ->active()
                ->first();
        }

        // Fallback to session
        if (! $market && session()->has('market_id')) {
            $market = Market::find(session('market_id'));
        }

        // Fallback to default market
        if (! $market) {
            $market = Market::default()->first();
        }

        if ($market) {
            session(['market_id' => $market->id]);
            $request->attributes->set('market', $market);
            app()->instance('market', $market);
        }
    }

    /**
     * Resolve locale from URL segment or context.
     */
    protected function resolveLocale(Request $request): void
    {
        $market = $request->attributes->get('market');
        $localeSegment = null;

        // Check if second segment is a locale (e.g., /it-b2c/it_IT/...)
        if ($market) {
            $secondSegment = $request->segment(2);

            // Verify if it's a valid locale format
            if ($secondSegment && $this->looksLikeLocale($secondSegment)) {
                $localeSegment = $this->localeResolver->normalize($secondSegment);

                // Validate it's supported by the market
                if ($market->supportsLocale($localeSegment)) {
                    $locale = $localeSegment;
                }
            }
        }

        // Use LocaleResolver for fallback chain
        if (! isset($locale)) {
            $locale = $this->localeResolver->resolve(
                explicit: $localeSegment ?? $request->query('locale'),
                marketId: $market?->id,
                siteId: session('site_id'),
                channelId: session('channel_id'),
            );
        }

        // Set application locale
        App::setLocale($locale);
        session(['locale' => $locale]);
        $request->attributes->set('locale', $locale);
    }

    /**
     * Configure site based on resolved market.
     */
    protected function configureSite(Request $request): void
    {
        $market = $request->attributes->get('market');

        if (! $market) {
            return;
        }

        // Get primary site for the market
        $site = $market->sites()->published()->first() ?? $market->sites()->active()->first();

        if ($site) {
            session(['site_id' => $site->id]);
            $request->attributes->set('site', $site);
            app()->instance('site', $site);

            // Set default URL for asset generation
            if ($site->url) {
                URL::forceRootUrl($site->url);
            }
        }
    }

    /**
     * Check if a string looks like a locale (e.g., it, it_IT, en-US).
     */
    protected function looksLikeLocale(string $value): bool
    {
        // Locale patterns: it, it_IT, it-IT, en, en_US, en-US
        return preg_match('/^[a-z]{2}([_-][A-Z]{2})?$/', $value) === 1;
    }
}
