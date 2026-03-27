<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\DTO\ProductDto;
use Cartino\Http\Controllers\Controller;
use Cartino\Http\Resources\ProductResource;
use Cartino\Models\Brand;
use Cartino\Models\Category;
use Cartino\Models\Product;
use Cartino\Repositories\ProductRepository;
use Cartino\Schema\SchemaRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductsController extends Controller
{
    public function __construct(
        protected SchemaRepository $schemas,
        protected readonly ProductRepository $repository,
    ) {}

    /**
     * Products index page with DataTable
     */
    public function index(Request $request)
    {
        $request = $request->all();

        $data = $this->repository->findAll($request);

        $page = Page::make(__('products.title'))
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('products.title'))
            ->primaryAction(__('products.create'), '/cp/products/create')
            ->secondaryActions([
                ['label' => __('admin.actions.import'), 'url' => '/cp/products/import'],
                ['label' => __('admin.actions.export'), 'url' => '/cp/products/export'],
            ]);

        return Inertia::render('products/index', [
            'page' => $page->compile(),
            'data' => $data,
            'actions' => [],
        ]);
    }

    /**
     * Create product page
     */
    public function create()
    {
        // Load schema from JSON file
        $schema = $this->schemas->getCollection('products');

        if (! $schema) {
            abort(404, 'Product schema not found');
        }

        $page = Page::make(__('products.create'))
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('products.title'), '/cp/products')
            ->breadcrumb(__('products.create'))
            ->primaryAction(__('admin.actions.save').' '.strtolower(__('products.single')), null, [
                'form' => 'product-form',
            ])
            ->secondaryActions([
                [
                    'label' => __('admin.actions.save').' & '.__('admin.actions.continue_editing'),
                    'action' => 'save_continue',
                ],
                [
                    'label' => __('admin.actions.save').' & '.__('admin.actions.add_another'),
                    'action' => 'save_add_another',
                ],
            ]);

        // Product form tabs
        $page->tabs([
            'general' => ['label' => __('products.tabs.general'), 'component' => 'ProductGeneralForm'],
            'inventory' => ['label' => __('products.tabs.inventory'), 'component' => 'ProductInventoryForm'],
            'shipping' => ['label' => __('products.tabs.shipping'), 'component' => 'ProductShippingForm'],
            'seo' => ['label' => __('products.tabs.seo'), 'component' => 'ProductSeoForm'],
            'variants' => ['label' => __('products.tabs.variants'), 'component' => 'ProductVariantsForm'],
        ]);

        return Inertia::render('products/Create', [
            'page' => $page->compile(),
            'schema' => $schema->toArray(),
            'categories' => Category::select('id', 'name')->get(),
            'brands' => Brand::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store product using DTO
     */
    public function store(Request $request)
    {
        // Load schema and build validation rules
        $schema = $this->schemas->getCollection('products');
        $validationRules = $this->buildValidationRules($schema);

        $validated = $request->validate($validationRules);

        // Create DTO from validated data
        $productDto = ProductDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $productDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Create product from DTO
        $product = Product::create($productDto->toArray());

        // Handle different save actions
        $action = $request->input('_action', 'save');

        return match ($action) {
            'save_continue' => response()->json([
                'message' => __('products.messages.created'),
                'redirect' => "/cp/products/{$product->id}/edit",
            ]),
            'save_add_another' => response()->json([
                'message' => __('products.messages.created'),
                'redirect' => '/cp/products/create',
            ]),
            default => response()->json([
                'message' => __('products.messages.created'),
                'redirect' => '/cp/products',
            ]),
        };
    }

    /**
     * Show single product
     */
    public function show(int $id)
    {
        $data = $this->repository->findById($id);

        $page = Page::make($data->name)
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('products.title'), '/cp/products')
            ->breadcrumb($data->name)
            ->primaryAction(__('products.edit'), "/cp/products/{$data->id}/edit")
            ->secondaryActions([
                ['label' => __('admin.actions.duplicate'), 'action' => 'duplicate'],
                [
                    'label' => __('admin.actions.view').' '.__('admin.navigation.storefront'),
                    'url' => "/products/{$data->slug}",
                    'target' => '_blank',
                ],
                ['label' => __('admin.actions.delete'), 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('products/[id]/index', [
            'page' => $page->compile(),
            'product' => new ProductResource($data),
        ]);
    }

    /**
     * Edit product page
     */
    public function edit(Product $product)
    {
        // Load schema from JSON file
        $schema = $this->schemas->getCollection('products');

        if (! $schema) {
            abort(404, 'Product schema not found');
        }

        $product->load(['category', 'brand', 'variants', 'media']);

        $page = Page::make(__('products.edit')." {$product->name}")
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('products.title'), '/cp/products')
            ->breadcrumb($product->name, "/cp/products/{$product->id}")
            ->breadcrumb(__('admin.actions.edit'))
            ->primaryAction(__('admin.actions.update').' '.strtolower(__('products.single')), null, [
                'form' => 'product-form',
            ])
            ->secondaryActions([
                [
                    'label' => __('admin.actions.view').' '.strtolower(__('products.single')),
                    'url' => "/cp/products/{$product->id}",
                ],
                [
                    'label' => __('admin.actions.view').' '.__('admin.navigation.storefront'),
                    'url' => "/products/{$product->handle}",
                    'target' => '_blank',
                ],
                ['label' => __('admin.actions.duplicate'), 'action' => 'duplicate'],
                ['label' => __('admin.actions.delete'), 'action' => 'delete', 'destructive' => true],
            ]);

        // Product form tabs
        $page->tabs([
            'general' => ['label' => __('products.tabs.general'), 'component' => 'ProductGeneralForm'],
            'inventory' => ['label' => __('products.tabs.inventory'), 'component' => 'ProductInventoryForm'],
            'shipping' => ['label' => __('products.tabs.shipping'), 'component' => 'ProductShippingForm'],
            'seo' => ['label' => __('products.tabs.seo'), 'component' => 'ProductSeoForm'],
            'variants' => ['label' => __('products.tabs.variants'), 'component' => 'ProductVariantsForm'],
        ]);

        return Inertia::render('products/Edit', [
            'page' => $page->compile(),
            'schema' => $schema->toArray(),
            'product' => new ProductResource($product),
            'categories' => Category::select('id', 'name')->get(),
            'brands' => Brand::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update product using DTO
     */
    public function update(Request $request, Product $product)
    {
        // Load schema and build validation rules
        $schema = $this->schemas->getCollection('products');
        $validationRules = $this->buildValidationRules($schema);

        // Make handle unique validation exclude current product
        if (isset($validationRules['handle'])) {
            $validationRules['handle'][] = 'unique:products,handle,'.$product->id;
        }

        $validated = $request->validate($validationRules);

        // Create DTO from validated data
        $productDto = ProductDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $productDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Update product from DTO
        $product->update($productDto->toArray());

        return response()->json([
            'message' => __('products.messages.updated'),
            'product' => new ProductResource($product->fresh(['category', 'brand'])),
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => __('products.messages.deleted'),
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
            return response()->json(['error' => __('admin.messages.no_items_selected')], 422);
        }

        $products = Product::whereIn('id', $ids);

        return match ($action) {
            'activate' => $this->bulkActivate($products),
            'draft' => $this->bulkDraft($products),
            'archive' => $this->bulkArchive($products),
            'delete' => $this->bulkDelete($products),
            'export' => $this->bulkExport($products),
            default => response()->json(['error' => 'Unknown action'], 422),
        };
    }

    /**
     * Build validation rules from schema
     */
    protected function buildValidationRules($schema): array
    {
        if (! $schema || ! isset($schema['fields'])) {
            return [
                'name' => 'required|string|max:255',
                'handle' => 'required|string|max:255|unique:products',
                'price' => 'required|numeric|min:0',
                'status' => 'required|in:active,draft,archived',
            ];
        }

        $rules = [];

        foreach ($schema['fields'] as $field => $config) {
            if (! empty($config['validate'])) {
                $rules[$field] = is_array($config['validate'])
                    ? $config['validate']
                    : explode('|', $config['validate']);
            } elseif ($config['required'] ?? false) {
                $rules[$field] = ['required'];
            }
        }

        return $rules;
    }

    /**
     * Bulk activate products
     */
    protected function bulkActivate($products)
    {
        $count = $products->update(['status' => 'active']);

        return response()->json(['message' => __('products.messages.bulk_activated', ['count' => $count])]);
    }

    /**
     * Bulk set as draft
     */
    protected function bulkDraft($products)
    {
        $count = $products->update(['status' => 'draft']);

        return response()->json(['message' => "Set {$count} products as draft"]);
    }

    /**
     * Bulk archive products
     */
    protected function bulkArchive($products)
    {
        $count = $products->update(['status' => 'archived']);

        return response()->json(['message' => __('products.messages.bulk_archived', ['count' => $count])]);
    }

    /**
     * Bulk delete products
     */
    protected function bulkDelete($products)
    {
        $count = $products->count();
        $products->delete();

        return response()->json(['message' => __('products.messages.bulk_deleted', ['count' => $count])]);
    }

    /**
     * Bulk export products
     */
    protected function bulkExport($products)
    {
        // TODO: Implement export logic
        $count = $products->count();

        return response()->json([
            'message' => __('products.messages.bulk_exported', ['count' => $count]),
            'download_url' => '/cp/products/export/download/'.uniqid(),
        ]);
    }
}
