<?php

namespace Cartino\Repositories;

use Cartino\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PaymentGatewayRepository extends BaseRepository
{
    protected string $cachePrefix = 'payment_gateways';

    protected function makeModel(): Model
    {
        return new PaymentGateway;
    }

    /**
     * Get paginated payment gateways with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('provider', 'like', "%{$search}%")->orWhere(
                    'description',
                    'like',
                    "%{$search}%",
                );
            });
        }

        // Status filter
        if (isset($filters['is_enabled'])) {
            $query->where('is_enabled', $filters['is_enabled']);
        }

        // Provider filter
        if (! empty($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        // Test mode filter
        if (isset($filters['test_mode'])) {
            $query->where('test_mode', $filters['test_mode']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'sort_order';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get enabled payment gateways
     */
    public function getEnabled(): Category
    {
        $cacheKey = $this->getCacheKey('enabled', '');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model
                ->enabled()
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Get default payment gateway
     */
    public function getDefault(): ?PaymentGateway
    {
        $cacheKey = $this->getCacheKey('default', '');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->where('is_default', true)->first();
        });
    }

    /**
     * Set gateway as default
     */
    public function setAsDefault(int $id): ?PaymentGateway
    {
        // Remove default from all gateways
        $this->model->where('is_default', true)->update(['is_default' => false]);

        // Set new default
        $gateway = $this->model->find($id);
        if ($gateway) {
            $gateway->update(['is_default' => true, 'is_enabled' => true]);
            $this->clearCache();
        }

        return $gateway;
    }

    /**
     * Update gateway configuration
     */
    public function updateConfig(int $id, array $config): ?PaymentGateway
    {
        $gateway = $this->model->find($id);

        if (! $gateway) {
            return null;
        }

        $currentConfig = $gateway->config ?? [];
        $gateway->update(['config' => array_merge($currentConfig, $config)]);

        $this->clearCache();

        return $gateway->fresh();
    }

    /**
     * Toggle gateway status
     */
    public function toggleStatus(int $id): Model
    {
        $gateway = $this->find($id);
        $gateway->update(['is_enabled' => ! $gateway->is_enabled]);

        $this->clearCache();

        return $gateway;
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(array $sortData): void
    {
        foreach ($sortData as $item) {
            $this->model->where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        $this->clearCache();
    }

    /**
     * Get providers for filters
     */
    public function getProviders(): Category
    {
        return collect([
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'square' => 'Square',
            'adyen' => 'Adyen',
            'braintree' => 'Braintree',
            'mollie' => 'Mollie',
            'razorpay' => 'Razorpay',
            'bank_transfer' => 'Bonifico Bancario',
            'cash_on_delivery' => 'Contrassegno',
        ]);
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
     * Get cache key
     */
    protected function getCacheKey(string $method, mixed $identifier): string
    {
        return $this->cachePrefix.'_'.$method.($identifier ? ('_'.$identifier) : '');
    }
}
