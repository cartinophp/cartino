<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Subscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SubscriptionRepository extends BaseRepository
{
    protected string $cachePrefix = 'subscriptions';

    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }

    protected function makeModel(): Subscription
    {
        return app(Subscription::class);
    }

    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Subscription::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('site_id'),
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('product_id'),
                AllowedFilter::exact('product_variant_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('billing_interval'),
                AllowedFilter::exact('billing_interval_count'),
                AllowedFilter::scope('active'),
                AllowedFilter::scope('paused'),
                AllowedFilter::scope('cancelled'),
                'subscription_number',
                'payment_method',
            ])
            ->allowedSorts([
                'id',
                'subscription_number',
                'status',
                'price',
                'started_at',
                'next_billing_date',
                'billing_cycle_count',
                'total_billed',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->with(['customer', 'product', 'variant', 'currency']);

        return $query->paginate($perPage);
    }

    public function findOne(int $id): ?Subscription
    {
        return $this->getCachedData("subscription.{$id}", fn () => $this->model->with([
            'customer',
            'product',
            'variant',
            'currency',
            'orders',
        ])->find($id));
    }

    public function createOne(array $data): Subscription
    {
        $subscription = $this->model->create($data);
        $this->clearCache();

        return $subscription->load(['customer', 'product', 'variant', 'currency']);
    }

    public function updateOne(int $id, array $data): Subscription
    {
        $subscription = $this->model->findOrFail($id);
        $subscription->update($data);
        $this->clearCache();

        return $subscription->load(['customer', 'product', 'variant', 'currency']);
    }

    public function deleteOne(int $id): bool
    {
        $subscription = $this->model->findOrFail($id);
        $deleted = $subscription->delete();
        $this->clearCache();

        return $deleted;
    }

    public function canDelete(int $id): bool
    {
        $subscription = $this->model->find($id);

        if (! $subscription) {
            return false;
        }

        // Can only delete if cancelled or expired
        return in_array($subscription->status, ['cancelled', 'expired']);
    }

    public function pause(int $id, ?string $reason = null, ?\DateTime $resumesAt = null): Subscription
    {
        $subscription = $this->model->findOrFail($id);
        $subscription->pause($reason, $resumesAt);
        $this->clearCache();

        return $subscription->fresh(['customer', 'product', 'variant']);
    }

    public function resume(int $id): Subscription
    {
        $subscription = $this->model->findOrFail($id);
        $subscription->resume();
        $this->clearCache();

        return $subscription->fresh(['customer', 'product', 'variant']);
    }

    public function cancel(
        int $id,
        ?string $reason = null,
        ?string $comment = null,
        bool $immediately = false,
    ): Subscription {
        $subscription = $this->model->findOrFail($id);
        $subscription->cancel($reason, $comment, $immediately);
        $this->clearCache();

        return $subscription->fresh(['customer', 'product', 'variant']);
    }

    public function getActive(): Collection
    {
        return $this->getCachedData('subscriptions.active', fn () => $this->model
            ->active()
            ->with(['customer', 'product', 'variant'])
            ->get());
    }

    public function getDueForBilling(): Collection
    {
        return $this->model
            ->dueForBilling()
            ->with(['customer', 'product', 'variant', 'currency'])
            ->get();
    }

    public function getTrialEnding(int $days = 3): Collection
    {
        return $this->model
            ->trialEnding($days)
            ->with(['customer', 'product'])
            ->get();
    }

    public function getCustomerSubscriptions(int $customerId): Collection
    {
        return $this->getCachedData("customer.{$customerId}.subscriptions", fn () => $this->model
            ->where('customer_id', $customerId)
            ->with(['product', 'variant', 'currency'])
            ->orderBy('created_at', 'desc')
            ->get());
    }

    public function getProductSubscriptions(int $productId): Collection
    {
        return $this->getCachedData("product.{$productId}.subscriptions", fn () => $this->model
            ->where('product_id', $productId)
            ->with(['customer', 'variant'])
            ->orderBy('created_at', 'desc')
            ->get());
    }

    public function getSubscriptionOrders(int $id): Collection
    {
        $subscription = $this->model->findOrFail($id);

        return $subscription->orders()->with(['customer', 'lines'])->orderBy('created_at', 'desc')->get();
    }
}
