<?php

namespace Cartino\Http\Controllers\Api\Admin;

use Cartino\Http\Controllers\Api\ApiController;
use Cartino\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductTypeController extends ApiController
{
    /**
     * Display a listing of product types with filtering
     */
    public function index(Request $request): JsonResponse
    {
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

        // Status filter
        if ($request->has('is_enabled')) {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = [
            'name',
            'slug',
            'products_count',
            'is_enabled',
            'created_at',
            'updated_at',
        ];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $productTypes = $query->paginate($perPage);

        return $this->successResponse([
            'data' => $productTypes->items(),
            'meta' => [
                'current_page' => $productTypes->currentPage(),
                'per_page' => $productTypes->perPage(),
                'total' => $productTypes->total(),
                'last_page' => $productTypes->lastPage(),
            ],
            'links' => [
                'first' => $productTypes->url(1),
                'last' => $productTypes->url($productTypes->lastPage()),
                'prev' => $productTypes->previousPageUrl(),
                'next' => $productTypes->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created product type
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_types,name',
            'slug' => 'nullable|string|max:255|unique:product_types,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'slug' => $request->slug ?: Str::slug($request->name),
                'description' => $request->description,
            ];

            // Ensure unique slug
            $originalSlug = $data['slug'];
            $counter = 1;
            while (ProductType::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug.'-'.$counter;
                $counter++;
            }

            $productType = ProductType::create($data);

            DB::commit();

            return $this->successResponse([
                'message' => 'Product type created successfully',
                'data' => $productType->loadCount('products'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create product type: '.$e->getMessage(), 500);
        }
    }

    /**
     * Display the specified product type
     */
    public function show(ProductType $productType): JsonResponse
    {
        $productType->loadCount('products');

        return $this->successResponse([
            'data' => $productType,
        ]);
    }

    /**
     * Update the specified product type
     */
    public function update(Request $request, ProductType $productType): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:product_types,name,'.$productType->id,
            'slug' => 'sometimes|string|max:255|unique:product_types,slug,'.$productType->id,
            'description' => 'nullable|string|max:1000',
            'is_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $updateData = $request->only(['name', 'slug', 'description', 'is_enabled']);

            // Generate slug if name changed but slug not provided
            if (isset($updateData['name']) && ! isset($updateData['slug'])) {
                $updateData['slug'] = Str::slug($updateData['name']);
            }

            // Ensure unique slug if changed
            if (isset($updateData['slug']) && $updateData['slug'] !== $productType->slug) {
                $originalSlug = $updateData['slug'];
                $counter = 1;
                while (ProductType::where('slug', $updateData['slug'])->where('id', '!=', $productType->id)->exists()) {
                    $updateData['slug'] = $originalSlug.'-'.$counter;
                    $counter++;
                }
            }

            $productType->update($updateData);

            DB::commit();

            $productType->loadCount('products');

            return $this->successResponse([
                'message' => 'Product type updated successfully',
                'data' => $productType,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to update product type: '.$e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified product type
     */
    public function destroy(ProductType $productType): JsonResponse
    {
        try {
            // Check if product type has products
            $productsCount = $productType->products()->count();

            if ($productsCount > 0) {
                return $this->errorResponse(
                    "Cannot delete product type '{$productType->name}' because it has {$productsCount} associated products. Please reassign or delete the products first.",
                    422,
                );
            }

            $productType->delete();

            return $this->successResponse([
                'message' => 'Product type deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete product type: '.$e->getMessage(), 500);
        }
    }

    /**
     * Toggle the enabled status of a product type
     */
    public function toggle(ProductType $productType): JsonResponse
    {
        try {
            $productType->update(['is_enabled' => ! $productType->is_enabled]);

            $status = $productType->is_enabled ? 'enabled' : 'disabled';

            return $this->successResponse([
                'message' => "Product type {$status} successfully",
                'data' => $productType->loadCount('products'),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to toggle product type status: '.$e->getMessage(), 500);
        }
    }

    /**
     * Bulk operations for product types
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:enable,disable,delete',
            'product_type_ids' => 'required|array|min:1',
            'product_type_ids.*' => 'integer|exists:product_types,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $productTypes = ProductType::whereIn('id', $request->product_type_ids);
            $action = $request->action;

            switch ($action) {
                case 'enable':
                    $updated = $productTypes->update(['is_enabled' => true]);
                    $message = "{$updated} product types enabled successfully";
                    break;

                case 'disable':
                    $updated = $productTypes->update(['is_enabled' => false]);
                    $message = "{$updated} product types disabled successfully";
                    break;

                case 'delete':
                    // Check if any product types have associated products
                    $typesWithProducts = ProductType::whereIn('id', $request->product_type_ids)
                        ->withCount('products')
                        ->having('products_count', '>', 0)
                        ->get(['id', 'name', 'products_count']);

                    if ($typesWithProducts->isNotEmpty()) {
                        $names = $typesWithProducts->pluck('name')->join(', ');

                        return $this->errorResponse(
                            "Cannot delete product types ({$names}) because they have associated products. Please reassign or delete the products first.",
                            422,
                        );
                    }

                    $deleted = $productTypes->delete();
                    $message = "{$deleted} product types deleted successfully";
                    break;
            }

            DB::commit();

            return $this->successResponse([
                'message' => $message,
                'affected_count' => $updated ?? $deleted ?? 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Bulk operation failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get product types for select/dropdown usage
     */
    public function options(Request $request): JsonResponse
    {
        $query = ProductType::select('id', 'name', 'slug');

        $productTypes = $query->orderBy('name')->get();

        return $this->successResponse([
            'data' => $productTypes,
        ]);
    }

    /**
     * Get product type statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalTypes = ProductType::count();
            $enabledTypes = ProductType::where('is_enabled', true)->count();
            $disabledTypes = ProductType::where('is_enabled', false)->count();
            $typesWithProducts = ProductType::whereHas('products')->count();
            $emptyTypes = ProductType::doesntHave('products')->count();

            // Most used product types
            $topTypes = ProductType::withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'products_count']);

            return $this->successResponse([
                'data' => [
                    'total_types' => $totalTypes,
                    'enabled_types' => $enabledTypes,
                    'disabled_types' => $disabledTypes,
                    'types_with_products' => $typesWithProducts,
                    'empty_types' => $emptyTypes,
                    'top_types' => $topTypes,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to load statistics: '.$e->getMessage(), 500);
        }
    }
}
