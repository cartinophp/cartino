<?php

namespace Cartino\Repositories;

use Cartino\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentMethodRepository extends BaseRepository
{
    protected string $cachePrefix = 'payment_methods';

    protected function makeModel(): Model
    {
        return new PaymentMethod;
    }

    /**
     * Get paginated payment methods with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(PaymentMethod::class)
            ->allowedFilters([
                'title',
                'slug',
                'status',
                AllowedFilter::exact('site_id'),
                AllowedFilter::scope('published'),
            ])
            ->allowedSorts(['title', 'created_at', 'published_at'])
            ->allowedIncludes(['site'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Get unique providers
     */
    public function getProviders(): array
    {
        $cacheKey = $this->getCacheKey('providers', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model
                ->select('provider')
                ->distinct()
                ->orderBy('provider')
                ->pluck('provider')
                ->toArray();
        });
    }

    /**
     * Update sort orders for payment methods
     */
    public function updateSortOrders(array $sortOrders): void
    {
        $this->clearCache();

        foreach ($sortOrders as $id => $sortOrder) {
            $this->model->where('id', $id)->update(['sort_order' => $sortOrder]);
        }
    }

    /**
     * Toggle payment method status
     */
    public function toggleStatus(int $id): PaymentMethod
    {
        $this->clearCache();

        $paymentMethod = $this->model->find($id);
        $paymentMethod->update(['is_enabled' => ! $paymentMethod->is_enabled]);

        return $paymentMethod;
    }

    /**
     * Test payment method configuration
     */
    public function testConfiguration(int $id): array
    {
        $paymentMethod = $this->model->find($id);

        if (! $paymentMethod) {
            return ['success' => false, 'message' => 'Payment method not found'];
        }

        // This would implement actual API testing based on provider
        switch ($paymentMethod->provider) {
            case 'stripe':
                return $this->testStripeConfiguration($paymentMethod);
            case 'paypal':
                return $this->testPayPalConfiguration($paymentMethod);
            case 'square':
                return $this->testSquareConfiguration($paymentMethod);
            default:
                return ['success' => true, 'message' => 'Configuration looks valid'];
        }
    }

    /**
     * Create a new payment method
     */
    public function create(array $data): PaymentMethod
    {
        $this->clearCache();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Set default sort order
        if (! isset($data['sort_order'])) {
            $data['sort_order'] = $this->model->max('sort_order') + 1;
        }

        return $this->model->create($data);
    }

    /**
     * Update payment method
     */
    public function update(int $id, array $attributes): Model
    {
        $this->clearCache();

        $paymentMethod = $this->model->find($id);
        $paymentMethod->update($attributes);

        return $paymentMethod;
    }

    /**
     * Delete payment method
     */
    public function delete(int $id): bool
    {
        $this->clearCache();

        return $this->model->find($id)->delete();
    }

    /**
     * Test Stripe configuration
     */
    protected function testStripeConfiguration(PaymentMethod $paymentMethod): array
    {
        $config = $paymentMethod->configuration;

        if (empty($config['secret_key'])) {
            return ['success' => false, 'message' => 'Secret key is required'];
        }

        // Here you would make an actual API call to Stripe
        // For now, just validate the key format
        $isValidFormat = str_starts_with($config['secret_key'], 'sk_');

        return [
            'success' => $isValidFormat,
            'message' => $isValidFormat ? 'Stripe configuration is valid' : 'Invalid Stripe secret key format',
        ];
    }

    /**
     * Test PayPal configuration
     */
    protected function testPayPalConfiguration(PaymentMethod $paymentMethod): array
    {
        $config = $paymentMethod->configuration;

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return ['success' => false, 'message' => 'Client ID and Secret are required'];
        }

        // Here you would make an actual API call to PayPal
        return ['success' => true, 'message' => 'PayPal configuration looks valid'];
    }

    /**
     * Test Square configuration
     */
    protected function testSquareConfiguration(PaymentMethod $paymentMethod): array
    {
        $config = $paymentMethod->configuration;

        if (empty($config['access_token']) || empty($config['application_id'])) {
            return ['success' => false, 'message' => 'Access token and Application ID are required'];
        }

        // Here you would make an actual API call to Square
        return ['success' => true, 'message' => 'Square configuration looks valid'];
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }
}
