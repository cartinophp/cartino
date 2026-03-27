<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Models\Asset;
use Cartino\Models\AssetContainer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssetBrowserController
{
    public function index(Request $request): Response
    {
        $containers = AssetContainer::withCount('assets')->get();

        $container = $request->get('container', $containers->first()?->handle ?? 'images');

        $query = Asset::query()->with(['containerModel', 'uploadedBy'])->inContainer($container);

        if ($request->filled('folder')) {
            $query->inFolder($request->folder);
        }

        if ($request->filled('type')) {
            match ($request->type) {
                'image' => $query->images(),
                'video' => $query->videos(),
                'document' => $query->documents(),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $assets = $query->paginate($request->get('per_page', 24));

        return Inertia::render('media/AssetBrowser', [
            'containers' => $containers,
            'assets' => $assets,
            'filters' => $request->only(['container', 'folder', 'type', 'search', 'sort_by']),
        ]);
    }
}
