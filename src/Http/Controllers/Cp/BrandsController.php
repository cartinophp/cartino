<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Listing;
use Cartino\Cp\Page;
use Cartino\Http\Requests\CP\StoreBrandRequest;
use Cartino\Http\Requests\CP\UpdateBrandRequest;
use Cartino\Http\Resources\CP\BrandResource;
use Cartino\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class BrandsController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:browse_brands')->only(['index', 'show']);
        $this->middleware('can:create_brands')->only(['create', 'store']);
        $this->middleware('can:update_brands')->only(['edit', 'update']);
        $this->middleware('can:delete_brands')->only(['destroy']);
    }

    /**
     * Display brands listing.
     */
    public function index(Request $request): Response
    {
        $page = Page::make('Brands')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/categories')
            ->breadcrumb('Brands')
            ->primaryAction('Add brand', route('cp.brands.create'))
            ->secondaryActions([
                ['label' => 'Import', 'url' => '#'],
                ['label' => 'Export', 'url' => '#'],
            ]);

        $listing = Listing::make()
            ->column('name', 'Name', sortable: true)
            ->column('slug', 'Slug')
            ->column('products_count', 'Products', sortable: true)
            ->column('status', 'Status', sortable: true)
            ->column('actions', '', sortable: false, width: '100px')
            ->filter('status', 'Status', 'select', [
                ['value' => '', 'label' => 'All'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ], 'All')
            ->bulkAction('enable', 'Enable')
            ->bulkAction('disable', 'Disable')
            ->bulkAction('delete', 'Delete', destructive: true, confirm: 'Delete selected brands?')
            ->bulkAction('export', 'Export')
            ->searchable(placeholder: 'Search brands...')
            ->emptyState('No brands yet', 'Add your first brand to organize your products.', ['label' => 'Add brand', 'url' => route('cp.brands.create')], 'tag')
            ->sort('name', 'asc');

        $data = QueryBuilder::for(Brand::class)
            ->withCount('products')
            ->allowedFilters(['name', 'slug', 'status'])
            ->allowedSorts(['name', 'created_at', 'status', 'products_count'])
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15))
            ->withQueryString();

        return Inertia::render('brands/index', [
            'page' => $page->compile(),
            'listing' => $listing->toArray(),
            'data' => $data,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cp.categories.index')
            ->addBreadcrumb('Brands', 'cartino.brands.index')
            ->addBreadcrumb('Add brand');

        $page = Page::make('Add brand')
            ->primaryAction('Save brand', null, ['form' => 'brand-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('brands/create', [
            'page' => $page->compile(),
        ]);
    }

    /**
     * Store new brand.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated());

        $action = $request->input('_action', 'save');

        $redirectUrl = match ($action) {
            'save_continue' => route('cp.brands.edit', $brand),
            'save_add_another' => route('cp.brands.create'),
            default => route('cp.brands.index'),
        };

        return $this->successResponse('Brand created successfully', [
            'brand' => new BrandResource($brand),
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Display brand details.
     */
    public function show(Brand $brand): Response
    {
        $brand->load(['products' => fn ($query) => $query->limit(10)->latest()]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cp.categories.index')
            ->addBreadcrumb('Brands', 'cartino.brands.index')
            ->addBreadcrumb($brand->name);

        $page = Page::make($brand->name)
            ->primaryAction('Edit brand', route('cp.brands.edit', $brand))
            ->secondaryActions([
                ['label' => 'View in store', 'url' => "/brands/{$brand->slug}", 'target' => '_blank'],
                [
                    'label' => 'Visit website',
                    'url' => $brand->website,
                    'target' => '_blank',
                    'disabled' => ! $brand->website,
                ],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('brands/show', [
            'page' => $page->compile(),
            'brand' => new BrandResource($brand),
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Brand $brand): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cp.categories.index')
            ->addBreadcrumb('Brands', 'cartino.brands.index')
            ->addBreadcrumb($brand->name, route('cp.brands.show', $brand))
            ->addBreadcrumb('Edit');

        $page = Page::make("Edit {$brand->name}")
            ->primaryAction('Update brand', null, ['form' => 'brand-form'])
            ->secondaryActions([
                ['label' => 'View brand', 'url' => route('cp.brands.show', $brand)],
                ['label' => 'View in store', 'url' => "/brands/{$brand->slug}", 'target' => '_blank'],
                [
                    'label' => 'Visit website',
                    'url' => $brand->website,
                    'target' => '_blank',
                    'disabled' => ! $brand->website,
                ],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'BrandGeneralForm'],
                'seo' => ['label' => 'SEO', 'component' => 'BrandSeoForm'],
                'products' => ['label' => 'Products', 'component' => 'BrandProductsForm'],
            ]);

        return Inertia::render('brands/edit', [
            'page' => $page->compile(),
            'brand' => new BrandResource($brand),
        ]);
    }

    /**
     * Update brand.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $brand->update($request->validated());

        return $this->successResponse('Brand updated successfully', [
            'brand' => new BrandResource($brand->fresh()),
        ]);
    }

    /**
     * Delete brand.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        if ($brand->products()->exists()) {
            return $this->errorResponse('Cannot delete brand with products');
        }

        $brand->delete();

        return $this->successResponse('Brand deleted successfully');
    }

    /**
     * Handle bulk operations.
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:enable,disable,delete,export',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:brands,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        return $this->handleBulkOperation($action, $ids, function ($action, $ids) {
            $brands = Brand::whereIn('id', $ids);

            return match ($action) {
                'enable' => $brands->update(['status' => 'active']),
                'disable' => $brands->update(['status' => 'inactive']),
                'delete' => $this->handleBulkDelete($brands),
                'export' => $this->handleBulkExport($brands),
            };
        });
    }

    /**
     * Apply search filter for brands.
     */
    protected function applySearchFilter($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere(
                'website',
                'like',
                "%{$search}%",
            );
        });
    }

    /**
     * Handle bulk delete with validation.
     */
    private function handleBulkDelete($brands): int
    {
        $count = 0;
        $brands
            ->get()
            ->each(function ($brand) use (&$count) {
                if (! $brand->products()->exists()) {
                    $brand->delete();
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Handle bulk export.
     */
    private function handleBulkExport($brands): int
    {
        $count = $brands->count();

        // TODO: Implement actual export logic
        return $count;
    }
}
