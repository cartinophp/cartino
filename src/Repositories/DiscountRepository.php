<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Discount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DiscountRepository extends BaseRepository
{
    protected string $cachePrefix = 'discounts';

    protected function makeModel(): Model
    {
        return new Discount;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Discount::class)
            ->allowedFilters([
                'code',
                'name',
                'type',
                'status',
                AllowedFilter::scope('active'),
            ])
            ->allowedSorts(['code', 'name', 'created_at', 'starts_at', 'ends_at'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or code
     */
    public function findOne(int|string $codeOrId): ?Discount
    {
        return $this->model
            ->where('id', $codeOrId)
            ->orWhere('code', $codeOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Discount
    {
        $discount = $this->model->create($data);

        $this->clearCache();

        return $discount;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Discount
    {
        $discount = $this->findOrFail($id);
        $discount->update($data);

        $this->clearCache();

        return $discount->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $discount = $this->findOrFail($id);
        $deleted = $discount->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Discounts can always be deleted
    }

    /**
     * Toggle discount status
     */
    public function toggleStatus(int $id): Discount
    {
        $discount = $this->findOrFail($id);
        $newStatus = $discount->status === 'active' ? 'inactive' : 'active';
        $discount->update(['status' => $newStatus]);

        $this->clearCache();

        return $discount->fresh();
    }
}
