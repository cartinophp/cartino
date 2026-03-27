<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api\Admin;

use Cartino\Http\Requests\Admin\SiteRequest;
use Cartino\Http\Resources\SiteResource;
use Cartino\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class SiteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Site::query()->withCount(['channels', 'catalogs']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")->orWhere(
                    'handle',
                    'like',
                    "%{$request->search}%",
                )->orWhere('domain', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('country')) {
            $query->whereJsonContains('countries', $request->country);
        }

        if ($request->filled('currency')) {
            $query->where('default_currency', $request->currency);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);

        return SiteResource::collection($query->paginate($perPage));
    }

    public function store(SiteRequest $request): JsonResponse
    {
        $site = Site::create($request->validated());

        // If marked as default, unset other defaults
        if ($site->is_default) {
            Site::where('id', '!=', $site->id)->update(['is_default' => false]);
        }

        return response()->json([
            'message' => 'Site created successfully.',
            'data' => new SiteResource($site->load(['channels', 'catalogs'])),
        ], 201);
    }

    public function show(Site $site): SiteResource
    {
        return new SiteResource($site->load(['channels', 'catalogs']));
    }

    public function update(SiteRequest $request, Site $site): JsonResponse
    {
        $site->update($request->validated());

        // If marked as default, unset other defaults
        if ($site->is_default) {
            Site::where('id', '!=', $site->id)->update(['is_default' => false]);
        }

        return response()->json([
            'message' => 'Site updated successfully.',
            'data' => new SiteResource($site->fresh(['channels', 'catalogs'])),
        ]);
    }

    public function destroy(Site $site): JsonResponse
    {
        // Prevent deleting default site
        if ($site->is_default) {
            return response()->json([
                'message' => 'Cannot delete the default site. Set another site as default first.',
            ], 422);
        }

        // Check if site has active channels
        if ($site->channels()->where('status', 'active')->exists()) {
            return response()->json([
                'message' => 'Cannot delete site with active channels. Archive or delete channels first.',
            ], 422);
        }

        $site->delete();

        return response()->json([
            'message' => 'Site deleted successfully.',
        ]);
    }

    public function setDefault(Site $site): JsonResponse
    {
        Site::query()->update(['is_default' => false]);
        $site->update(['is_default' => true]);

        return response()->json([
            'message' => 'Site set as default successfully.',
            'data' => new SiteResource($site),
        ]);
    }

    public function attachCatalog(Request $request, Site $site): JsonResponse
    {
        $validated = $request->validate([
            'catalog_id' => 'required|exists:catalogs,id',
            'priority' => 'integer|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'settings' => 'nullable|array',
        ]);

        // Check if already attached
        if ($site->catalogs()->where('catalog_id', $validated['catalog_id'])->exists()) {
            return response()->json([
                'message' => 'Catalog is already attached to this site.',
            ], 422);
        }

        $site->catalogs()->attach($validated['catalog_id'], [
            'priority' => $validated['priority'] ?? 0,
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'settings' => $validated['settings'] ?? null,
        ]);

        // If marked as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            $site->catalogs()->wherePivot('catalog_id', '!=', $validated['catalog_id'])->updateExistingPivot(
                $site->catalogs->pluck('id'),
                ['is_default' => false],
            );
        }

        return response()->json([
            'message' => 'Catalog attached successfully.',
            'data' => new SiteResource($site->fresh(['catalogs'])),
        ]);
    }

    public function detachCatalog(Site $site, int $catalogId): JsonResponse
    {
        if (! $site->catalogs()->where('catalog_id', $catalogId)->exists()) {
            return response()->json([
                'message' => 'Catalog is not attached to this site.',
            ], 404);
        }

        $site->catalogs()->detach($catalogId);

        return response()->json([
            'message' => 'Catalog detached successfully.',
        ]);
    }

    public function updateCatalogPivot(Request $request, Site $site, int $catalogId): JsonResponse
    {
        if (! $site->catalogs()->where('catalog_id', $catalogId)->exists()) {
            return response()->json([
                'message' => 'Catalog is not attached to this site.',
            ], 404);
        }

        $validated = $request->validate([
            'priority' => 'integer|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'settings' => 'nullable|array',
        ]);

        $site->catalogs()->updateExistingPivot($catalogId, $validated);

        // If marked as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            $site->catalogs()->wherePivot('catalog_id', '!=', $catalogId)->updateExistingPivot(
                $site->catalogs->pluck('id'),
                ['is_default' => false],
            );
        }

        return response()->json([
            'message' => 'Catalog settings updated successfully.',
            'data' => new SiteResource($site->fresh(['catalogs'])),
        ]);
    }
}
