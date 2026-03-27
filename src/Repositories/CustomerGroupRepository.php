<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerGroupRepository extends BaseRepository
{
    protected string $cachePrefix = 'customer_groups';

    protected function makeModel(): Model
    {
        return new CustomerGroup;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(CustomerGroup::class)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('is_default'),
            ])
            ->allowedSorts(['name', 'created_at', 'discount_percentage'])
            ->allowedIncludes(['customers'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID
     */
    public function findOne(int $id): ?CustomerGroup
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create one
     */
    public function createOne(array $data): CustomerGroup
    {
        $group = $this->model->create($data);

        $this->clearCache();

        return $group;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): CustomerGroup
    {
        $group = $this->findOrFail($id);
        $group->update($data);

        $this->clearCache();

        return $group->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $group = $this->findOrFail($id);
        $deleted = $group->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $group = $this->findOrFail($id);

        return ! $group->customers()->exists();
    }
}
