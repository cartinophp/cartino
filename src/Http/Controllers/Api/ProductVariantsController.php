<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreProductVariantRequest;
use Cartino\Http\Requests\Api\UpdateProductVariantRequest;
use Cartino\Http\Resources\ProductVariantResource;
use Cartino\Models\ProductVariant;
use Cartino\Repositories\ProductVariantRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantsController extends ApiController
{
    public function __construct(
        private readonly ProductVariantRepository $repository,
    ) {}

    /**
     * Display a listing of product variants
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified product variant
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->findOne($id);

        return $this->successResponse(new ProductVariantResource($data));
    }

    /**
     * Store a newly created product variant
     */
    public function store(StoreProductVariantRequest $request): JsonResponse
    {
        try {
            $variant = $this->repository->createOne($request->validated());

            return $this->created(new ProductVariantResource($variant), 'Variant creata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione della variant: '.$e->getMessage());
        }
    }

    /**
     * Update the specified product variant
     */
    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant): JsonResponse
    {
        try {
            $updatedVariant = $this->repository->updateOne($productVariant->id, $request->validated());

            return $this->successResponse(
                new ProductVariantResource($updatedVariant),
                'Variant aggiornata con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento della variant: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified product variant
     */
    public function destroy(ProductVariant $productVariant): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($productVariant->id)) {
                return $this->errorResponse('Impossibile eliminare la variant: è associata a ordini', 422);
            }

            $this->repository->deleteOne($productVariant->id);

            return $this->successResponse(null, 'Variant eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione della variant: '.$e->getMessage());
        }
    }
}
