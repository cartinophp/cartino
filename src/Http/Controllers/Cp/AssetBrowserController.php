<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Listing;
use Cartino\Cp\Page;
use Cartino\Models\Asset;
use Cartino\Models\AssetContainer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AssetBrowserController
{
    public function index(Request $request): Response
    {
        $containers = AssetContainer::withCount('assets')->get();
        $container = $request->get('container', $containers->first()?->handle ?? 'images');

        $page = Page::make('Assets')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Assets');

        $listing = Listing::make()
            ->column('thumbnail', '', sortable: false, width: '60px')
            ->column('name', 'Name', sortable: true)
            ->column('type', 'Type', sortable: true)
            ->column('size', 'Size', sortable: true)
            ->column('created_at', 'Uploaded', sortable: true, format: 'date')
            ->column('actions', '', sortable: false, width: '100px')
            ->filter('type', 'Type', 'select', [
                ['value' => '', 'label' => 'All Types'],
                ['value' => 'image', 'label' => 'Images'],
                ['value' => 'video', 'label' => 'Videos'],
                ['value' => 'document', 'label' => 'Documents'],
            ], 'All Types')
            ->searchable(placeholder: 'Search assets...')
            ->emptyState('No assets yet', 'Upload your first asset to get started.', icon: 'image')
            ->sort('created_at', 'desc')
            ->perPage(24);

        $data = QueryBuilder::for(Asset::class)
            ->with(['containerModel', 'uploadedBy'])
            ->where('container', $container)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('type'),
                AllowedFilter::exact('folder'),
            ])
            ->allowedSorts(['name', 'size', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 24))
            ->withQueryString();

        return Inertia::render('assets/index', [
            'page' => $page->compile(),
            'listing' => $listing->toArray(),
            'data' => $data,
            'containers' => $containers,
            'currentContainer' => $container,
        ]);
    }
}
