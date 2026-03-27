<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreProductTypeRequest;
use Cartino\Http\Requests\Api\UpdateProductTypeRequest;
use Cartino\Http\Resources\ProductTypeResource;
use Cartino\Models\ProductType;
use Cartino\Repositories\ProductTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductTypesController extends ApiController
{
    public function __construct(
        private readonly ProductTypeRepository $repository,
    ) {}

    /**
     * Display a listing of product types
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified product type
     */
    public function show(int|string $slug): JsonResponse
    {
        $data = $this->repository->findOne($slug);

        return $this->successResponse(new ProductTypeResource($data));
    }

    /**
     * Store a newly created product type
     */
    public function store(StoreProductTypeRequest $request): JsonResponse
    {
        try {
            $productType = $this->repository->createOne($request->validated());

            return $this->created(new ProductTypeResource($productType), 'ProductType creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del product type: '.$e->getMessage());
        }
    }

    /**
     * Update the specified product type
     */
    public function update(UpdateProductTypeRequest $request, ProductType $productType): JsonResponse
    {
        try {
            $updatedProductType = $this->repository->updateOne($productType->id, $request->validated());

            return $this->successResponse(
                new ProductTypeResource($updatedProductType),
                'ProductType aggiornato con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del product type: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified product type
     */
    public function destroy(ProductType $productType): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($productType->id)) {
                return $this->errorResponse('Impossibile eliminare il product type: ha prodotti associati', 422);
            }

            $this->repository->deleteOne($productType->id);

            return $this->successResponse(null, 'ProductType eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del product type: '.$e->getMessage());
        }
    }

    /**
     * Toggle product type status
     */
    public function toggleStatus(ProductType $productType): JsonResponse
    {
        try {
            $updatedProductType = $this->repository->toggleStatus($productType->id);

            return $this->successResponse(
                new ProductTypeResource($updatedProductType),
                'Stato del product type aggiornato',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
