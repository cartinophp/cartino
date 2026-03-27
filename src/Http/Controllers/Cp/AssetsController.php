<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Models\Asset;
use Cartino\Models\AssetContainer;
use Cartino\Models\AssetFolder;
use Cartino\Services\AssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AssetsController extends BaseController
{
    public function __construct(
        protected AssetService $assetService,
    ) {}

    /**
     * Display the asset browser.
     */
    public function index(Request $request): Response
    {
        $this->addBreadcrumb('Assets');

        $page = Page::make('Assets')
            ->primaryAction('Upload', route('cp.assets.create'))
            ->secondaryActions([
                ['label' => 'Manage containers', 'url' => route('cp.asset-containers.index')],
                ['label' => 'Settings', 'url' => route('cp.settings.media')],
            ]);

        // Get all containers
        $containers = AssetContainer::withCount('assets')->get();

        // Current container filter
        $container = $request->get('container', $containers->first()?->handle ?? 'assets');

        // Build query
        $query = Asset::query()->with(['containerModel', 'uploadedBy'])->inContainer($container);

        // Folder filter
        if ($request->filled('folder')) {
            $query->inFolder($request->folder);
        }

        // Type filter
        if ($request->filled('type')) {
            match ($request->type) {
                'image' => $query->images(),
                'video' => $query->videos(),
                'document' => $query->documents(),
                default => null,
            };
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Paginate
        $perPage = $request->get('per_page', 24);
        $assets = $query->paginate($perPage)->withQueryString();

        // Get folders for current container
        $folders = AssetFolder::where('container', $container)->orderBy('path')->get();

        return $this->inertiaResponse('assets/index', [
            'page' => $page->compile(),
            'containers' => $containers,
            'currentContainer' => $container,
            'folders' => $folders,
            'assets' => $assets,
            'filters' => $request->only(['container', 'folder', 'type', 'search', 'sort_by', 'sort_dir', 'per_page']),
            'stats' => [
                'total' => Asset::inContainer($container)->count(),
                'images' => Asset::inContainer($container)->images()->count(),
                'videos' => Asset::inContainer($container)->videos()->count(),
                'documents' => Asset::inContainer($container)->documents()->count(),
                'total_size' => Asset::inContainer($container)->sum('size'),
            ],
        ]);
    }

    /**
     * Show the asset upload form.
     */
    public function create(Request $request): Response
    {
        $this->addBreadcrumb('Assets', route('cp.assets.index'));
        $this->addBreadcrumb('Upload');

        $page = Page::make('Upload Assets');

        $containers = AssetContainer::where('allow_uploads', true)->get();

        return $this->inertiaResponse('assets/Create', [
            'page' => $page->compile(),
            'containers' => $containers,
            'selectedContainer' => $request->get('container', 'assets'),
            'selectedFolder' => $request->get('folder'),
        ]);
    }

    /**
     * Store uploaded assets.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:10240', // 10MB
            'container' => 'required|string|exists:asset_containers,handle',
            'folder' => 'nullable|string',
        ]);

        $uploadedAssets = [];
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                $asset = $this->assetService->upload(
                    file: $file,
                    container: $request->container,
                    folder: $request->folder,
                    userId: auth()->id(),
                );
                $uploadedAssets[] = $asset;
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (count($errors) > 0) {
            $this->flashWarning(
                __('flash.assets.upload_partial', [
                    'uploaded' => count($uploadedAssets),
                    'failed' => count($errors),
                ]),
                [],
                ['uploadErrors' => $errors],
            );

            return redirect()->back();
        }

        $this->flashSuccess(__('flash.assets.upload_success', ['count' => count($uploadedAssets)]));

        return redirect()->route('cp.assets.index', ['container' => $request->container]);
    }

    /**
     * Show single asset details.
     */
    public function show(Asset $asset): Response
    {
        $this->addBreadcrumb('Assets', route('cp.assets.index'));
        $this->addBreadcrumb($asset->filename);

        $page = Page::make($asset->filename)
            ->primaryAction('Download', route('cp.assets.download', $asset))
            ->secondaryActions([
                ['label' => 'Delete', 'url' => route('cp.assets.destroy', $asset), 'destructive' => true],
            ]);

        $asset->load(['containerModel', 'uploadedBy', 'transformations']);

        // Get asset transformations
        $transformations = $asset
            ->transformations()
            ->orderBy('access_count', 'desc')
            ->get()
            ->map(function ($transformation) {
                return [
                    'id' => $transformation->id,
                    'preset' => $transformation->preset,
                    'params' => $transformation->params,
                    'size' => $transformation->size,
                    'width' => $transformation->width,
                    'height' => $transformation->height,
                    'access_count' => $transformation->access_count,
                    'last_accessed_at' => $transformation->last_accessed_at,
                ];
            });

        // Get models using this asset
        $usedBy = \DB::table('assetables')
            ->where('asset_id', $asset->id)
            ->get()
            ->map(function ($relation) {
                $modelClass = $relation->assetable_type;
                $model = $modelClass::find($relation->assetable_id);

                return [
                    'type' => class_basename($modelClass),
                    'id' => $relation->assetable_id,
                    'name' => $model?->name ?? $model?->title ?? 'Unknown',
                    'collection' => $relation->collection,
                    'is_primary' => $relation->is_primary,
                    'url' => $this->getModelUrl($modelClass, $relation->assetable_id),
                ];
            });

        return $this->inertiaResponse('assets/Show', [
            'page' => $page->compile(),
            'asset' => $asset,
            'transformations' => $transformations,
            'usedBy' => $usedBy,
            'presets' => config('media.presets'),
        ]);
    }

    /**
     * Show edit asset form.
     */
    public function edit(Asset $asset): Response
    {
        $this->addBreadcrumb('Assets', route('cp.assets.index'));
        $this->addBreadcrumb($asset->filename, route('cp.assets.show', $asset));
        $this->addBreadcrumb('Edit');

        $page = Page::make('Edit Asset');

        return $this->inertiaResponse('assets/Edit', [
            'page' => $page->compile(),
            'asset' => $asset,
            'containers' => AssetContainer::all(),
            'folders' => AssetFolder::where('container', $asset->container)->get(),
        ]);
    }

    /**
     * Update asset metadata.
     */
    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $request->validate([
            'meta.alt' => 'nullable|string|max:255',
            'meta.title' => 'nullable|string|max:255',
            'meta.caption' => 'nullable|string',
            'meta.description' => 'nullable|string',
            'focus_point.x' => 'nullable|integer|min:0|max:100',
            'focus_point.y' => 'nullable|integer|min:0|max:100',
            'folder' => 'nullable|string',
        ]);

        // Update metadata
        if ($request->has('meta')) {
            $this->assetService->updateMeta($asset, $request->meta);
        }

        // Update focus point
        if ($request->has('focus_point.x') && $request->has('focus_point.y')) {
            $this->assetService->setFocusPoint(
                $asset,
                $request->input('focus_point.x'),
                $request->input('focus_point.y'),
            );
        }

        // Move to different folder
        if ($request->has('folder') && $request->folder !== $asset->folder) {
            $this->assetService->move($asset, $request->folder);
        }

        $this->flashSuccess(__('flash.assets.updated'));

        return redirect()->route('cp.assets.show', $asset);
    }

    /**
     * Delete asset.
     */
    public function destroy(Asset $asset): RedirectResponse
    {
        $container = $asset->container;

        $this->assetService->delete($asset);

        $this->flashSuccess(__('flash.assets.deleted'));

        return redirect()->route('cp.assets.index', ['container' => $container]);
    }

    /**
     * Bulk delete assets.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:assets,id',
        ]);

        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                $asset = Asset::findOrFail($id);
                $this->assetService->delete($asset);
                $deleted++;
            } catch (\Exception $e) {
                // Continue deleting others
            }
        }

        $this->flashSuccess(__('flash.assets.bulk_deleted', ['count' => $deleted]));

        return redirect()->back();
    }

    /**
     * Download asset.
     */
    public function download(Asset $asset)
    {
        if (! $asset->exists()) {
            abort(404, 'File not found');
        }

        return $asset->disk()->download($asset->path, $asset->basename);
    }

    /**
     * Get model URL helper.
     */
    protected function getModelUrl(string $modelClass, int $modelId): ?string
    {
        $map = [
            'Cartino\Models\Product' => 'cartino.products.show',
            'Cartino\Models\Category' => 'cartino.categories.show',
            'Cartino\Models\Brand' => 'cartino.brands.show',
        ];

        $routeName = $map[$modelClass] ?? null;

        return $routeName ? route($routeName, $modelId) : null;
    }
}
