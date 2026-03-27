<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\Channel;
use Cartino\Models\Market;
use Cartino\Models\Site;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class LocaleResolver
{
    /**
     * Get fallback chain for a locale.
     * Example: it_IT → it → en_US → en
     */
    public function getFallbackChain(string $locale): array
    {
        $cacheKey = "locale_fallback_chain:{$locale}";

        return Cache::remember($cacheKey, 3600, function () use ($locale) {
            return $this->buildFallbackChain($locale);
        });
    }

    /**
     * Build fallback chain for a locale.
     */
    protected function buildFallbackChain(string $locale): array
    {
        $chain = [];

        // Add language part if locale has region (it_IT → it)
        if (str_contains($locale, '_')) {
            [$language, $region] = explode('_', $locale, 2);
            $chain[] = $language;
        }

        // Add default locale
        $defaultLocale = Config::get('app.locale', 'en');
        if ($defaultLocale !== $locale && ! in_array($defaultLocale, $chain)) {
            $chain[] = $defaultLocale;

            // Add language part of default locale if has region
            if (str_contains($defaultLocale, '_')) {
                [$language, $region] = explode('_', $defaultLocale, 2);
                if (! in_array($language, $chain)) {
                    $chain[] = $language;
                }
            }
        }

        // Add fallback locale from config
        $fallbackLocale = Config::get('app.fallback_locale', 'en');
        if ($fallbackLocale !== $defaultLocale && ! in_array($fallbackLocale, $chain)) {
            $chain[] = $fallbackLocale;
        }

        return array_values(array_unique($chain));
    }

    /**
     * Resolve locale from multiple sources.
     * Priority: explicit > session > user > market > site > channel > default
     */
    public function resolve(
        ?string $explicit = null,
        ?int $marketId = null,
        ?int $siteId = null,
        ?int $channelId = null,
    ): string {
        // 1. Explicit locale (from parameter/URL)
        if ($explicit && $this->isValidLocale($explicit)) {
            return $explicit;
        }

        // 2. Session locale
        if (session()->has('locale')) {
            $locale = session('locale');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 3. User preference
        if (auth()->check() && auth()->user()->locale) {
            $locale = auth()->user()->locale;
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 4. Market default locale
        if ($marketId) {
            $market = Market::find($marketId);
            if ($market && $market->default_locale) {
                return $market->default_locale;
            }
        }

        // 5. Site default locale
        if ($siteId) {
            $site = Site::find($siteId);
            if ($site && $site->locale) {
                return $site->locale;
            }
        }

        // 6. Channel default locale
        if ($channelId) {
            $channel = Channel::find($channelId);
            if ($channel && method_exists($channel, 'getDefaultLocale')) {
                $locale = $channel->getDefaultLocale();
                if ($locale) {
                    return $locale;
                }
            }
        }

        // 7. Browser language (Accept-Language header)
        if (request()->hasHeader('Accept-Language')) {
            $browserLocale = $this->parseBrowserLocale(request()->header('Accept-Language'));
            if ($browserLocale && $this->isValidLocale($browserLocale)) {
                return $browserLocale;
            }
        }

        // 8. Default from config
        return Config::get('app.locale', 'en_US');
    }

    /**
     * Check if locale is valid and supported.
     */
    public function isValidLocale(string $locale): bool
    {
        $availableLocales = Config::get('app.available_locales', ['en', 'en_US']);

        return in_array($locale, $availableLocales);
    }

    /**
     * Parse browser Accept-Language header.
     */
    protected function parseBrowserLocale(string $acceptLanguage): ?string
    {
        // Parse Accept-Language header (e.g., "it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7")
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $language) {
            $locale = trim(explode(';', $language)[0]);
            $locale = str_replace('-', '_', $locale);

            if ($this->isValidLocale($locale)) {
                return $locale;
            }

            // Try language part only (it-IT → it)
            if (str_contains($locale, '_')) {
                [$lang, $region] = explode('_', $locale, 2);
                if ($this->isValidLocale($lang)) {
                    return $lang;
                }
            }
        }

        return null;
    }

    /**
     * Get all available locales from config.
     */
    public function getAvailableLocales(): array
    {
        return Config::get('app.available_locales', ['en', 'en_US', 'it', 'it_IT']);
    }

    /**
     * Normalize locale format (it-IT → it_IT).
     */
    public function normalize(string $locale): string
    {
        return str_replace('-', '_', $locale);
    }
}
