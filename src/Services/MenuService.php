<?php

namespace Cartino\Services;

use Cartino\DTO\Menu\MenuData;
use Cartino\DTO\Menu\MenuItemData;
use Cartino\Models\Menu;
use Cartino\Models\MenuItem;
use Illuminate\Support\Str;

class MenuService
{
    public function getAllMenus(): array
    {
        return Menu::ordered()
            ->get()
            ->map(fn ($menu) => MenuData::fromModel($menu))
            ->toArray();
    }

    public function getMenu(string $handle): ?MenuData
    {
        $menu = Menu::where('handle', $handle)->first();

        return $menu ? MenuData::fromModel($menu) : null;
    }

    public function createMenu(array $data): MenuData
    {
        $menu = Menu::create([
            'handle' => $data['handle'] ?? Str::slug($data['title']),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'settings' => $data['settings'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? $this->getNextSortOrder(),
        ]);

        return MenuData::fromModel($menu);
    }

    public function updateMenu(Menu $menu, array $data): MenuData
    {
        $menu->update([
            'title' => $data['title'] ?? $menu->title,
            'description' => $data['description'] ?? $menu->description,
            'settings' => $data['settings'] ?? $menu->settings,
            'is_active' => $data['is_active'] ?? $menu->is_active,
            'sort_order' => $data['sort_order'] ?? $menu->sort_order,
        ]);

        return MenuData::fromModel($menu->fresh());
    }

    public function deleteMenu(Menu $menu): bool
    {
        return $menu->delete();
    }

    public function createMenuItem(Menu $menu, array $data): MenuItemData
    {
        $item = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $data['parent_id'] ?? null,
            'title' => $data['title'],
            'url' => $data['url'] ?? null,
            'type' => $data['type'] ?? 'link',
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'data' => $data['data'] ?? [],
            'status' => $data['status'] ?? 'active',
            'opens_in_new_window' => $data['opens_in_new_window'] ?? false,
            'css_class' => $data['css_class'] ?? null,
            'sort_order' => $data['sort_order'] ?? $this->getNextItemSortOrder($menu->id, $data['parent_id'] ?? null),
            'depth' => $this->calculateDepth($data['parent_id'] ?? null),
        ]);

        return MenuItemData::fromModel($item);
    }

    public function updateMenuItem(MenuItem $item, array $data): MenuItemData
    {
        $item->update([
            'title' => $data['title'] ?? $item->title,
            'url' => $data['url'] ?? $item->url,
            'type' => $data['type'] ?? $item->type,
            'reference_type' => $data['reference_type'] ?? $item->reference_type,
            'reference_id' => $data['reference_id'] ?? $item->reference_id,
            'data' => $data['data'] ?? $item->data,
            'status' => $data['status'] ?? $item->status,
            'opens_in_new_window' => $data['opens_in_new_window'] ?? $item->opens_in_new_window,
            'css_class' => $data['css_class'] ?? $item->css_class,
        ]);

        return MenuItemData::fromModel($item->fresh());
    }

    public function deleteMenuItem(MenuItem $item): bool
    {
        return $item->delete();
    }

    public function reorderMenuItems(Menu $menu, array $items): bool
    {
        foreach ($items as $index => $itemData) {
            $this->updateItemOrder($itemData, $index, null, 0);
        }

        return true;
    }

    private function updateItemOrder(array $itemData, int $sortOrder, ?int $parentId, int $depth): void
    {
        MenuItem::where('id', $itemData['id'])->update([
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'depth' => $depth,
        ]);

        if (! empty($itemData['children'])) {
            foreach ($itemData['children'] as $index => $childData) {
                $this->updateItemOrder($childData, $index, $itemData['id'], $depth + 1);
            }
        }
    }

    public function moveMenuItem(MenuItem $item, ?int $parentId, int $sortOrder): MenuItemData
    {
        $item->update([
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'depth' => $this->calculateDepth($parentId),
        ]);

        // Update children depths
        $item->updateDepth();

        return MenuItemData::fromModel($item->fresh());
    }

    private function getNextSortOrder(): int
    {
        return Menu::max('sort_order') + 1;
    }

    private function getNextItemSortOrder(int $menuId, ?int $parentId): int
    {
        return MenuItem::where('menu_id', $menuId)->where('parent_id', $parentId)->max('sort_order') + 1;
    }

    private function calculateDepth(?int $parentId): int
    {
        if (! $parentId) {
            return 0;
        }

        $parent = MenuItem::find($parentId);

        return $parent ? ($parent->depth + 1) : 0;
    }

    public function duplicateMenu(Menu $menu): MenuData
    {
        $newMenu = Menu::create([
            'handle' => $menu->handle.'_copy',
            'title' => $menu->title.' (Copy)',
            'description' => $menu->description,
            'settings' => $menu->settings,
            'is_active' => false,
            'sort_order' => $this->getNextSortOrder(),
        ]);

        // Duplicate all items
        $this->duplicateMenuItems($menu->items, $newMenu->id);

        return MenuData::fromModel($newMenu);
    }

    private function duplicateMenuItems($items, int $newMenuId, ?int $newParentId = null): void
    {
        foreach ($items as $item) {
            $newItem = MenuItem::create([
                'menu_id' => $newMenuId,
                'parent_id' => $newParentId,
                'title' => $item->title,
                'url' => $item->url,
                'type' => $item->type,
                'reference_type' => $item->reference_type,
                'reference_id' => $item->reference_id,
                'data' => $item->data,
                'status' => $item->status,
                'opens_in_new_window' => $item->opens_in_new_window,
                'css_class' => $item->css_class,
                'sort_order' => $item->sort_order,
                'depth' => $item->depth,
            ]);

            if ($item->children->isNotEmpty()) {
                $this->duplicateMenuItems($item->children, $newMenuId, $newItem->id);
            }
        }
    }
}
