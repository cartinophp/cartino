<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Listing;
use Cartino\Cp\Page;
use Cartino\Http\Requests\CP\StoreCollectionRequest;
use Cartino\Http\Requests\CP\UpdateCollectionRequest;
use Cartino\Http\Resources\CP\CollectionResource;
use Cartino\Models\Category;
use Cartino\Schema\SchemaRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class CategoriesController extends BaseController
{
    public function __construct(
        protected SchemaRepository $schemas,
    ) {}

    /**
     * Display collections listing.
     */
    public function index(Request $request): Response
    {
        $page = Page::make('Collections')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/categories')
            ->breadcrumb('Collections')
            ->primaryAction('Add collection', route('cp.categories.create'))
            ->secondaryActions([
                ['label' => 'Import', 'url' => '#'],
                ['label' => 'Export', 'url' => '#'],
            ]);

        $listing = Listing::make()
            ->column('name', 'Name', sortable: true)
            ->column('handle', 'Handle')
            ->column('children_count', 'Children', sortable: true)
            ->column('status', 'Status', sortable: true)
            ->column('actions', '', sortable: false, width: '100px')
            ->filter('status', 'Status', 'select', [
                ['value' => '', 'label' => 'All'],
                ['value' => 'published', 'label' => 'Published'],
                ['value' => 'draft', 'label' => 'Draft'],
            ], 'All')
            ->bulkAction('publish', 'Publish')
            ->bulkAction('unpublish', 'Unpublish')
            ->bulkAction('delete', 'Delete', destructive: true, confirm: 'Delete selected collections?')
            ->bulkAction('export', 'Export')
            ->searchable(placeholder: 'Search collections...')
            ->emptyState('No collections yet', 'Create your first collection to organize products.', ['label' => 'Add collection', 'url' => route('cp.categories.create')], 'folder')
            ->sort('name', 'asc');

        $data = QueryBuilder::for(Category::class)
            ->withCount('children')
            ->allowedFilters(['name', 'slug', 'status'])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->allowedIncludes(['parent', 'children'])
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15))
            ->withQueryString();

        return Inertia::render('categories/index', [
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
            ->addBreadcrumb('Collections', 'cartino.collections.index')
            ->addBreadcrumb('Add collection');

        $page = Page::make('Add collection')
            ->primaryAction('Save collection', null, ['form' => 'collection-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('categories/create', [
            'page' => $page->compile(),
            'parents' => Category::whereNull('parent_id')->select('id', 'title')->get(),
        ]);
    }

    /**
     * Store new collection.
     */
    public function store(StoreCollectionRequest $request): JsonResponse
    {
        $collection = Category::create($request->validated());

        $action = $request->input('_action', 'save');

        $redirectUrl = match ($action) {
            'save_continue' => route('cp.collections.edit', $collection),
            'save_add_another' => route('cp.collections.create'),
            default => route('cp.collections.index'),
        };

        return $this->successResponse('Category created successfully', [
            'collection' => new CollectionResource($collection),
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Display collection details.
     */
    public function show(Category $collection): Response
    {
        $collection->load(['products' => fn ($query) => $query->limit(10)->latest()]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cp.categories.index')
            ->addBreadcrumb('Collections', 'cartino.collections.index')
            ->addBreadcrumb($collection->title);

        $page = Page::make($collection->title)
            ->primaryAction('Edit collection', route('cp.collections.edit', $collection))
            ->secondaryActions([
                ['label' => 'View in store', 'url' => $collection->url, 'target' => '_blank'],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('categories/show', [
            'page' => $page->compile(),
            'collection' => new CollectionResource($collection),
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Category $collection): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cp.categories.index')
            ->addBreadcrumb('Collections', 'cartino.collections.index')
            ->addBreadcrumb($collection->title, route('cp.collections.show', $collection))
            ->addBreadcrumb('Edit');

        $page = Page::make("Edit {$collection->title}")
            ->primaryAction('Update collection', null, ['form' => 'collection-form'])
            ->secondaryActions([
                ['label' => 'View collection', 'url' => route('cp.collections.show', $collection)],
                ['label' => 'View in store', 'url' => $collection->url, 'target' => '_blank'],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'CollectionGeneralForm'],
                'products' => ['label' => 'Products', 'component' => 'CollectionProductsForm'],
                'conditions' => [
                    'label' => 'Conditions',
                    'component' => 'CollectionConditionsForm',
                    'show_if' => 'collection_type === "smart"',
                ],
                'seo' => ['label' => 'SEO', 'component' => 'CollectionSeoForm'],
            ]);

        return Inertia::render('categories/edit', [
            'page' => $page->compile(),
            'collection' => new CollectionResource($collection),
            'parents' => Category::whereNull('parent_id')->where('id', '!=', $collection->id)->select('id', 'title')->get(),
        ]);
    }

    /**
     * Update collection.
     */
    public function update(UpdateCollectionRequest $request, Category $collection): JsonResponse
    {
        $collection->update($request->validated());

        return $this->successResponse('Category updated successfully', [
            'collection' => new CollectionResource($collection->fresh()),
        ]);
    }

    /**
     * Delete collection.
     */
    public function destroy(Category $collection): JsonResponse
    {
        $collection->delete();

        return $this->successResponse('Category deleted successfully');
    }

    /**
     * Handle bulk operations.
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:publish,unpublish,delete,export',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:collections,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        return $this->handleBulkOperation($action, $ids, function ($action, $ids) {
            $collections = Category::whereIn('id', $ids);

            return match ($action) {
                'publish' => $collections->update(['status' => 'published']),
                'unpublish' => $collections->update(['status' => 'draft']),
                'delete' => $collections->delete(),
                'export' => $this->handleBulkExport($collections),
            };
        });
    }

    /**
     * Add products to collection.
     */
    public function addProducts(Request $request, Category $collection): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);

        foreach ($request->product_ids as $productId) {
            $collection
                ->products()
                ->syncWithoutDetaching([$productId => [
                    'position' => $collection->products()->count() + 1,
                ]]);
        }

        return $this->successResponse('Products added to collection successfully');
    }

    /**
     * Remove products from collection.
     */
    public function removeProducts(Request $request, Category $collection): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);

        $collection->products()->detach($request->product_ids);

        return $this->successResponse('Products removed from collection successfully');
    }

    /**
     * Apply search filter for collections.
     */
    protected function applySearchFilter($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere(
                'handle',
                'like',
                "%{$search}%",
            );
        });
    }

    /**
     * Apply custom filters.
     */
    protected function applyCustomFilter($query, string $key, $value): void
    {
        match ($key) {
            'collection_type' => $query->where('collection_type', $value),
            default => parent::applyCustomFilter($query, $key, $value),
        };
    }

    /**
     * Handle bulk export.
     */
    private function handleBulkExport($collections): int
    {
        $count = $collections->count();

        // TODO: Implement actual export logic
        return $count;
    }
}
