<?php

namespace Cartino\Repositories;

use Cartino\Models\Menu;
use Cartino\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuRepository extends BaseRepository
{
    protected string $cachePrefix = 'menus';

    protected function makeModel(): Model
    {
        return new Menu;
    }

    /**
     * Get paginated menus with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Menu::class)
            ->allowedFilters(['title', 'handle', 'is_active'])
            ->allowedSorts(['title', 'sort_order', 'created_at'])
            ->allowedIncludes(['items'])
            ->withCount('allItems')
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or handle
     */
    public function findOne(int|string $handleOrId): ?Menu
    {
        return $this->model
            ->where('id', $handleOrId)
            ->orWhere('handle', $handleOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Menu
    {
        $menu = $this->model->create($data);
        $this->clearCache();

        return $menu;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Menu
    {
        $menu = $this->findOrFail($id);
        $menu->update($data);
        $this->clearCache();

        return $menu->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $menu = $this->findOrFail($id);
        $deleted = $menu->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Menus can always be deleted
    }

    /**
     * Toggle menu status
     */
    public function toggleStatus(int $id): Menu
    {
        $menu = $this->findOrFail($id);
        $menu->update(['is_active' => ! $menu->is_active]);
        $this->clearCache();

        return $menu->fresh();
    }

    /**
     * Get active menus
     */
    public function getActive(): Category
    {
        $cacheKey = $this->getCacheKey('active', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Get menu by handle
     */
    public function findByHandle(string $handle): ?Menu
    {
        $cacheKey = $this->getCacheKey('handle', $handle);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($handle) {
            return $this->model->where('handle', $handle)->first();
        });
    }

    /**
     * Get menu with tree structure
     */
    public function getWithTree(string $handle): ?Menu
    {
        $menu = $this->findByHandle($handle);

        if (! $menu) {
            return null;
        }

        return $menu->load(['items' => function ($query) {
            $query->whereNull('parent_id')->with('children')->orderBy('sort_order');
        }]);
    }

    /**
     * Duplicate menu
     */
    public function duplicate(int $id): ?Menu
    {
        $originalMenu = $this->find($id);

        if (! $originalMenu) {
            return null;
        }

        $duplicatedData = $originalMenu->toArray();
        unset($duplicatedData['id'], $duplicatedData['created_at'], $duplicatedData['updated_at']);

        $duplicatedData['title'] = $duplicatedData['title'].' (Copy)';
        $duplicatedData['handle'] = $duplicatedData['handle'].'_copy_'.time();
        $duplicatedData['is_active'] = false;

        $newMenu = $this->create($duplicatedData);

        // Duplicate menu items
        $this->duplicateMenuItems($originalMenu->items, $newMenu->id);

        $this->clearCache();

        return $newMenu;
    }

    /**
     * Duplicate menu items recursively
     */
    protected function duplicateMenuItems($items, int $newMenuId, ?int $newParentId = null): void
    {
        foreach ($items as $item) {
            $itemData = $item->toArray();
            unset($itemData['id'], $itemData['created_at'], $itemData['updated_at']);

            $itemData['menu_id'] = $newMenuId;
            $itemData['parent_id'] = $newParentId;

            $newItem = MenuItem::create($itemData);

            if ($item->children->isNotEmpty()) {
                $this->duplicateMenuItems($item->children, $newMenuId, $newItem->id);
            }
        }
    }

    /**
     * Update sort orders
     */
    public function updateSortOrders(array $menus): bool
    {
        try {
            foreach ($menus as $menuData) {
                $this->model->where('id', $menuData['id'])->update(['sort_order' => $menuData['sort_order']]);
            }

            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get menus for select options
     */
    public function getForSelect(): Collection
    {
        return $this->model
            ->select('id', 'title', 'handle')
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
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
