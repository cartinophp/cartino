<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Models\Catalog;
use Cartino\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class SiteController
{
    public function index(Request $request): Response
    {
        $query = Site::query()->with(['channels'])->withCount('channels');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('handle', 'like', "%{$search}%")->orWhere(
                    'domain',
                    'like',
                    "%{$search}%",
                );
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('country')) {
            $query->forCountry($request->input('country'));
        }

        if ($request->filled('currency')) {
            $query->where('default_currency', $request->input('currency'));
        }

        $sites = $query->orderBy('priority', 'asc')->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('sites/index', [
            'sites' => $sites,
            'availableCountries' => $this->getAvailableCountries(),
            'availableCurrencies' => $this->getAvailableCurrencies(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('sites/form', [
            'site' => null,
            'availableCountries' => $this->getAvailableCountries(),
            'availableLocales' => $this->getAvailableLocales(),
            'availableCurrencies' => $this->getAvailableCurrencies(),
            'availableCatalogs' => [],
        ]);
    }

    public function edit(Site $site): Response
    {
        $site->load(['channels', 'catalogs']);

        return Inertia::render('sites/form', [
            'site' => $site,
            'availableCountries' => $this->getAvailableCountries(),
            'availableLocales' => $this->getAvailableLocales(),
            'availableCurrencies' => $this->getAvailableCurrencies(),
            'availableCatalogs' => Catalog::select(['id', 'name', 'handle', 'description'])->orderBy('name')->get(),
        ]);
    }

    private function getAvailableCountries(): array
    {
        return require database_path('data/countries.php');
    }

    private function getAvailableLocales(): array
    {
        return [
            ['code' => 'en_US', 'name' => 'English (US)'],
            ['code' => 'en_GB', 'name' => 'English (UK)'],
            ['code' => 'it_IT', 'name' => 'Italian'],
            ['code' => 'fr_FR', 'name' => 'French'],
            ['code' => 'de_DE', 'name' => 'German'],
            ['code' => 'es_ES', 'name' => 'Spanish'],
            ['code' => 'pt_PT', 'name' => 'Portuguese'],
            ['code' => 'nl_NL', 'name' => 'Dutch'],
        ];
    }

    private function getAvailableCurrencies(): array
    {
        return require database_path('data/currencies.php');
    }
}
