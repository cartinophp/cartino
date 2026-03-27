<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Dictionaries\AddressTypes;
use Cartino\Dictionaries\Countries;
use Cartino\Dictionaries\Currencies;
use Cartino\Dictionaries\Entities;
use Cartino\Dictionaries\Languages;
use Cartino\Dictionaries\Locales;
use Cartino\Dictionaries\OrderStatuses;
use Cartino\Dictionaries\PaymentProviders;
use Cartino\Dictionaries\PaymentStatuses;
use Cartino\Dictionaries\PhonePrefixes;
use Cartino\Dictionaries\ShippingTypes;
use Cartino\Dictionaries\Timezones;
use Cartino\Dictionaries\UnitsOfMeasure;
use Cartino\Dictionaries\VatRates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DictionariesController extends ApiController
{
    /**
     * List all available dictionaries
     */
    public function index(): JsonResponse
    {
        $dictionaries = $this->getAllDictionaries();

        $list = collect($dictionaries)->map(function ($class, $handle) {
            $instance = new $class;

            return [
                'handle' => $handle,
                'title' => $instance->title() ?? ucfirst($handle),
                'keywords' => $instance->keywords(),
                'count' => count($instance->options()),
            ];
        })->values();

        return $this->successResponse($list);
    }

    /**
     * Get options for a specific dictionary
     */
    public function show(Request $request, string $handle): JsonResponse
    {
        $dictionaries = $this->getAllDictionaries();

        if (! isset($dictionaries[$handle])) {
            return $this->errorResponse("Dictionary '{$handle}' not found", 404);
        }

        $cacheKey = "dictionary:{$handle}:".($request->get('search') ?? 'all');

        $data = Cache::remember($cacheKey, 3600, function () use ($dictionaries, $handle, $request) {
            $dictionary = new $dictionaries[$handle];

            $search = $request->get('search');
            $items = $dictionary->optionItems($search);

            return [
                'handle' => $handle,
                'title' => $dictionary->title() ?? ucfirst($handle),
                'keywords' => $dictionary->keywords(),
                'options' => $dictionary->options($search),
                'items' => collect($items)
                    ->map(fn ($item) => [
                        'value' => $item->value(),
                        'label' => $item->label(),
                        'extra' => $item->extra(),
                    ])
                    ->values()
                    ->all(),
                'total' => count($items),
            ];
        });

        return $this->successResponse($data);
    }

    /**
     * Get a specific item from a dictionary
     */
    public function item(string $handle, string $key): JsonResponse
    {
        $dictionaries = $this->getAllDictionaries();

        if (! isset($dictionaries[$handle])) {
            return $this->errorResponse("Dictionary '{$handle}' not found", 404);
        }

        $dictionary = new $dictionaries[$handle];
        $item = $dictionary->get($key);

        if (! $item) {
            return $this->errorResponse("Item '{$key}' not found in dictionary '{$handle}'", 404);
        }

        return $this->successResponse([
            'value' => $item->value(),
            'label' => $item->label(),
            'extra' => $item->extra(),
        ]);
    }

    /**
     * Search across all dictionaries
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');

        if (! $query) {
            return $this->errorResponse('Search query is required', 422);
        }

        $dictionaries = $this->getAllDictionaries();

        $results = collect($dictionaries)
            ->map(function ($class, $handle) use ($query) {
                $dictionary = new $class;
                $items = $dictionary->optionItems($query);

                if (empty($items)) {
                    return null;
                }

                return [
                    'handle' => $handle,
                    'title' => $dictionary->title() ?? ucfirst($handle),
                    'items' => collect($items)
                        ->map(fn ($item) => [
                            'value' => $item->value(),
                            'label' => $item->label(),
                            'extra' => $item->extra(),
                        ])
                        ->values()
                        ->all(),
                    'count' => count($items),
                ];
            })
            ->filter()
            ->values();

        return $this->successResponse([
            'query' => $query,
            'results' => $results,
            'total_dictionaries' => $results->count(),
            'total_items' => $results->sum('count'),
        ]);
    }

    /**
     * Get all registered dictionaries
     * This method can be extended to support custom dictionaries via config
     */
    private function getAllDictionaries(): array
    {
        $builtIn = [
            'currencies' => Currencies::class,
            'countries' => Countries::class,
            'languages' => Languages::class,
            'locales' => Locales::class,
            'timezones' => Timezones::class,
            'phone_prefixes' => PhonePrefixes::class,
            'address_types' => AddressTypes::class,
            'payment_providers' => PaymentProviders::class,
            'shipping_types' => ShippingTypes::class,
            'order_statuses' => OrderStatuses::class,
            'payment_statuses' => PaymentStatuses::class,
            'vat_rates' => VatRates::class,
            'units' => UnitsOfMeasure::class,
            'entities' => Entities::class,
        ];

        // Allow extending dictionaries via config
        $custom = config('cartino.dictionaries', []);

        return array_merge($builtIn, $custom);
    }
}
