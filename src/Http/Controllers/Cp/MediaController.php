<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Media::query()->with(['model'])->latest();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")->orWhere('file_name', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($collection = $request->get('collection')) {
            $query->where('collection_name', $collection);
        }

        // Type filter
        if ($type = $request->get('type')) {
            $query->where('mime_type', 'LIKE', "{$type}/%");
        }

        $media = $query->paginate(20)->withQueryString();

        // Get available collections and types for filters
        $collections = Media::distinct('collection_name')
            ->pluck('collection_name')
            ->filter()
            ->sort()
            ->values();

        $types = Media::selectRaw('SUBSTRING_INDEX(mime_type, "/", 1) as type')
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort()
            ->values();

        return Inertia::render('Media/media-index', [
            'media' => $media,
            'collections' => $collections,
            'types' => $types,
            'filters' => $request->only(['search', 'collection', 'type']),
        ]);
    }

    public function show(Media $media): Response
    {
        $media->load(['model']);

        return Inertia::render('Media/media-show', [
            'media' => $media,
            'conversions' => $media->getMediaConversions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
            'collection' => 'nullable|string',
            'alt_text' => 'nullable|string',
        ]);

        // For now, attach to a temporary model or create a generic media entry
        // In a real implementation, you might want a dedicated MediaLibrary model
        $product = new Product;

        $media = $product->addMediaFromRequest('file')->toMediaCollection($request->get('collection', 'default'));

        // Update alt text if provided
        if ($request->has('alt_text')) {
            $media->setCustomProperty('alt_text', $request->get('alt_text'));
            $media->save();
        }

        return response()->json([
            'media' => $media,
            'message' => 'Media uploaded successfully',
        ]);
    }

    public function update(Request $request, Media $media)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string',
            'custom_properties' => 'nullable|array',
        ]);

        if ($request->has('name')) {
            $media->name = $request->get('name');
        }

        if ($request->has('alt_text')) {
            $media->setCustomProperty('alt_text', $request->get('alt_text'));
        }

        if ($request->has('custom_properties')) {
            foreach ($request->get('custom_properties', []) as $key => $value) {
                $media->setCustomProperty($key, $value);
            }
        }

        $media->save();

        return response()->json([
            'media' => $media->fresh(),
            'message' => 'Media updated successfully',
        ]);
    }

    public function destroy(Media $media)
    {
        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        Media::whereIn('id', $request->get('ids'))->delete();

        return response()->json([
            'message' => 'Selected media deleted successfully',
        ]);
    }

    public function download(Media $media)
    {
        return response()->download($media->getPath(), $media->file_name);
    }
}
