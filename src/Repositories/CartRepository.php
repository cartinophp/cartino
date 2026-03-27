<?php

namespace Cartino\Repositories;

use Cartino\Enums\CartStatus;
use Cartino\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CartRepository extends BaseRepository
{
    protected string $cachePrefix = 'cart';

    protected int $cacheTtl = 3600; // 1 hour

    protected function makeModel(): Model
    {
        return new Cart;
    }

    /**
     * Get paginated carts with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Cart::class)
            ->allowedFilters([
                'session_id',
                'email',
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['created_at', 'total_amount', 'abandoned_at'])
            ->allowedIncludes(['customer', 'items', 'items.product'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID
     */
    public function findOne(int $id): ?Cart
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create one
     */
    public function createOne(array $data): Cart
    {
        $cart = $this->model->create($data);
        $this->clearCache();

        return $cart;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Cart
    {
        $cart = $this->findOrFail($id);
        $cart->update($data);
        $this->clearCache();

        return $cart->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $cart = $this->findOrFail($id);
        $deleted = $cart->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Carts can always be deleted
    }

    /**
     * Get paginated (legacy method - to be deprecated)
     */
    public function getPaginated(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->query()->with(['customer']);

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Date range filter
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Amount range filter
        if (! empty($filters['amount_from'])) {
            $query->where('total_amount', '>=', $filters['amount_from']);
        }

        if (! empty($filters['amount_to'])) {
            $query->where('total_amount', '<=', $filters['amount_to']);
        }

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('session_id', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhereHas(
                    'customer',
                    function ($customerQuery) use ($search) {
                        $customerQuery->where('email', 'like', "%{$search}%")->orWhere(
                            'first_name',
                            'like',
                            "%{$search}%",
                        )->orWhere('last_name', 'like', "%{$search}%");
                    },
                );
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Mark cart as abandoned
     */
    public function markAsAbandoned(int $cartId): bool
    {
        $updated = $this->model
            ->where('id', $cartId)
            ->update([
                'status' => CartStatus::ABANDONED,
                'abandoned_at' => now(),
            ]);

        if ($updated) {
            $this->clearCache();
        }

        return $updated > 0;
    }

    /**
     * Mark cart as recovered
     */
    public function markAsRecovered(int $cartId): bool
    {
        $updated = $this->model
            ->where('id', $cartId)
            ->update([
                'recovered' => true,
                'recovered_at' => now(),
                'status' => CartStatus::ACTIVE,
            ]);

        if ($updated) {
            $this->clearCache();
        }

        return $updated > 0;
    }

    /**
     * Mark cart as converted
     */
    public function markAsConverted(int $cartId, int $orderId): bool
    {
        $updated = $this->model
            ->where('id', $cartId)
            ->update([
                'status' => CartStatus::CONVERTED,
                'converted_order_id' => $orderId,
                'recovered' => true,
                'recovered_at' => now(),
            ]);

        if ($updated) {
            $this->clearCache();
        }

        return $updated > 0;
    }

    /**
     * Get carts eligible for recovery
     */
    public function getEligibleForRecovery(int $hoursThreshold = 1)
    {
        return $this->model->eligibleForRecovery()->get();
    }

    /**
     * Get abandoned carts
     */
    public function getAbandoned()
    {
        return $this->model
            ->abandoned()
            ->with(['customer'])
            ->get();
    }

    /**
     * Get active carts
     */
    public function getActive()
    {
        return $this->model
            ->active()
            ->with(['customer'])
            ->get();
    }

    /**
     * Get carts that can be abandoned
     */
    public function getCanBeAbandoned(int $hoursThreshold = 1)
    {
        return $this->model->canBeAbandoned($hoursThreshold)->get();
    }

    /**
     * Auto-mark carts as abandoned
     */
    public function autoMarkAbandoned(int $hoursThreshold = 1): int
    {
        $carts = $this->getCanBeAbandoned($hoursThreshold);
        $marked = 0;

        foreach ($carts as $cart) {
            $cart->markAsAbandoned();
            $marked++;
        }

        return $marked;
    }

    /**
     * Get recovery statistics
     */
    public function getRecoveryStatistics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = $this->model->query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $abandoned = $query->where('status', CartStatus::ABANDONED)->count();
        $recovered = $query->where('recovered', true)->count();
        $converted = $query->where('status', CartStatus::CONVERTED)->count();
        $totalRevenue = $query->sum('total_amount');
        $recoveredRevenue = $query->where('recovered', true)->sum('total_amount');

        return [
            'total_carts' => $total,
            'abandoned_carts' => $abandoned,
            'recovered_carts' => $recovered,
            'converted_carts' => $converted,
            'recovery_rate' => $abandoned > 0 ? round(($recovered / $abandoned) * 100, 2) : 0,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 2) : 0,
            'total_revenue' => $totalRevenue,
            'recovered_revenue' => $recoveredRevenue,
            'lost_revenue' => $totalRevenue - $recoveredRevenue,
        ];
    }

    /**
     * Get top abandoned products
     */
    public function getTopAbandonedProducts(int $limit = 10): array
    {
        return $this->model
            ->abandoned()
            ->whereNotNull('items')
            ->get()
            ->flatMap(function ($cart) {
                return collect($cart->items ?? []);
            })
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                return [
                    'product_id' => $productId,
                    'product_name' => $items->first()['product_name'] ?? 'Unknown',
                    'abandon_count' => $items->count(),
                    'total_quantity' => $items->sum('quantity'),
                    'total_value' => $items->sum(function ($item) {
                        return $item['quantity'] * $item['price'];
                    }),
                ];
            })
            ->sortByDesc('abandon_count')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Clean old carts
     */
    public function cleanOldCarts(int $daysOld = 30): int
    {
        return $this->model
            ->where('created_at', '<', now()->subDays($daysOld))
            ->whereIn('status', [CartStatus::ABANDONED, CartStatus::EXPIRED])
            ->delete();
    }

    /**
     * Get lost revenue from abandoned carts
     */
    public function getLostRevenue(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $query = $this->model->abandoned();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->sum('total_amount') ?? 0.0;
    }

    /**
     * Bulk send recovery emails
     */
    public function bulkSendRecoveryEmails(array $cartIds): int
    {
        $sent = 0;

        foreach ($cartIds as $cartId) {
            $updated = $this->model
                ->where('id', $cartId)
                ->update([
                    'recovery_emails_sent' => DB::raw('COALESCE(recovery_emails_sent, 0) + 1'),
                    'last_recovery_email_sent_at' => now(),
                ]);

            if ($updated) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Get cart by session
     */
    public function getBySession(string $sessionId): ?Cart
    {
        return $this->model
            ->where('session_id', $sessionId)
            ->where('status', CartStatus::ACTIVE)
            ->first();
    }

    /**
     * Get cart by customer
     */
    public function getByCustomer(int $customerId): ?Cart
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->where('status', CartStatus::ACTIVE)
            ->first();
    }

    /**
     * Update cart activity
     */
    public function updateActivity(int $cartId): bool
    {
        return $this->model
            ->where('id', $cartId)
            ->update([
                'last_activity_at' => now(),
            ]) > 0;
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
