<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Models\AssetContainer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AssetContainersController extends BaseController
{
    /**
     * Display asset containers list.
     */
    public function index(): Response
    {
        $this->addBreadcrumb('Asset Containers');

        $page = Page::make('Asset Containers')->primaryAction('Create Container', route('cp.asset-containers.create'));

        $containers = AssetContainer::withCount('assets')
            ->get()
            ->map(function ($container) {
                return [
                    'id' => $container->id,
                    'handle' => $container->handle,
                    'title' => $container->title,
                    'disk' => $container->disk,
                    'allow_uploads' => $container->allow_uploads,
                    'assets_count' => $container->assets_count,
                    'max_file_size' => $container->max_file_size,
                    'max_file_size_mb' => $container->max_file_size
                        ? round(($container->max_file_size / 1024) / 1024, 2)
                        : null,
                    'allowed_extensions' => $container->allowed_extensions,
                    'url' => route('cp.asset-containers.show', $container),
                ];
            });

        return $this->inertiaResponse('asset-containers/index', [
            'page' => $page->compile(),
            'containers' => $containers,
            'availableDisks' => config('filesystems.disks'),
        ]);
    }

    /**
     * Show create container form.
     */
    public function create(): Response
    {
        $this->addBreadcrumb('Asset Containers', route('cp.asset-containers.index'));
        $this->addBreadcrumb('Create');

        $page = Page::make('Create Container');

        return $this->inertiaResponse('asset-containers/Create', [
            'page' => $page->compile(),
            'availableDisks' => array_keys(config('filesystems.disks')),
            'presetExtensions' => config('media.file_types'),
        ]);
    }

    /**
     * Store new container.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'handle' => 'required|string|unique:asset_containers,handle|max:255|alpha_dash',
            'title' => 'required|string|max:255',
            'disk' => 'required|string',
            'allow_uploads' => 'boolean',
            'allow_downloading' => 'boolean',
            'allow_renaming' => 'boolean',
            'allow_moving' => 'boolean',
            'max_file_size' => 'nullable|integer|min:1',
            'allowed_extensions' => 'nullable|array',
            'allowed_extensions.*' => 'string',
        ]);

        $container = AssetContainer::create([
            'handle' => $request->handle,
            'title' => $request->title,
            'disk' => $request->disk,
            'allow_uploads' => $request->boolean('allow_uploads', true),
            'allow_downloading' => $request->boolean('allow_downloading', true),
            'allow_renaming' => $request->boolean('allow_renaming', true),
            'allow_moving' => $request->boolean('allow_moving', true),
            'max_file_size' => $request->max_file_size,
            'allowed_extensions' => $request->allowed_extensions,
        ]);

        $this->flashSuccess(__('flash.asset_containers.created'));

        return redirect()->route('cp.asset-containers.show', $container);
    }

    /**
     * Show container details.
     */
    public function show(AssetContainer $assetContainer): Response
    {
        $this->addBreadcrumb('Asset Containers', route('cp.asset-containers.index'));
        $this->addBreadcrumb($assetContainer->title);

        $page = Page::make($assetContainer->title)
            ->primaryAction('Browse Assets', route('cp.assets.index', ['container' => $assetContainer->handle]))
            ->secondaryActions([
                ['label' => 'Edit', 'url' => route('cp.asset-containers.edit', $assetContainer)],
                [
                    'label' => 'Delete',
                    'url' => route('cp.asset-containers.destroy', $assetContainer),
                    'destructive' => true,
                ],
            ]);

        $assetContainer->loadCount('assets');

        $stats = [
            'total_assets' => $assetContainer->assets()->count(),
            'total_size' => $assetContainer->assets()->sum('size'),
            'images' => $assetContainer->assets()->images()->count(),
            'videos' => $assetContainer->assets()->videos()->count(),
            'documents' => $assetContainer->assets()->documents()->count(),
        ];

        return $this->inertiaResponse('asset-containers/Show', [
            'page' => $page->compile(),
            'container' => $assetContainer,
            'stats' => $stats,
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(AssetContainer $assetContainer): Response
    {
        $this->addBreadcrumb('Asset Containers', route('cp.asset-containers.index'));
        $this->addBreadcrumb($assetContainer->title, route('cp.asset-containers.show', $assetContainer));
        $this->addBreadcrumb('Edit');

        $page = Page::make('Edit Container');

        return $this->inertiaResponse('asset-containers/Edit', [
            'page' => $page->compile(),
            'container' => $assetContainer,
            'availableDisks' => array_keys(config('filesystems.disks')),
            'presetExtensions' => config('media.file_types'),
        ]);
    }

    /**
     * Update container.
     */
    public function update(Request $request, AssetContainer $assetContainer): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'disk' => 'required|string',
            'allow_uploads' => 'boolean',
            'allow_downloading' => 'boolean',
            'allow_renaming' => 'boolean',
            'allow_moving' => 'boolean',
            'max_file_size' => 'nullable|integer|min:1',
            'allowed_extensions' => 'nullable|array',
            'allowed_extensions.*' => 'string',
        ]);

        $assetContainer->update([
            'title' => $request->title,
            'disk' => $request->disk,
            'allow_uploads' => $request->boolean('allow_uploads'),
            'allow_downloading' => $request->boolean('allow_downloading'),
            'allow_renaming' => $request->boolean('allow_renaming'),
            'allow_moving' => $request->boolean('allow_moving'),
            'max_file_size' => $request->max_file_size,
            'allowed_extensions' => $request->allowed_extensions,
        ]);

        $this->flashSuccess(__('flash.asset_containers.updated'));

        return redirect()->route('cp.asset-containers.show', $assetContainer);
    }

    /**
     * Delete container.
     */
    public function destroy(AssetContainer $assetContainer): RedirectResponse
    {
        // Check if container has assets
        if ($assetContainer->assets()->count() > 0) {
            $this->flashError(__('flash.asset_containers.delete_blocked'));

            return redirect()->back();
        }

        $assetContainer->delete();

        $this->flashSuccess(__('flash.asset_containers.deleted'));

        return redirect()->route('cp.asset-containers.index');
    }
}
