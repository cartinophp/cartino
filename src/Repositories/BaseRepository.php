<?php

namespace Cartino\Repositories;

use Cartino\Contracts\RepositoryInterface;
use Cartino\Traits\OptimizesQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

abstract class BaseRepository implements RepositoryInterface
{
    use OptimizesQueries;

    protected Model $model;

    protected Builder $query;

    protected array $with = [];

    protected string $cachePrefix = '';

    protected int $cacheTtl = 3600;

    public function __construct()
    {
        $this->model = $this->makeModel();
        $this->query = $this->model->newQuery();
    }

    abstract protected function makeModel(): Model;

    public function all(array $columns = ['*']): Collection
    {
        $cacheKey = $this->getCacheKey('all', md5(serialize($columns)));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($columns) {
            return $this->applyRelations()->get($columns);
        });
    }

    public function paginate(int $perPage = 20, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->applyRelations()->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        $cacheKey = $this->getCacheKey('find', $id);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $columns) {
            return $this->applyRelations()->find($id, $columns);
        });
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        $cacheKey = $this->getCacheKey('find', $id);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $columns) {
            return $this->applyRelations()->findOrFail($id, $columns);
        });
    }

    public function findWhere(array $where, array $columns = ['*']): Collection
    {
        $cacheKey = $this->getCacheKey('findWhere', md5(serialize($where)));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($where, $columns) {
            $query = $this->applyRelations();

            foreach ($where as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }

            return $query->get($columns);
        });
    }

    public function findWhereFirst(array $where, array $columns = ['*']): ?Model
    {
        $query = $this->applyRelations();

        foreach ($where as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->first($columns);
    }

    public function create(array $attributes): Model
    {
        $model = $this->model->create($attributes);

        $this->clearCache();

        return $model;
    }

    public function update(int $id, array $attributes): Model
    {
        $model = $this->findOrFail($id);
        $model->update($attributes);

        $this->clearCache();

        return $model;
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);
        $result = $model->delete();

        $this->clearCache();

        return $result;
    }

    public function with(array $relations): static
    {
        $this->with = array_merge($this->with, $relations);

        return $this;
    }

    public function whereHas(string $relation, ?callable $callback = null): static
    {
        $this->query->whereHas($relation, $callback);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    public function search(string $term): static
    {
        // Override this method in specific repositories
        return $this;
    }

    protected function applyRelations(): Builder
    {
        if (! empty($this->with)) {
            $this->query->with($this->with);
        }

        return $this->query;
    }

    protected function getCacheKey(string $method, mixed $identifier): string
    {
        return sprintf('%s:%s:%s', $this->cachePrefix, $method, $identifier);
    }

    protected function clearCache(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([$this->cachePrefix])->flush();
        }
    }

    public function resetQuery(): static
    {
        $this->query = $this->model->newQuery();
        $this->with = [];

        return $this;
    }

    /**
     * Standard CRUD operations - Override if custom logic needed
     */
    public function createOne(array $data): Model
    {
        $model = $this->model->create($data);
        $this->clearCache();

        return $model;
    }

    public function updateOne(int $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        $this->clearCache();

        return $model->fresh();
    }

    public function deleteOne(int $id): bool
    {
        $model = $this->findOrFail($id);
        $deleted = $model->delete();
        $this->clearCache();

        return $deleted;
    }

    public function canDelete(int $id): bool
    {
        $model = $this->findOrFail($id);
        $restrictions = $this->getDeleteRestrictions($model);

        return empty($restrictions);
    }

    /**
     * Override in child repositories to define relationships that prevent deletion
     * Example: ['products' => 'Has associated products']
     */
    protected function getDeleteRestrictions(Model $model): array
    {
        return [];
    }

    public function toggleStatus(int $id): Model
    {
        $model = $this->findOrFail($id);
        $statusField = $this->getStatusField();
        $currentStatus = $model->{$statusField};
        $newStatus = $this->getToggledStatus($currentStatus);

        $model->update([$statusField => $newStatus]);
        $this->clearCache();

        return $model->fresh();
    }

    protected function getStatusField(): string
    {
        return 'status';
    }

    protected function getToggledStatus(string $current): string
    {
        return match ($current) {
            'active' => 'inactive',
            'inactive' => 'active',
            'published' => 'draft',
            'draft' => 'published',
            default => $current,
        };
    }
}
