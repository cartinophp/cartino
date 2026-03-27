<?php

namespace Cartino\Repositories;

use Cartino\Models\Wishlist;
use Cartino\Models\WishlistItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class WishlistRepository extends BaseRepository
{
    protected string $cachePrefix = 'wishlist';

    protected int $cacheTtl = 3600; // 1 hour

    protected function makeModel(): Model
    {
        return new Wishlist;
    }

    /**
     * Get paginated wishlists with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Wishlist::class)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['name', 'created_at'])
            ->allowedIncludes(['customer', 'items', 'items.product'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID
     */
    public function findOne(int $id): ?Wishlist
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create one
     */
    public function createOne(array $data): Wishlist
    {
        $wishlist = $this->model->create($data);
        $this->clearCache();

        return $wishlist;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Wishlist
    {
        $wishlist = $this->findOrFail($id);
        $wishlist->update($data);
        $this->clearCache();

        return $wishlist->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $wishlist = $this->findOrFail($id);
        $deleted = $wishlist->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Wishlists can always be deleted
    }

    /**
     * Get paginated (legacy method - to be deprecated)
     */
    public function getPaginated(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->query()->with(['customer']);

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhereHas('customer', function ($customerQuery) use (
                    $search,
                ) {
                    $customerQuery->where('email', 'like', "%{$search}%")->orWhere(
                        'first_name',
                        'like',
                        "%{$search}%",
                    )->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Customer filter
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Date range filter
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Add item to wishlist
     */
    public function addItem(int $wishlistId, array $itemData): array
    {
        $wishlist = $this->find($wishlistId);

        // Check if item already exists
        $existingItem = $wishlist->items()->where('product_id', $itemData['product_id'])->first();

        if ($existingItem) {
            // Update quantity
            $existingItem->update([
                'quantity' => $existingItem->quantity + ($itemData['quantity'] ?? 1),
                'notes' => $itemData['notes'] ?? $existingItem->notes,
            ]);
            $item = $existingItem;
        } else {
            // Create new item
            $item = $wishlist
                ->items()
                ->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'] ?? 1,
                    'notes' => $itemData['notes'] ?? null,
                ]);
        }

        $this->clearCache();

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'notes' => $item->notes,
            'created_at' => $item->created_at,
        ];
    }

    /**
     * Remove item from wishlist
     */
    public function removeItem(int $wishlistId, int $itemId): bool
    {
        $wishlist = $this->find($wishlistId);
        $deleted = $wishlist->items()->where('id', $itemId)->delete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted > 0;
    }

    /**
     * Clear all items from wishlist
     */
    public function clearItems(int $wishlistId): int
    {
        $wishlist = $this->find($wishlistId);
        $deleted = $wishlist->items()->delete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    /**
     * Generate share token for wishlist
     */
    public function generateShareToken(int $wishlistId): string
    {
        $token = Str::random(32);

        $this->update($wishlistId, [
            'share_token' => $token,
            'is_shared' => true,
        ]);

        return $token;
    }

    /**
     * Find wishlist by share token
     */
    public function findByShareToken(string $token): ?Wishlist
    {
        return $this->model
            ->where('share_token', $token)
            ->where('is_shared', true)
            ->with(['customer', 'items.product'])
            ->first();
    }

    /**
     * Get wishlist statistics
     */
    public function getStatistics(): array
    {
        $cacheKey = $this->getCacheKey('statistics', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            $total = $this->model->count();
            $active = $this->model->where('status', 'active')->count();
            $shared = $this->model->where('is_shared', true)->count();
            $avgItems = $this->model->withCount('items')->avg('items_count') ?? 0;

            return [
                'total_wishlists' => $total,
                'active_wishlists' => $active,
                'shared_wishlists' => $shared,
                'average_items_per_wishlist' => round($avgItems, 2),
                'total_items' => WishlistItem::count(),
                'conversion_rate' => 0, // TODO: Calculate conversion rate
            ];
        });
    }

    /**
     * Bulk delete wishlists
     */
    public function bulkDelete(array $wishlistIds): int
    {
        $deleted = $this->model->whereIn('id', $wishlistIds)->delete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    /**
     * Get popular wishlist items
     */
    public function getPopularItems(int $limit = 10): array
    {
        return WishlistItem::query()
            ->selectRaw('product_id, COUNT(*) as wishlist_count')
            ->groupBy('product_id')
            ->orderBy('wishlist_count', 'desc')
            ->limit($limit)
            ->with('product:id,name,price')
            ->get()
            ->toArray();
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
