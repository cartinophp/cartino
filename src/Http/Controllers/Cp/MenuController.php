<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Http\Controllers\Controller;
use Cartino\Http\Controllers\Cp\Concerns\HandlesFlashMessages;
use Cartino\Http\Requests\Menu\ReorderMenuItemsRequest;
use Cartino\Http\Requests\Menu\StoreMenuItemRequest;
use Cartino\Http\Requests\Menu\StoreMenuRequest;
use Cartino\Http\Requests\Menu\UpdateMenuItemRequest;
use Cartino\Http\Requests\Menu\UpdateMenuRequest;
use Cartino\Models\Menu;
use Cartino\Models\MenuItem;
use Cartino\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    use HandlesFlashMessages;

    public function __construct(
        private MenuService $menuService,
    ) {}

    public function index(): Response
    {
        $menus = $this->menuService->getAllMenus();

        return Inertia::render('menus/index', [
            'menus' => $menus,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('menus/create');
    }

    public function store(StoreMenuRequest $request)
    {
        $menu = $this->menuService->createMenu($request->validated());

        $this->flashSuccess(__('flash.menus.created'));

        return redirect()->route('cp.menus.edit', $menu->handle);
    }

    public function edit(string $handle): Response
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();
        $menuData = $this->menuService->getMenu($handle);

        return Inertia::render('menus/edit', [
            'menu' => $menuData,
        ]);
    }

    public function update(UpdateMenuRequest $request, string $handle)
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();
        $this->menuService->updateMenu($menu, $request->validated());

        $this->flashSuccess(__('flash.menus.updated'));

        return back();
    }

    public function destroy(string $handle)
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();
        $this->menuService->deleteMenu($menu);

        $this->flashSuccess(__('flash.menus.deleted'));

        return redirect()->route('cp.menus.index');
    }

    public function duplicate(string $handle)
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();
        $newMenu = $this->menuService->duplicateMenu($menu);

        $this->flashSuccess(__('flash.menus.duplicated'));

        return redirect()->route('cp.menus.edit', $newMenu->handle);
    }

    // Menu Items Management
    public function storeItem(StoreMenuItemRequest $request, string $menuHandle): JsonResponse
    {
        $menu = Menu::where('handle', $menuHandle)->firstOrFail();
        $item = $this->menuService->createMenuItem($menu, $request->validated());

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => __('flash.menu_items.created'),
        ]);
    }

    public function updateItem(UpdateMenuItemRequest $request, string $menuHandle, MenuItem $item): JsonResponse
    {
        $item = $this->menuService->updateMenuItem($item, $request->validated());

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => __('flash.menu_items.updated'),
        ]);
    }

    public function destroyItem(string $menuHandle, MenuItem $item): JsonResponse
    {
        $this->menuService->deleteMenuItem($item);

        return response()->json([
            'success' => true,
            'message' => __('flash.menu_items.deleted'),
        ]);
    }

    public function reorderItems(ReorderMenuItemsRequest $request, string $menuHandle): JsonResponse
    {
        $menu = Menu::where('handle', $menuHandle)->firstOrFail();
        $this->menuService->reorderMenuItems($menu, $request->validated()['items']);

        return response()->json([
            'success' => true,
            'message' => __('flash.menu_items.reordered'),
        ]);
    }

    public function moveItem(Request $request, string $menuHandle, MenuItem $item): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menu_items,id',
            'sort_order' => 'required|integer|min:0',
        ]);

        $item = $this->menuService->moveMenuItem($item, $validated['parent_id'], $validated['sort_order']);

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => __('flash.menu_items.moved'),
        ]);
    }
}
