<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\DTO\Wishlist\WishlistData;
use Cartino\Http\Controllers\Controller;
use Cartino\Http\Requests\Wishlist\StoreWishlistRequest;
use Cartino\Http\Requests\Wishlist\UpdateWishlistRequest;
use Cartino\Models\Wishlist;
use Cartino\Repositories\WishlistRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistRepository $repository,
    ) {
        $this->authorizeResource(Wishlist::class, 'wishlist', [
            'except' => ['index', 'show'],
        ]);
    }

    /**
     * Display a listing of wishlists
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Wishlist::class);

        $filters = $request->only(['search', 'status', 'customer_id', 'date_from', 'date_to']);
        $wishlists = $this->repository->getPaginated($filters, $request->get('per_page', 15));

        return Inertia::render('CP/Wishlists/index', [
            'wishlists' => $wishlists,
            'filters' => $filters,
            'statistics' => $this->repository->getStatistics(),
        ]);
    }

    /**
     * Show the form for creating a new wishlist
     */
    public function create(): Response
    {
        return Inertia::render('CP/Wishlists/Create');
    }

    /**
     * Store a newly created wishlist
     */
    public function store(StoreWishlistRequest $request): JsonResponse
    {
        $wishlist = $this->repository->create($request->validated());

        return response()->json([
            'message' => __('Wishlist created successfully'),
            'wishlist' => WishlistData::fromModel($wishlist),
        ], 201);
    }

    /**
     * Display the specified wishlist
     */
    public function show(Wishlist $wishlist): Response
    {
        $this->authorize('view', $wishlist);

        $wishlist->load(['customer', 'items.product']);

        return Inertia::render('CP/Wishlists/Show', [
            'wishlist' => WishlistData::fromModel($wishlist),
            'items' => $wishlist->items->map(fn ($item) => [
                'id' => $item->id,
                'product' => $item->product,
                'quantity' => $item->quantity,
                'added_at' => $item->created_at,
                'notes' => $item->notes,
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified wishlist
     */
    public function edit(Wishlist $wishlist): Response
    {
        $wishlist->load(['customer', 'items.product']);

        return Inertia::render('CP/Wishlists/Edit', [
            'wishlist' => WishlistData::fromModel($wishlist),
        ]);
    }

    /**
     * Update the specified wishlist
     */
    public function update(UpdateWishlistRequest $request, Wishlist $wishlist): JsonResponse
    {
        $wishlist = $this->repository->update($wishlist->id, $request->validated());

        return response()->json([
            'message' => __('Wishlist updated successfully'),
            'wishlist' => WishlistData::fromModel($wishlist),
        ]);
    }

    /**
     * Remove the specified wishlist
     */
    public function destroy(Wishlist $wishlist): JsonResponse
    {
        $this->repository->delete($wishlist->id);

        return response()->json([
            'message' => __('Wishlist deleted successfully'),
        ]);
    }

    /**
     * Add item to wishlist
     */
    public function addItem(Request $request, Wishlist $wishlist): JsonResponse
    {
        $this->authorize('update', $wishlist);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:999',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = $this->repository->addItem($wishlist->id, $request->only(['product_id', 'quantity', 'notes']));

        return response()->json([
            'message' => __('Item added to wishlist'),
            'item' => $item,
        ]);
    }

    /**
     * Remove item from wishlist
     */
    public function removeItem(Wishlist $wishlist, int $itemId): JsonResponse
    {
        $this->authorize('update', $wishlist);

        $this->repository->removeItem($wishlist->id, $itemId);

        return response()->json([
            'message' => __('Item removed from wishlist'),
        ]);
    }

    /**
     * Clear all items from wishlist
     */
    public function clear(Wishlist $wishlist): JsonResponse
    {
        $this->authorize('update', $wishlist);

        $this->repository->clearItems($wishlist->id);

        return response()->json([
            'message' => __('Wishlist cleared successfully'),
        ]);
    }

    /**
     * Share wishlist
     */
    public function share(Wishlist $wishlist): JsonResponse
    {
        $this->authorize('view', $wishlist);

        $shareToken = $this->repository->generateShareToken($wishlist->id);

        return response()->json([
            'message' => __('Wishlist share link generated'),
            'share_url' => route('wishlist.shared', ['token' => $shareToken]),
        ]);
    }

    /**
     * Bulk operations on wishlists
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,export',
            'wishlist_ids' => 'required|array',
            'wishlist_ids.*' => 'exists:wishlists,id',
        ]);

        $wishlistIds = $request->input('wishlist_ids');

        foreach ($wishlistIds as $wishlistId) {
            $wishlist = Wishlist::find($wishlistId);
            if ($wishlist) {
                $this->authorize($request->input('action') === 'delete' ? 'delete' : 'view', $wishlist);
            }
        }

        switch ($request->input('action')) {
            case 'delete':
                $deleted = $this->repository->bulkDelete($wishlistIds);

                return response()->json([
                    'message' => __(':count wishlists deleted successfully', ['count' => $deleted]),
                ]);

            case 'export':
                // TODO: Implement export functionality
                return response()->json([
                    'message' => __('Export functionality not implemented yet'),
                ], 501);

            default:
                return response()->json(['error' => __('Invalid action')], 400);
        }
    }

    /**
     * Get wishlist statistics
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', Wishlist::class);

        return response()->json($this->repository->getStatistics());
    }

    /**
     * Convert wishlist to order
     */
    public function convertToOrder(Wishlist $wishlist): JsonResponse
    {
        $this->authorize('update', $wishlist);

        // TODO: Implement conversion to order
        return response()->json([
            'message' => __('Conversion to order not implemented yet'),
        ], 501);
    }
}
