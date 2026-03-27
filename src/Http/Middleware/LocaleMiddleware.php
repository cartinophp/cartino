<?php

namespace Cartino\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get locale from session, URL parameter, or user preference
        $locale = $this->determineLocale($request);

        // Set the application locale
        App::setLocale($locale);

        // Store the locale in session for future requests
        Session::put('locale', $locale);

        // Share locale with views
        view()->share('currentLocale', $locale);
        view()->share('availableLocales', Config::get('app.available_locales', ['en', 'it']));

        return $next($request);
    }

    /**
     * Determine the locale for the current request.
     */
    private function determineLocale(Request $request): string
    {
        $availableLocales = Config::get('app.available_locales', ['en', 'it']);
        $defaultLocale = Config::get('app.locale', 'en');

        // 1. Check URL parameter
        if ($request->has('locale') && in_array($request->get('locale'), $availableLocales)) {
            return $request->get('locale');
        }

        // 2. Check session
        if (Session::has('locale') && in_array(Session::get('locale'), $availableLocales)) {
            return Session::get('locale');
        }

        // 3. Check user preference (if authenticated)
        if (auth()->check() && auth()->user()->locale && in_array(auth()->user()->locale, $availableLocales)) {
            return auth()->user()->locale;
        }

        // 4. Check browser language
        $browserLocale = $request->getPreferredLanguage($availableLocales);
        if ($browserLocale && in_array($browserLocale, $availableLocales)) {
            return $browserLocale;
        }

        // 5. Return default locale
        return $defaultLocale;
    }
}
