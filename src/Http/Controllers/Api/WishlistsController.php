<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreWishlistRequest;
use Cartino\Http\Requests\Api\UpdateWishlistRequest;
use Cartino\Http\Resources\WishlistResource;
use Cartino\Models\Wishlist;
use Cartino\Repositories\WishlistRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistsController extends ApiController
{
    public function __construct(
        private readonly WishlistRepository $repository,
    ) {}

    /**
     * Display a listing of wishlists
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified wishlist
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->findOne($id);

        return $this->successResponse(new WishlistResource($data));
    }

    /**
     * Store a newly created wishlist
     */
    public function store(StoreWishlistRequest $request): JsonResponse
    {
        try {
            $wishlist = $this->repository->createOne($request->validated());

            return $this->created(new WishlistResource($wishlist), 'Wishlist creata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione della wishlist: '.$e->getMessage());
        }
    }

    /**
     * Update the specified wishlist
     */
    public function update(UpdateWishlistRequest $request, Wishlist $wishlist): JsonResponse
    {
        try {
            $updatedWishlist = $this->repository->updateOne($wishlist->id, $request->validated());

            return $this->successResponse(new WishlistResource($updatedWishlist), 'Wishlist aggiornata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento della wishlist: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified wishlist
     */
    public function destroy(Wishlist $wishlist): JsonResponse
    {
        try {
            $this->repository->deleteOne($wishlist->id);

            return $this->successResponse(null, 'Wishlist eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione della wishlist: '.$e->getMessage());
        }
    }

    /**
     * Get wishlist items
     */
    public function items(Wishlist $wishlist): JsonResponse
    {
        $items = $this->repository->getWishlistItems($wishlist->id);

        return $this->successResponse($items);
    }
}
