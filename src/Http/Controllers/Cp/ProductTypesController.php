<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Http\Requests\StoreProductTypeRequest;
use Cartino\Http\Requests\UpdateProductTypeRequest;
use Cartino\Http\Resources\ProductTypeResource;
use Cartino\Models\ProductType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductTypesController extends Controller
{
    /**
     * Product types index
     */
    public function index(Request $request)
    {
        $page = Page::make('Product Types')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Product Types')
            ->primaryAction('Add product type', '/cp/product-types/create')
            ->secondaryActions([
                ['label' => 'Import', 'url' => '/cp/product-types/import'],
                ['label' => 'Export', 'url' => '/cp/product-types/export'],
            ]);

        // If AJAX request, return data for DataTable
        if ($request->expectsJson()) {
            $query = ProductType::withCount('products');

            // Search filter
            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere(
                        'description',
                        'like',
                        "%{$search}%",
                    );
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');

            $allowedSorts = ['name', 'slug', 'sort_order', 'created_at', 'products_count'];
            if (in_array($sortBy, $allowedSorts)) {
                if ($sortBy === 'products_count') {
                    $query->orderBy('products_count', $sortDirection);
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }
            }

            $productTypes = $query->paginate($request->get('per_page', 20));

            return ProductTypeResource::collection($productTypes);
        }

        return Inertia::render('product-types/index', [
            'page' => $page->compile(),
        ]);
    }

    /**
     * Create product type page
     */
    public function create()
    {
        $page = Page::make('Add product type')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Product Types', '/cp/product-types')
            ->breadcrumb('Add product type')
            ->primaryAction('Save product type', null, ['form' => 'product-type-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('product-types/Create', [
            'page' => $page->compile(),
        ]);
    }

    /**
     * Store product type
     */
    public function store(StoreProductTypeRequest $request)
    {
        $productType = ProductType::create($request->validated());

        // Handle different save actions
        $action = $request->input('_action', 'save');

        return match ($action) {
            'save_continue' => response()->json([
                'message' => 'Product type created successfully',
                'redirect' => "/cp/product-types/{$productType->id}/edit",
            ]),
            'save_add_another' => response()->json([
                'message' => 'Product type created successfully',
                'redirect' => '/cp/product-types/create',
            ]),
            default => response()->json([
                'message' => 'Product type created successfully',
                'redirect' => '/cp/product-types',
            ]),
        };
    }

    /**
     * Show single product type
     */
    public function show(ProductType $productType)
    {
        $productType->loadCount('products');

        if (request()->expectsJson()) {
            return new ProductTypeResource($productType);
        }

        $page = Page::make($productType->name)
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Product Types', '/cp/product-types')
            ->breadcrumb($productType->name)
            ->primaryAction('Edit product type', "/cp/product-types/{$productType->id}/edit")
            ->secondaryActions([
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('product-types/Show', [
            'page' => $page->compile(),
            'productType' => new ProductTypeResource($productType),
        ]);
    }

    /**
     * Edit product type page
     */
    public function edit(ProductType $productType)
    {
        $productType->loadCount('products');

        $page = Page::make("Edit {$productType->name}")
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Product Types', '/cp/product-types')
            ->breadcrumb($productType->name, "/cp/product-types/{$productType->id}")
            ->breadcrumb('Edit')
            ->primaryAction('Update product type', null, ['form' => 'product-type-form'])
            ->secondaryActions([
                ['label' => 'View product type', 'url' => "/cp/product-types/{$productType->id}"],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('product-types/Edit', [
            'page' => $page->compile(),
            'productType' => new ProductTypeResource($productType),
        ]);
    }

    /**
     * Update product type
     */
    public function update(UpdateProductTypeRequest $request, ProductType $productType)
    {
        $productType->update($request->validated());

        return response()->json([
            'message' => 'Product type updated successfully',
            'productType' => new ProductTypeResource($productType->fresh()),
        ]);
    }

    /**
     * Delete product type
     */
    public function destroy(ProductType $productType)
    {
        // Check if product type has products
        if ($productType->products()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete product type that has products assigned. Please reassign or delete the products first.',
            ], 422);
        }

        $productType->delete();

        return response()->json([
            'message' => 'Product type deleted successfully',
        ]);
    }

    /**
     * Handle bulk actions
     */
    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['error' => 'No items selected'], 422);
        }

        $productTypes = ProductType::whereIn('id', $ids);

        return match ($action) {
            'enable' => $this->bulkEnable($productTypes),
            'disable' => $this->bulkDisable($productTypes),
            'delete' => $this->bulkDelete($productTypes),
            default => response()->json(['error' => 'Unknown action'], 422),
        };
    }

    /**
     * Bulk enable product types
     */
    protected function bulkEnable($productTypes)
    {
        $count = $productTypes->update(['is_enabled' => true]);

        return response()->json(['message' => "Enabled {$count} product types"]);
    }

    /**
     * Bulk disable product types
     */
    protected function bulkDisable($productTypes)
    {
        $count = $productTypes->update(['is_enabled' => false]);

        return response()->json(['message' => "Disabled {$count} product types"]);
    }

    /**
     * Bulk delete product types
     */
    protected function bulkDelete($productTypes)
    {
        // Check if any product types have products
        $typesWithProducts = $productTypes
            ->withCount('products')
            ->get()
            ->filter(fn ($type) => $type->products_count > 0);

        if ($typesWithProducts->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete product types that have products assigned. Please reassign or delete the products first.',
            ], 422);
        }

        $count = $productTypes->count();
        $productTypes->delete();

        return response()->json(['message' => "Deleted {$count} product types"]);
    }

    /**
     * Duplicate product type
     */
    public function duplicate(ProductType $productType)
    {
        $duplicate = $productType->replicate();
        $duplicate->name = $productType->name.' Copy';
        $duplicate->slug = $productType->slug.'-copy';
        $duplicate->save();

        return response()->json([
            'message' => 'Product type duplicated successfully',
            'redirect' => "/cp/product-types/{$duplicate->id}/edit",
        ]);
    }

    /**
     * Export product types
     */
    public function export(Request $request)
    {
        $productTypes = ProductType::withCount('products')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product-types-'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($productTypes) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Slug',
                'Description',
                'Enabled',
                'Sort Order',
                'Products Count',
                'Created At',
            ]);

            // CSV data
            foreach ($productTypes as $type) {
                fputcsv($file, [
                    $type->id,
                    $type->name,
                    $type->slug,
                    $type->description,
                    $type->sort_order,
                    $type->products_count,
                    $type->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
