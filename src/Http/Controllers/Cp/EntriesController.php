<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class EntriesController extends Controller
{
    public function index(Request $request, string $collection): JsonResponse
    {
        // Mock entries data - in real app would fetch from database with pagination
        $entries = collect([
            [
                'id' => 1,
                'title' => 'Premium Wireless Headphones',
                'slug' => 'premium-wireless-headphones',
                'handle' => 'premium-wireless-headphones',
                'collection_handle' => $collection,
                'collection_title' => 'Products',
                'status' => 'published',
                'is_published' => true,
                'is_featured' => true,
                'excerpt' => 'High-quality wireless headphones with noise cancellation and premium sound quality.',
                'description' => 'Experience superior audio with our premium wireless headphones featuring advanced noise cancellation technology.',
                'author' => 'John Doe',
                'category' => 'Electronics',
                'price' => 299.99,
                'stock_quantity' => 25,
                'views' => 1247,
                'site' => 'default',
                'url' => '/products/premium-wireless-headphones',
                'featured_image' => '/storage/products/headphones-1.jpg',
                'published_at' => now()->subDays(5)->toISOString(),
                'created_at' => now()->subDays(10)->toISOString(),
                'updated_at' => now()->subDays(2)->toISOString(),
            ],
            [
                'id' => 2,
                'title' => 'Smart Fitness Watch',
                'slug' => 'smart-fitness-watch',
                'handle' => 'smart-fitness-watch',
                'collection_handle' => $collection,
                'collection_title' => 'Products',
                'status' => 'published',
                'is_published' => true,
                'is_featured' => false,
                'excerpt' => 'Track your fitness goals with our advanced smart watch featuring heart rate monitoring.',
                'description' => 'Stay motivated and track your progress with comprehensive fitness tracking capabilities.',
                'author' => 'Jane Smith',
                'category' => 'Wearables',
                'price' => 199.99,
                'stock_quantity' => 0,
                'views' => 892,
                'site' => 'default',
                'url' => '/products/smart-fitness-watch',
                'featured_image' => '/storage/products/watch-1.jpg',
                'published_at' => now()->subDays(8)->toISOString(),
                'created_at' => now()->subDays(12)->toISOString(),
                'updated_at' => now()->subDays(1)->toISOString(),
            ],
            [
                'id' => 3,
                'title' => 'Portable Bluetooth Speaker',
                'slug' => 'portable-bluetooth-speaker',
                'handle' => 'portable-bluetooth-speaker',
                'collection_handle' => $collection,
                'collection_title' => 'Products',
                'status' => 'draft',
                'is_published' => false,
                'is_featured' => false,
                'excerpt' => 'Compact and powerful bluetooth speaker with 360-degree sound.',
                'description' => 'Take your music anywhere with our waterproof, portable bluetooth speaker.',
                'author' => 'Mike Johnson',
                'category' => 'Audio',
                'price' => 79.99,
                'stock_quantity' => 15,
                'views' => 324,
                'site' => 'default',
                'url' => '/products/portable-bluetooth-speaker',
                'featured_image' => '/storage/products/speaker-1.jpg',
                'published_at' => null,
                'created_at' => now()->subDays(3)->toISOString(),
                'updated_at' => now()->subHours(4)->toISOString(),
            ],
            [
                'id' => 4,
                'title' => 'Gaming Mechanical Keyboard',
                'slug' => 'gaming-mechanical-keyboard',
                'handle' => 'gaming-mechanical-keyboard',
                'collection_handle' => $collection,
                'collection_title' => 'Products',
                'status' => 'scheduled',
                'is_published' => false,
                'is_featured' => true,
                'excerpt' => 'Professional gaming keyboard with RGB lighting and mechanical switches.',
                'description' => 'Dominate your games with our high-performance mechanical gaming keyboard.',
                'author' => 'Sarah Wilson',
                'category' => 'Gaming',
                'price' => 149.99,
                'stock_quantity' => 8,
                'views' => 567,
                'site' => 'gaming',
                'url' => '/products/gaming-mechanical-keyboard',
                'featured_image' => '/storage/products/keyboard-1.jpg',
                'published_at' => now()->addDays(2)->toISOString(),
                'created_at' => now()->subDays(7)->toISOString(),
                'updated_at' => now()->subDays(1)->toISOString(),
            ],
            [
                'id' => 5,
                'title' => 'Wireless Charging Pad',
                'slug' => 'wireless-charging-pad',
                'handle' => 'wireless-charging-pad',
                'collection_handle' => $collection,
                'collection_title' => 'Products',
                'status' => 'published',
                'is_published' => true,
                'is_featured' => false,
                'excerpt' => 'Fast wireless charging pad compatible with all Qi-enabled devices.',
                'description' => 'Charge your devices effortlessly with our sleek wireless charging solution.',
                'author' => 'Tom Brown',
                'category' => 'Accessories',
                'price' => 39.99,
                'stock_quantity' => 50,
                'views' => 1432,
                'site' => 'default',
                'url' => '/products/wireless-charging-pad',
                'featured_image' => '/storage/products/charger-1.jpg',
                'published_at' => now()->subDays(15)->toISOString(),
                'created_at' => now()->subDays(20)->toISOString(),
                'updated_at' => now()->subDays(3)->toISOString(),
            ],
        ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = strtolower($request->get('search'));
            $entries = $entries->filter(function ($entry) use ($search) {
                return
                    str_contains(strtolower($entry['title']), $search) ||
                    str_contains(strtolower($entry['slug']), $search) ||
                    str_contains(strtolower($entry['excerpt'] ?? ''), $search);
            });
        }

        if ($request->filled('status')) {
            $entries = $entries->where('status', $request->get('status'));
        }

        if ($request->filled('site')) {
            $entries = $entries->where('site', $request->get('site'));
        }

        if ($request->filled('stock_status')) {
            $stockStatus = $request->get('stock_status');
            if ($stockStatus === 'in_stock') {
                $entries = $entries->where('stock_quantity', '>', 0);
            } elseif ($stockStatus === 'out_of_stock') {
                $entries = $entries->where('stock_quantity', '<=', 0);
            } elseif ($stockStatus === 'low_stock') {
                $entries = $entries->whereBetween('stock_quantity', [1, 10]);
            }
        }

        if ($request->filled('featured')) {
            $entries = $entries->where('is_featured', $request->boolean('featured'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $entries = $entries->sortBy($sortBy, SORT_REGULAR, $sortDirection === 'desc');

        // Paginate manually for demo
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $total = $entries->count();

        $paginatedEntries = $entries->slice(($page - 1) * $perPage, $perPage)->values();

        $pagination = new LengthAwarePaginator($paginatedEntries, $total, $perPage, $page, [
            'path' => $request->url(),
            'pageName' => 'page',
        ]);

        return response()->json([
            'entries' => $paginatedEntries,
            'pagination' => [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'total' => $pagination->total(),
                'from' => $pagination->firstItem(),
                'to' => $pagination->lastItem(),
                'has_more_pages' => $pagination->hasMorePages(),
            ],
            'filters' => [
                'statuses' => [
                    ['value' => 'published', 'label' => 'Published', 'count' => 3],
                    ['value' => 'draft', 'label' => 'Draft', 'count' => 1],
                    ['value' => 'scheduled', 'label' => 'Scheduled', 'count' => 1],
                ],
                'sites' => [
                    ['value' => 'default', 'label' => 'Default Site', 'count' => 4],
                    ['value' => 'gaming', 'label' => 'Gaming Site', 'count' => 1],
                ],
                'stock_statuses' => [
                    ['value' => 'in_stock', 'label' => 'In Stock', 'count' => 4],
                    ['value' => 'out_of_stock', 'label' => 'Out of Stock', 'count' => 1],
                    ['value' => 'low_stock', 'label' => 'Low Stock', 'count' => 1],
                ],
            ],
        ]);
    }

    public function show(string $collection, int $id): JsonResponse
    {
        // Mock entry data - in real app would fetch from database
        $entry = [
            'id' => $id,
            'title' => 'Premium Wireless Headphones',
            'slug' => 'premium-wireless-headphones',
            'handle' => 'premium-wireless-headphones',
            'collection_handle' => $collection,
            'collection_title' => 'Products',
            'status' => 'published',
            'is_published' => true,
            'is_featured' => true,
            'excerpt' => 'High-quality wireless headphones with noise cancellation.',
            'description' => 'Experience superior audio with our premium wireless headphones.',
            'author' => 'John Doe',
            'category' => 'Electronics',
            'price' => 299.99,
            'stock_quantity' => 25,
            'views' => 1247,
            'site' => 'default',
            'url' => '/products/premium-wireless-headphones',
            'featured_image' => '/storage/products/headphones-1.jpg',
            'published_at' => now()->subDays(5)->toISOString(),
            'created_at' => now()->subDays(10)->toISOString(),
            'updated_at' => now()->subDays(2)->toISOString(),
            'fields' => [
                'title' => 'Premium Wireless Headphones',
                'description' => 'Experience superior audio with our premium wireless headphones.',
                'price' => 299.99,
                'stock_quantity' => 25,
                'featured_image' => '/storage/products/headphones-1.jpg',
            ],
        ];

        return response()->json(['entry' => $entry]);
    }

    public function store(Request $request, string $collection): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:entries,slug',
            'status' => 'in:published,draft,scheduled',
            'is_featured' => 'boolean',
            'fields' => 'array',
        ]);

        // Mock creation - in real app would create in database
        $entry = [
            'id' => rand(1000, 9999),
            'title' => $request->get('title'),
            'slug' => $request->get('slug') ?: str()->slug($request->get('title')),
            'handle' => $request->get('slug') ?: str()->slug($request->get('title')),
            'collection_handle' => $collection,
            'collection_title' => 'Products',
            'status' => $request->get('status', 'draft'),
            'is_published' => $request->get('status') === 'published',
            'is_featured' => $request->boolean('is_featured'),
            'site' => 'default',
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'fields' => $request->get('fields', []),
        ];

        return response()->json([
            'entry' => $entry,
            'message' => 'Entry created successfully',
        ], 201);
    }

    public function update(Request $request, string $collection, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'status' => 'in:published,draft,scheduled',
            'is_featured' => 'boolean',
            'fields' => 'array',
        ]);

        // Mock update - in real app would update in database
        $entry = [
            'id' => $id,
            'title' => $request->get('title'),
            'slug' => $request->get('slug'),
            'handle' => $request->get('slug'),
            'collection_handle' => $collection,
            'collection_title' => 'Products',
            'status' => $request->get('status'),
            'is_published' => $request->get('status') === 'published',
            'is_featured' => $request->boolean('is_featured'),
            'site' => 'default',
            'created_at' => now()->subDays(10)->toISOString(),
            'updated_at' => now()->toISOString(),
            'fields' => $request->get('fields', []),
        ];

        return response()->json([
            'entry' => $entry,
            'message' => 'Entry updated successfully',
        ]);
    }

    public function destroy(string $collection, int $id): JsonResponse
    {
        // Mock deletion - in real app would delete from database
        return response()->json([
            'message' => 'Entry deleted successfully',
        ]);
    }

    public function bulkAction(Request $request, string $collection): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,publish,unpublish,feature,unfeature',
            'entries' => 'required|array|min:1',
            'entries.*' => 'integer',
        ]);

        $action = $request->get('action');
        $entryIds = $request->get('entries');

        // Mock bulk action - in real app would perform bulk operation
        switch ($action) {
            case 'delete':
                $message = count($entryIds).' entries deleted successfully';
                break;
            case 'publish':
                $message = count($entryIds).' entries published successfully';
                break;
            case 'unpublish':
                $message = count($entryIds).' entries unpublished successfully';
                break;
            case 'feature':
                $message = count($entryIds).' entries featured successfully';
                break;
            case 'unfeature':
                $message = count($entryIds).' entries unfeatured successfully';
                break;
            default:
                $message = 'Bulk action completed successfully';
        }

        return response()->json([
            'message' => $message,
            'affected_count' => count($entryIds),
        ]);
    }
}
