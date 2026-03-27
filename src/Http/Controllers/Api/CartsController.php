<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreCartRequest;
use Cartino\Http\Requests\Api\UpdateCartRequest;
use Cartino\Http\Resources\CartResource;
use Cartino\Models\Cart;
use Cartino\Repositories\CartRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartsController extends ApiController
{
    public function __construct(
        private readonly CartRepository $repository,
    ) {}

    /**
     * Display a listing of carts
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified cart
     */
    public function show(int|string $token): JsonResponse
    {
        $data = $this->repository->findOne($token);

        return $this->successResponse(new CartResource($data));
    }

    /**
     * Store a newly created cart
     */
    public function store(StoreCartRequest $request): JsonResponse
    {
        try {
            $cart = $this->repository->createOne($request->validated());

            return $this->created(new CartResource($cart), 'Cart creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del cart: '.$e->getMessage());
        }
    }

    /**
     * Update the specified cart
     */
    public function update(UpdateCartRequest $request, Cart $cart): JsonResponse
    {
        try {
            $updatedCart = $this->repository->updateOne($cart->id, $request->validated());

            return $this->successResponse(new CartResource($updatedCart), 'Cart aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del cart: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified cart
     */
    public function destroy(Cart $cart): JsonResponse
    {
        try {
            $this->repository->deleteOne($cart->id);

            return $this->successResponse(null, 'Cart eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del cart: '.$e->getMessage());
        }
    }

    /**
     * Clear cart items
     */
    public function clear(Cart $cart): JsonResponse
    {
        try {
            $clearedCart = $this->repository->clearCart($cart->id);

            return $this->successResponse(new CartResource($clearedCart), 'Cart svuotato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nello svuotamento del cart: '.$e->getMessage());
        }
    }
}
