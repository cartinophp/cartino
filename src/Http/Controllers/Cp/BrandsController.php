<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Requests\CP\StoreBrandRequest;
use Cartino\Http\Requests\CP\UpdateBrandRequest;
use Cartino\Http\Resources\CP\BrandCollection;
use Cartino\Http\Resources\CP\BrandResource;
use Cartino\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

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
        $this->addDashboardBreadcrumb()->addBreadcrumb('Catalog', 'cp.categories.index')->addBreadcrumb('Brands');

        $filters = $this->getFilters(['search', 'status', 'created_at']);

        $brands = Brand::query()
            ->withCount('products')
            ->when($filters, fn ($query) => $this->applyFilters($query, $filters))
            ->orderBy('name')
            ->paginate(request('per_page', 15));

        $page = Page::make('Brands')
            ->primaryAction('Add brand', route('cp.brands.create'))
            ->secondaryActions([
                ['label' => 'Import', 'url' => route('cp.brands.import')],
                ['label' => 'Export', 'url' => route('cp.brands.export')],
            ]);

        return $this->inertiaResponse('brands/index', [
            'page' => $page->compile(),
            'brands' => new BrandCollection($brands),
            'filters' => $filters,
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

        return $this->inertiaResponse('brands/Create', [
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

        return $this->inertiaResponse('brands/Show', [
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

        return $this->inertiaResponse('brands/Edit', [
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
