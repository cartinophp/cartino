<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Resources\AssetResource;
use Cartino\Models\Asset;
use Cartino\Models\Brand;
use Cartino\Models\Category;
use Cartino\Models\Collection;
use Cartino\Models\Page;
use Cartino\Models\Post;
use Cartino\Models\Product;
use Cartino\Traits\HasAssets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Handles polymorphic asset relationships
 * Works with any model that uses HasAssets trait
 */
class AssetableController extends Controller
{
    /**
     * List all assets for a model
     *
     * GET /api/products/{id}/assets?collection=images
     */
    public function index(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $collection = $request->get('collection', 'images');

        $assets = $model->getAssets($collection);

        return response()->json([
            'data' => AssetResource::collection($assets),
            'meta' => [
                'collection' => $collection,
                'total' => $assets->count(),
                'primary' => $model->getPrimaryAsset($collection)?->id,
            ],
        ]);
    }

    /**
     * Attach asset to model
     *
     * POST /api/products/{id}/assets
     * {
     *   "asset_id": 123,
     *   "collection": "images",
     *   "is_primary": true,
     *   "sort_order": 0,
     *   "meta": {"alt": "Custom alt text"}
     * }
     */
    public function attach(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $validated = $request->validate([
            'asset_id' => 'required|integer|exists:assets,id',
            'collection' => 'sometimes|string',
            'is_primary' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
            'meta' => 'sometimes|array',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);
        $collection = $validated['collection'] ?? 'images';

        try {
            DB::beginTransaction();

            // Build attributes
            $attributes = [];
            if (isset($validated['is_primary'])) {
                $attributes['is_primary'] = $validated['is_primary'];
            }
            if (isset($validated['is_featured'])) {
                $attributes['is_featured'] = $validated['is_featured'];
            }
            if (isset($validated['sort_order'])) {
                $attributes['sort_order'] = $validated['sort_order'];
            }
            if (isset($validated['meta'])) {
                $attributes['meta'] = $validated['meta'];
            }

            // If set as primary, remove primary from others
            if ($validated['is_primary'] ?? false) {
                $model->assets()->wherePivot('collection', $collection)->update(['assetables.is_primary' => false]);
            }

            $model->attachAsset($asset, $collection, $attributes);

            DB::commit();

            return response()->json([
                'data' => new AssetResource($asset->fresh()),
                'message' => 'Asset attached successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to attach asset: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Attach multiple assets at once
     *
     * POST /api/products/{id}/assets/bulk
     * {
     *   "asset_ids": [1, 2, 3, 4, 5],
     *   "collection": "gallery"
     * }
     */
    public function attachBulk(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $validated = $request->validate([
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'required|integer|exists:assets,id',
            'collection' => 'sometimes|string',
        ]);

        $collection = $validated['collection'] ?? 'images';

        try {
            DB::beginTransaction();

            $model->attachAssets($validated['asset_ids'], $collection);

            DB::commit();

            $assets = $model->getAssets($collection);

            return response()->json([
                'data' => AssetResource::collection($assets),
                'message' => count($validated['asset_ids']).' assets attached successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to attach assets: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update asset pivot data
     *
     * PATCH /api/products/{id}/assets/{assetId}
     * {
     *   "sort_order": 1,
     *   "is_primary": false,
     *   "meta": {"alt": "Updated alt"}
     * }
     */
    public function update(Request $request, string $modelType, int $modelId, int $assetId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $validated = $request->validate([
            'collection' => 'sometimes|string',
            'is_primary' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
            'meta' => 'sometimes|array',
        ]);

        $collection = $validated['collection'] ?? 'images';

        try {
            DB::beginTransaction();

            // Check if asset is attached
            $attached = $model->assets()->wherePivot('collection', $collection)->where('asset_id', $assetId)->exists();

            if (! $attached) {
                return response()->json([
                    'message' => 'Asset not attached to this model in collection '.$collection,
                ], 404);
            }

            // Handle primary flag
            if (isset($validated['is_primary']) && $validated['is_primary']) {
                $model->setPrimaryAsset($assetId, $collection);
                unset($validated['is_primary']);
            }

            // Handle meta update
            if (isset($validated['meta'])) {
                $model->updateAssetMeta($assetId, $validated['meta'], $collection);
                unset($validated['meta']);
            }

            // Update other pivot attributes
            if (! empty($validated)) {
                $model->assets()->wherePivot('collection', $collection)->updateExistingPivot($assetId, $validated);
            }

            DB::commit();

            $asset = Asset::find($assetId);

            return response()->json([
                'data' => new AssetResource($asset),
                'message' => 'Asset updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update asset: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Detach asset from model
     *
     * DELETE /api/products/{id}/assets/{assetId}?collection=images
     */
    public function detach(Request $request, string $modelType, int $modelId, int $assetId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $collection = $request->get('collection');

        try {
            $model->detachAsset($assetId, $collection);

            return response()->json([
                'message' => 'Asset detached successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to detach asset: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Detach all assets from collection
     *
     * DELETE /api/products/{id}/assets?collection=images
     */
    public function detachAll(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $collection = $request->get('collection');

        try {
            $model->detachAllAssets($collection);

            return response()->json([
                'message' => 'All assets detached successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to detach assets: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Set primary asset
     *
     * POST /api/products/{id}/assets/{assetId}/set-primary?collection=images
     */
    public function setPrimary(Request $request, string $modelType, int $modelId, int $assetId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $collection = $request->get('collection', 'images');

        try {
            $model->setPrimaryAsset($assetId, $collection);

            return response()->json([
                'message' => 'Primary asset set successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to set primary asset: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reorder assets in collection
     *
     * POST /api/products/{id}/assets/reorder
     * {
     *   "collection": "images",
     *   "order": [
     *     {"asset_id": 1, "sort_order": 0},
     *     {"asset_id": 2, "sort_order": 1},
     *     {"asset_id": 3, "sort_order": 2}
     *   ]
     * }
     */
    public function reorder(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $validated = $request->validate([
            'collection' => 'sometimes|string',
            'order' => 'required|array',
            'order.*.asset_id' => 'required|integer|exists:assets,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        $collection = $validated['collection'] ?? 'images';

        try {
            DB::beginTransaction();

            $orderMap = [];
            foreach ($validated['order'] as $item) {
                $orderMap[$item['asset_id']] = $item['sort_order'];
            }

            $model->reorderAssets($collection, $orderMap);

            DB::commit();

            return response()->json([
                'message' => 'Assets reordered successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to reorder assets: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Sync assets (replace all in collection)
     *
     * PUT /api/products/{id}/assets/sync
     * {
     *   "collection": "images",
     *   "asset_ids": [1, 2, 3]
     * }
     */
    public function sync(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $model = $this->findModel($modelType, $modelId);

        $validated = $request->validate([
            'collection' => 'sometimes|string',
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'required|integer|exists:assets,id',
        ]);

        $collection = $validated['collection'] ?? 'images';

        try {
            DB::beginTransaction();

            $model->syncAssets($validated['asset_ids'], $collection);

            DB::commit();

            $assets = $model->getAssets($collection);

            return response()->json([
                'data' => AssetResource::collection($assets),
                'message' => 'Assets synced successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to sync assets: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Find model by type and ID
     */
    protected function findModel(string $modelType, int $modelId): Model
    {
        $modelClass = $this->resolveModelClass($modelType);

        $model = $modelClass::find($modelId);

        if (! $model) {
            throw ValidationException::withMessages([
                'model' => ['Model not found'],
            ]);
        }

        if (! in_array(HasAssets::class, class_uses_recursive($model))) {
            throw ValidationException::withMessages([
                'model' => ['Model does not support assets'],
            ]);
        }

        return $model;
    }

    /**
     * Resolve model class from type
     */
    protected function resolveModelClass(string $modelType): string
    {
        $modelMap = [
            'products' => Product::class,
            'categories' => Category::class,
            'brands' => Brand::class,
            'collections' => Collection::class,
            'pages' => Page::class,
            'posts' => Post::class,
        ];

        if (! isset($modelMap[$modelType])) {
            throw ValidationException::withMessages([
                'model_type' => ['Invalid model type: '.$modelType],
            ]);
        }

        return $modelMap[$modelType];
    }
}
