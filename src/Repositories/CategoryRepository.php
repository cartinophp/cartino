<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\QueryBuilder;

final class CategoryRepository extends BaseRepository
{
    protected string $cachePrefix = 'collections';

    protected function makeModel(): Model
    {
        return new Category;
    }

    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return $query = QueryBuilder::for(Category::class)
            ->allowedFilters(['name', 'slug', 'status'])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->allowedIncludes(['parent', 'children', 'products'])
            ->withCount('children') // Always include children count
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page'))
            ->appends($filters);
    }

    /**
     * Find one by ID or slug
     */
    public function findOne(int|string $slugOrId): ?Category
    {
        return $this->model
            ->where('id', $slugOrId)
            ->orWhere('slug', $slugOrId)
            ->withCount('children')
            ->firstOrFail();
    }

    /**
     * Get all root categories (no parent)
     */
    public function getRootCategories(): Collection
    {
        return $this->model
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->withCount('children')->orderBy('sort_order');
            }])
            ->withCount('children')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get full category tree (recursive)
     */
    public function getTree(): Collection
    {
        return $this->model
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query
                    ->with(['children' => function ($q) {
                        $q->withCount('children')->orderBy('sort_order');
                    }])
                    ->withCount('children')
                    ->orderBy('sort_order');
            }])
            ->withCount('children')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Category
    {
        $category = $this->model->create($data);
        $this->clearCache();

        return $category;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Category
    {
        $category = $this->findOrFail($id);
        $category->update($data);
        $this->clearCache();

        return $category->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $category = $this->findOrFail($id);
        $deleted = $category->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $category = $this->findOrFail($id);

        return ! $category->products()->exists() && ! $category->children()->exists();
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(int $id): Category
    {
        $category = $this->findOrFail($id);
        $newStatus = $category->is_visible ? false : true;
        $category->update(['is_visible' => $newStatus]);
        $this->clearCache();

        return $category->fresh();
    }

    public function findWithProducts(int $id): ?Category
    {
        $cacheKey = $this->getCacheKey('with_products', $id);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            return $this->model->with(['products'])->find($id);
        });
    }

    public function getProducts(int $collectionId, int $perPage = 25): LengthAwarePaginator
    {
        $collection = $this->model->find($collectionId);

        if (! $collection) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        return $collection->products()->paginate($perPage);
    }

    public function addProducts(int $collectionId, array $productIds): array
    {
        $collection = $this->model->find($collectionId);

        if (! $collection) {
            return ['success' => false, 'message' => 'Category not found'];
        }

        try {
            $collection->products()->syncWithoutDetaching($productIds);
            $this->clearCache();

            return [
                'success' => true,
                'message' => 'Products added successfully',
                'added_count' => count($productIds),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function removeProducts(int $collectionId, array $productIds): array
    {
        $collection = $this->model->find($collectionId);

        if (! $collection) {
            return ['success' => false, 'message' => 'Category not found'];
        }

        try {
            $collection->products()->detach($productIds);
            $this->clearCache();

            return [
                'success' => true,
                'message' => 'Products removed successfully',
                'removed_count' => count($productIds),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function bulkAction(string $action, array $ids): array
    {
        $validatedIds = $this->model
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
        $processedCount = 0;
        $errors = [];

        foreach ($validatedIds as $id) {
            try {
                switch ($action) {
                    case 'show':
                        $this->model->where('id', $id)->update(['is_visible' => true]);
                        $processedCount++;
                        break;

                    case 'hide':
                        $this->model->where('id', $id)->update(['is_visible' => false]);
                        $processedCount++;
                        break;

                    case 'delete':
                        $this->model->where('id', $id)->delete();
                        $processedCount++;
                        break;

                    default:
                        $errors[] = "Category ID {$id}: Unknown action '{$action}'";
                }
            } catch (\Exception $e) {
                $errors[] = "Category ID {$id}: {$e->getMessage()}";
            }
        }

        $this->clearCache();

        return [
            'processed' => $processedCount,
            'total' => count($ids),
            'errors' => $errors,
            'success' => count($errors) === 0,
        ];
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }

    protected function getFilterableFields(): array
    {
        return [
            'name',
            'slug',
            'is_visible',
            'sort_order',
            'created_at',
            'updated_at',
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'description'];
    }

    protected function getDefaultSortField(): string
    {
        return 'sort_order';
    }

    protected function getDefaultSortDirection(): string
    {
        return 'asc';
    }
}
