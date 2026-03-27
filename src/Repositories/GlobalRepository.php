<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\GlobalSet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GlobalRepository extends BaseRepository
{
    protected string $cachePrefix = 'global';

    protected int $cacheTtl = 3600; // 1 hour

    protected function makeModel(): Model
    {
        return new GlobalSet;
    }

    /**
     * Get paginated globals with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(GlobalSet::class)
            ->allowedFilters([
                'handle',
                'title',
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['handle', 'title', 'created_at', 'updated_at'])
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }

    /**
     * Find one by ID or handle
     */
    public function findOne(int|string $id): ?GlobalSet
    {
        if (is_numeric($id)) {
            return $this->model->find($id);
        }

        return $this->model->byHandle($id)->first();
    }

    /**
     * Create a new global
     */
    public function createOne(array $data): GlobalSet
    {
        $globalSet = $this->model->create($data);
        $this->clearCache();

        return $globalSet;
    }

    /**
     * Update a global
     */
    public function updateOne(int $id, array $data): GlobalSet
    {
        $globalSet = $this->model->findOrFail($id);
        $globalSet->update($data);
        $this->clearCache();

        return $globalSet->fresh();
    }

    /**
     * Delete a global
     */
    public function deleteOne(int $id): bool
    {
        $globalSet = $this->model->findOrFail($id);
        $deleted = $globalSet->delete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    /**
     * Check if global can be deleted
     */
    public function canDelete(int $id): bool
    {
        // All globals can be deleted
        return true;
    }

    /**
     * Get global by handle
     */
    public function getByHandle(string $handle): ?GlobalSet
    {
        return $this->model->byHandle($handle)->first();
    }

    /**
     * Get all globals
     */
    public function getAll(): Collection
    {
        return $this->model->orderBy('handle')->get();
    }

    /**
     * Update global data by handle
     */
    public function updateByHandle(string $handle, array $data): ?GlobalSet
    {
        $globalSet = $this->getByHandle($handle);

        if (! $globalSet) {
            return null;
        }

        $globalSet->update(['data' => $data]);
        $this->clearCache();

        return $globalSet->fresh();
    }

    /**
     * Set a specific value in global data
     */
    public function setValue(string $handle, string $key, mixed $value): ?GlobalSet
    {
        $globalSet = $this->getByHandle($handle);

        if (! $globalSet) {
            return null;
        }

        $globalSet->set($key, $value);
        $globalSet->save();
        $this->clearCache();

        return $globalSet->fresh();
    }

    /**
     * Get a specific value from global data
     */
    public function getValue(string $handle, string $key, mixed $default = null): mixed
    {
        $globalSet = $this->getByHandle($handle);

        if (! $globalSet) {
            return $default;
        }

        return $globalSet->get($key, $default);
    }
}
