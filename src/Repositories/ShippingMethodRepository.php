<?php

namespace Cartino\Repositories;

use Cartino\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\QueryBuilder;

class ShippingMethodRepository extends BaseRepository
{
    protected string $cachePrefix = 'shipping_methods';

    protected function makeModel(): Model
    {
        return new ShippingMethod;
    }

    /**
     * Get paginated shipping methods with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['zones']);

        return $query = QueryBuilder::for(ShippingMethod::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page'))
            ->appends($filters);
    }

    /**
     * Get enabled shipping methods
     */
    public function getEnabled(): ShippingMethod
    {
        $cacheKey = $this->getCacheKey('enabled', '');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model
                ->with(['zones'])
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Calculate shipping cost
     */
    public function calculateShippingCost(int $methodId, array $cartData): float
    {
        $method = $this->find($methodId);

        if (! $method || ! $method->is_enabled) {
            return 0;
        }

        $weight = $cartData['weight'] ?? 0;
        $subtotal = $cartData['subtotal'] ?? 0;
        $itemCount = $cartData['item_count'] ?? 0;

        switch ($method->calculation_type) {
            case 'fixed':
                return $method->base_cost;

            case 'weight_based':
                $config = $method->config ?? [];
                $costPerKg = $config['cost_per_kg'] ?? 0;

                return $method->base_cost + ($weight * $costPerKg);

            case 'order_total':
                $config = $method->config ?? [];
                $percentage = $config['percentage'] ?? 0;

                return $method->base_cost + (($subtotal * $percentage) / 100);

            case 'item_count':
                $config = $method->config ?? [];
                $costPerItem = $config['cost_per_item'] ?? 0;

                return $method->base_cost + ($itemCount * $costPerItem);

            case 'free_over_amount':
                $config = $method->config ?? [];
                $freeOverAmount = $config['free_over_amount'] ?? 0;

                return $subtotal >= $freeOverAmount ? 0 : $method->base_cost;

            default:
                return $method->base_cost;
        }
    }

    /**
     * Get carriers for filters
     */
    public function getCarriers(): ShippingMethod
    {
        return $this->model
            ->select('carrier')
            ->distinct()
            ->whereNotNull('carrier')
            ->orderBy('carrier')
            ->pluck('carrier');
    }

    /**
     * Get calculation types
     */
    public function getCalculationTypes(): array
    {
        return [
            'fixed' => 'Tariffa fissa',
            'weight_based' => 'Basato sul peso',
            'order_total' => 'Basato sul totale ordine',
            'item_count' => 'Basato sul numero di articoli',
            'free_over_amount' => 'Gratuito sopra un importo',
        ];
    }

    /**
     * Update sort order for multiple shipping methods
     */
    public function updateSortOrder(array $shippingMethods): bool
    {
        try {
            foreach ($shippingMethods as $methodData) {
                $this->model->where('id', $methodData['id'])->update(['sort_order' => $methodData['sort_order']]);
            }

            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update method configuration
     */
    public function updateConfig(int $id, array $config): Model
    {
        $method = $this->find($id);
        $currentConfig = $method->config ?? [];
        $method->update(['config' => array_merge($currentConfig, $config)]);

        $this->clearCache();

        return $method;
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }

    /**
     * Get available shipping zones
     */
    public function getShippingZones(): \Illuminate\Support\ShippingMethod
    {
        $cacheKey = $this->getCacheKey('shipping_zones', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return collect([
                ['code' => 'IT', 'name' => 'Italia', 'region' => 'Europa'],
                ['code' => 'FR', 'name' => 'Francia', 'region' => 'Europa'],
                ['code' => 'DE', 'name' => 'Germania', 'region' => 'Europa'],
                ['code' => 'ES', 'name' => 'Spagna', 'region' => 'Europa'],
                ['code' => 'US', 'name' => 'Stati Uniti', 'region' => 'Nord America'],
                ['code' => 'CA', 'name' => 'Canada', 'region' => 'Nord America'],
                ['code' => 'GB', 'name' => 'Regno Unito', 'region' => 'Europa'],
                ['code' => 'AU', 'name' => 'Australia', 'region' => 'Oceania'],
                ['code' => 'JP', 'name' => 'Giappone', 'region' => 'Asia'],
            ]);
        });
    }

    /**
     * Get available shipping types
     */
    public function getShippingTypes(): \Illuminate\Support\ShippingMethod
    {
        return collect([
            [
                'value' => 'flat_rate',
                'label' => 'Tariffa Fissa',
                'description' => 'Costo fisso per tutte le spedizioni',
            ],
            [
                'value' => 'free',
                'label' => 'Spedizione Gratuita',
                'description' => 'Nessun costo di spedizione',
            ],
            [
                'value' => 'local_pickup',
                'label' => 'Ritiro in Negozio',
                'description' => 'Cliente ritira in negozio',
            ],
            [
                'value' => 'weight_based',
                'label' => 'Basato sul Peso',
                'description' => 'Costo basato sul peso del pacco',
            ],
            [
                'value' => 'zone_based',
                'label' => 'Basato su Zone',
                'description' => 'Costo basato sulla zona di destinazione',
            ],
        ]);
    }

    /**
     * Duplicate a shipping method
     */
    public function duplicate(int $id): ?ShippingMethod
    {
        $originalMethod = $this->model->find($id);

        if (! $originalMethod) {
            return null;
        }

        $duplicatedData = $originalMethod->toArray();
        unset($duplicatedData['id'], $duplicatedData['created_at'], $duplicatedData['updated_at']);
        $duplicatedData['name'] = $duplicatedData['name'].' (Copia)';
        $duplicatedData['slug'] = $duplicatedData['slug'].'-copy-'.time();

        return $this->create($duplicatedData);
    }

    /**
     * Get cache key
     */
    protected function getCacheKey(string $method, mixed $identifier): string
    {
        return $this->cachePrefix.'_'.$method.($identifier ? ('_'.$identifier) : '');
    }
}
