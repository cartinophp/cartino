<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Resources\AssetContainerResource;
use Cartino\Models\AssetContainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AssetContainerController extends Controller
{
    public function index(): JsonResponse
    {
        $containers = AssetContainer::with(['assets' => fn ($q) => $q->limit(5)])->withCount('assets')->get();

        return response()->json([
            'data' => AssetContainerResource::collection($containers),
        ]);
    }

    public function show(AssetContainer $assetContainer): JsonResponse
    {
        $assetContainer->loadCount('assets');

        return response()->json([
            'data' => new AssetContainerResource($assetContainer),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'handle' => 'required|string|unique:asset_containers,handle|alpha_dash',
            'title' => 'required|string|max:255',
            'disk' => 'required|string',
            'allow_uploads' => 'boolean',
            'allow_downloading' => 'boolean',
            'allow_renaming' => 'boolean',
            'allow_moving' => 'boolean',
            'allowed_extensions' => 'nullable|array',
            'max_file_size' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
            'glide_presets' => 'nullable|array',
        ]);

        $container = AssetContainer::create($request->all());

        return response()->json([
            'data' => new AssetContainerResource($container),
            'message' => 'Container created successfully',
        ], 201);
    }

    public function update(Request $request, AssetContainer $assetContainer): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'disk' => 'sometimes|string',
            'allow_uploads' => 'boolean',
            'allow_downloading' => 'boolean',
            'allow_renaming' => 'boolean',
            'allow_moving' => 'boolean',
            'allowed_extensions' => 'nullable|array',
            'max_file_size' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
            'glide_presets' => 'nullable|array',
        ]);

        $assetContainer->update($request->all());

        return response()->json([
            'data' => new AssetContainerResource($assetContainer->fresh()),
            'message' => 'Container updated successfully',
        ]);
    }

    public function destroy(AssetContainer $assetContainer): JsonResponse
    {
        if ($assetContainer->assets()->exists()) {
            return response()->json([
                'message' => 'Cannot delete container with assets. Delete all assets first.',
            ], 422);
        }

        $assetContainer->delete();

        return response()->json([
            'message' => 'Container deleted successfully',
        ]);
    }
}
