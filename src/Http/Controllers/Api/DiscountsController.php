<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreDiscountRequest;
use Cartino\Http\Requests\Api\UpdateDiscountRequest;
use Cartino\Http\Resources\DiscountResource;
use Cartino\Models\Discount;
use Cartino\Repositories\DiscountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountsController extends ApiController
{
    public function __construct(
        private readonly DiscountRepository $repository,
    ) {}

    /**
     * Display a listing of discounts
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified discount
     */
    public function show(int|string $code): JsonResponse
    {
        $data = $this->repository->findOne($code);

        return $this->successResponse(new DiscountResource($data));
    }

    /**
     * Store a newly created discount
     */
    public function store(StoreDiscountRequest $request): JsonResponse
    {
        try {
            $discount = $this->repository->createOne($request->validated());

            return $this->created(new DiscountResource($discount), 'Discount creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del discount: '.$e->getMessage());
        }
    }

    /**
     * Update the specified discount
     */
    public function update(UpdateDiscountRequest $request, Discount $discount): JsonResponse
    {
        try {
            $updatedDiscount = $this->repository->updateOne($discount->id, $request->validated());

            return $this->successResponse(new DiscountResource($updatedDiscount), 'Discount aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del discount: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified discount
     */
    public function destroy(Discount $discount): JsonResponse
    {
        try {
            $this->repository->deleteOne($discount->id);

            return $this->successResponse(null, 'Discount eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del discount: '.$e->getMessage());
        }
    }

    /**
     * Toggle discount status
     */
    public function toggleStatus(Discount $discount): JsonResponse
    {
        try {
            $updatedDiscount = $this->repository->toggleStatus($discount->id);

            return $this->successResponse(new DiscountResource($updatedDiscount), 'Stato del discount aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
