<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Get translations for a specific locale
     */
    public function translations(Request $request, string $locale)
    {
        $availableLocales = Config::get('app.available_locales', ['en', 'it']);

        if (! in_array($locale, array_keys($availableLocales))) {
            abort(404, 'Locale not found');
        }

        App::setLocale($locale);

        $translations = [
            // Core admin translations
            'admin' => Lang::get('admin', [], $locale),
            // Module-specific translations
            'products' => Lang::get('products', [], $locale),
            'categories' => Lang::get('categories', [], $locale),
            'brands' => Lang::get('brands', [], $locale),
            'pages' => Lang::get('pages', [], $locale),
            // Laravel validation messages
            'validation' => Lang::get('validation', [], $locale),
            // Auth messages
            'auth' => Lang::get('auth', [], $locale),
            // Pagination
            'pagination' => Lang::get('pagination', [], $locale),
        ];

        return response()->json($translations);
    }

    /**
     * Set locale for current session
     */
    public function setLocale(Request $request)
    {
        $locale = $request->input('locale');
        $availableLocales = Config::get('app.available_locales', ['en', 'it']);

        if (! in_array($locale, array_keys($availableLocales))) {
            return response()->json(['error' => 'Invalid locale'], 422);
        }

        // Set session locale
        Session::put('locale', $locale);

        // Update user preference if authenticated
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        return response()->json([
            'message' => __('admin.messages.locale_updated'),
            'locale' => $locale,
        ]);
    }

    /**
     * Update user locale preference
     */
    public function updateUserLocale(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|in:'.implode(',', array_keys(Config::get('app.available_locales', ['en', 'it']))),
        ]);

        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        auth()
            ->user()
            ->update([
                'locale' => $request->locale,
            ]);

        Session::put('locale', $request->locale);

        return response()->json([
            'message' => __('admin.messages.locale_updated'),
            'locale' => $request->locale,
        ]);
    }

    /**
     * Get available locales
     */
    public function availableLocales()
    {
        return response()->json([
            'locales' => Config::get('app.available_locales', ['en', 'it']),
            'current' => App::getLocale(),
        ]);
    }
}
