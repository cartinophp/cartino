<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Resources\AssetResource;
use Cartino\Models\Asset;
use Cartino\Services\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function __construct(
        protected AssetService $assetService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Asset::query()->with(['containerModel', 'uploadedBy']);

        // Filter by container
        if ($request->has('container')) {
            $query->inContainer($request->container);
        }

        // Filter by folder
        if ($request->has('folder')) {
            $folder = $request->folder === '/' ? '' : $request->folder;
            $query->inFolder($folder);
        }

        // Filter by type
        if ($request->has('type')) {
            match ($request->type) {
                'image' => $query->images(),
                'video' => $query->videos(),
                'document' => $query->documents(),
                default => null,
            };
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->get('per_page', 50);
        $assets = $query->paginate($perPage);

        return response()->json([
            'data' => AssetResource::collection($assets),
            'meta' => [
                'current_page' => $assets->currentPage(),
                'last_page' => $assets->lastPage(),
                'per_page' => $assets->perPage(),
                'total' => $assets->total(),
            ],
        ]);
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['containerModel', 'uploadedBy', 'transformations']);

        return response()->json([
            'data' => new AssetResource($asset),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
            'container' => 'required|string|exists:asset_containers,handle',
            'folder' => 'nullable|string',
        ]);

        try {
            $asset = $this->assetService->upload(
                $request->file('file'),
                $request->container,
                $request->folder,
                $request->user()?->id,
            );

            return response()->json([
                'data' => new AssetResource($asset),
                'message' => 'File uploaded successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed: '.$e->getMessage(),
            ], 422);
        }
    }

    public function uploadMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file',
            'container' => 'required|string|exists:asset_containers,handle',
            'folder' => 'nullable|string',
        ]);

        $uploadedAssets = [];
        $errors = [];

        foreach ($request->file('files') as $index => $file) {
            try {
                $asset = $this->assetService->upload(
                    $file,
                    $request->container,
                    $request->folder,
                    $request->user()?->id,
                );
                $uploadedAssets[] = new AssetResource($asset);
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'data' => $uploadedAssets,
            'errors' => $errors,
            'message' => count($uploadedAssets).' files uploaded successfully',
        ], 201);
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'meta' => 'sometimes|array',
            'meta.alt' => 'sometimes|string|max:255',
            'meta.title' => 'sometimes|string|max:255',
            'meta.caption' => 'sometimes|string',
            'meta.description' => 'sometimes|string',
            'data' => 'sometimes|array',
            'focus_point' => 'sometimes|array',
            'focus_point.x' => 'required_with:focus_point|integer|min:0|max:100',
            'focus_point.y' => 'required_with:focus_point|integer|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('meta')) {
                $this->assetService->updateMeta($asset, $request->meta);
            }

            if ($request->has('data')) {
                $asset->update(['data' => array_merge($asset->data ?? [], $request->data)]);
            }

            if ($request->has('focus_point')) {
                $this->assetService->setFocusPoint(
                    $asset,
                    $request->input('focus_point.x'),
                    $request->input('focus_point.y'),
                );
            }

            DB::commit();

            return response()->json([
                'data' => new AssetResource($asset->fresh()),
                'message' => 'Asset updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed: '.$e->getMessage(),
            ], 422);
        }
    }

    public function move(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'folder' => 'required|string',
        ]);

        try {
            $updatedAsset = $this->assetService->move($asset, $request->folder);

            return response()->json([
                'data' => new AssetResource($updatedAsset),
                'message' => 'Asset moved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Move failed: '.$e->getMessage(),
            ], 422);
        }
    }

    public function rename(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string|max:255',
        ]);

        try {
            $updatedAsset = $this->assetService->rename($asset, $request->filename);

            return response()->json([
                'data' => new AssetResource($updatedAsset),
                'message' => 'Asset renamed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Rename failed: '.$e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Asset $asset): JsonResponse
    {
        try {
            $this->assetService->delete($asset);

            return response()->json([
                'message' => 'Asset deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Delete failed: '.$e->getMessage(),
            ], 422);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:assets,id',
        ]);

        $deleted = 0;
        $errors = [];

        foreach ($request->ids as $id) {
            try {
                $asset = Asset::findOrFail($id);
                $this->assetService->delete($asset);
                $deleted++;
            } catch (\Exception $e) {
                $errors[] = [
                    'id' => $id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => "{$deleted} assets deleted successfully",
            'deleted' => $deleted,
            'errors' => $errors,
        ]);
    }

    public function download(Asset $asset)
    {
        if (! $asset->exists()) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return $asset->disk()->download($asset->path, $asset->basename);
    }
}
