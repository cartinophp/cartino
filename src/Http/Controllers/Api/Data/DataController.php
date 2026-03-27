<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api\Data;

use Cartino\Dictionaries\AddressTypes;
use Cartino\Dictionaries\BasicDictionary;
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
use Cartino\Http\Controllers\Api\ApiController;
use Cartino\Models\DictionaryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DataController extends ApiController
{
    /**
     * Get all available data endpoints (meta information)
     */
    public function index(): JsonResponse
    {
        return $this->successResponse([
            'dictionaries' => [
                'endpoint' => '/api/data/dictionaries',
                'count' => count($this->getAllDictionaries()),
            ],
            'statuses' => [
                'endpoint' => '/api/data/statuses',
                'types' => ['product', 'customer', 'order', 'general'],
            ],
        ]);
    }

    /**
     * List all available dictionaries
     */
    public function dictionaries(): JsonResponse
    {
        $dictionaries = $this->getAllDictionaries();

        $list = collect($dictionaries)->map(function ($class, $handle) {
            $instance = $this->instantiateDictionary($class, $handle);

            return [
                'handle' => $handle,
                'title' => $instance->title() ?? ucfirst($handle),
                'keywords' => $instance->keywords(),
                'count' => count($instance->options()),
                'is_extensible' => $this->isExtensible($handle),
                'custom_items' => count($this->getCustomItems($handle)),
            ];
        })->values();

        return $this->successResponse($list);
    }

    /**
     * Get options for a specific dictionary
     */
    public function dictionary(Request $request, string $handle): JsonResponse
    {
        $dictionaries = $this->getAllDictionaries();

        if (! isset($dictionaries[$handle])) {
            return $this->errorResponse("Dictionary '{$handle}' not found", 404);
        }

        $cacheKey = "dictionary:{$handle}:".($request->get('search') ?? 'all');

        $data = Cache::remember($cacheKey, 3600, function () use ($dictionaries, $handle, $request) {
            $dictionary = $this->instantiateDictionary($dictionaries[$handle], $handle);

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
                'is_extensible' => $this->isExtensible($handle),
                'has_custom_items' => count($this->getCustomItems($handle)) > 0,
            ];
        });

        return $this->successResponse($data);
    }

    /**
     * Get a specific item from a dictionary
     */
    public function dictionaryItem(string $handle, string $key): JsonResponse
    {
        $dictionaries = $this->getAllDictionaries();

        if (! isset($dictionaries[$handle])) {
            return $this->errorResponse("Dictionary '{$handle}' not found", 404);
        }

        $dictionary = $this->instantiateDictionary($dictionaries[$handle], $handle);
        $item = $dictionary->get($key);

        if (! $item) {
            return $this->errorResponse("Item '{$key}' not found in dictionary '{$handle}'", 404);
        }

        return $this->successResponse([
            'value' => $item->value(),
            'label' => $item->label(),
            'extra' => $item->extra(),
            'is_custom' => $this->isCustomItem($handle, $key),
        ]);
    }

    /**
     * Search across all dictionaries
     */
    public function searchDictionaries(Request $request): JsonResponse
    {
        $query = $request->get('q');

        if (! $query) {
            return $this->errorResponse('Search query is required', 422);
        }

        $dictionaries = $this->getAllDictionaries();

        $results = collect($dictionaries)
            ->map(function ($class, $handle) use ($query) {
                $dictionary = $this->instantiateDictionary($class, $handle);
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
     * Get all registered dictionaries (built-in + custom)
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
        $custom = config('cartino.custom_dictionaries', []);

        return array_merge($builtIn, $custom);
    }

    /**
     * Instantiate a dictionary with custom items merged
     */
    private function instantiateDictionary(string $class, string $handle): BasicDictionary
    {
        $dictionary = new $class;

        // If this dictionary is extensible, create an extended version
        if ($this->isExtensible($handle)) {
            $customItems = $this->getCustomItems($handle);

            if (! empty($customItems)) {
                $dictionary = new class($dictionary, $customItems) extends BasicDictionary
                {
                    private BasicDictionary $baseDictionary;

                    private array $customItems;

                    public function __construct(BasicDictionary $baseDictionary, array $customItems)
                    {
                        $this->baseDictionary = $baseDictionary;
                        $this->customItems = $customItems;
                        $this->keywords = $baseDictionary->keywords();
                    }

                    protected function getItems(): array
                    {
                        // Get base items via reflection (since getItems is protected)
                        $reflection = new \ReflectionClass($this->baseDictionary);
                        $method = $reflection->getMethod('getItems');
                        $method->setAccessible(true);
                        $baseItems = $method->invoke($this->baseDictionary);

                        // Merge custom items
                        return array_merge($baseItems, $this->customItems);
                    }

                    public function keywords(): array
                    {
                        return $this->baseDictionary->keywords();
                    }
                };
            }
        }

        return $dictionary;
    }

    /**
     * Check if a dictionary handle is extensible
     */
    private function isExtensible(string $handle): bool
    {
        $extensible = config('cartino.extensible_dictionaries', [
            'address_types',
            'payment_providers',
            'shipping_types',
            'order_statuses',
            'payment_statuses',
            'units',
        ]);

        return in_array($handle, $extensible);
    }

    /**
     * Get custom items for a dictionary from config AND database
     * Merge priority: Base (code) → Config → Database
     */
    private function getCustomItems(string $handle): array
    {
        $customItems = [];

        // 1. Get from config
        $configItems = config("cartino.dictionary_extensions.{$handle}", []);

        // 2. Get from database
        $dbItems = DictionaryItem::forDictionary($handle)
            ->enabled()
            ->orderBy('order')
            ->get()
            ->map(function ($item) {
                return array_merge(
                    [
                        'value' => $item->value,
                        'label' => $item->label,
                    ],
                    $item->extra ?? [],
                    [
                        '_source' => 'database',
                        '_db_id' => $item->id,
                    ],
                );
            })
            ->toArray();

        // 3. Merge (DB overrides config if same value)
        $merged = collect($configItems);

        foreach ($dbItems as $dbItem) {
            // Check if value exists in config
            $existingIndex = $merged->search(fn ($item) => $item['value'] === $dbItem['value']);

            if ($existingIndex !== false) {
                // Override config with DB
                $merged[$existingIndex] = $dbItem;
            } else {
                // Add new DB item
                $merged->push($dbItem);
            }
        }

        return $merged->toArray();
    }

    /**
     * Check if an item is a custom item
     */
    private function isCustomItem(string $handle, string $key): bool
    {
        $customItems = $this->getCustomItems($handle);

        foreach ($customItems as $item) {
            if (($item['value'] ?? null) === $key) {
                return true;
            }
        }

        return false;
    }
}
