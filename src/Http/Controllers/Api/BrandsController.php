<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreBrandRequest;
use Cartino\Http\Requests\Api\UpdateBrandRequest;
use Cartino\Http\Resources\BrandResource;
use Cartino\Models\Brand;
use Cartino\Repositories\BrandRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandsController extends ApiController
{
    public function __construct(
        private readonly BrandRepository $repository,
    ) {}

    /**
     * Display a listing of brands
     */
    public function index(Request $request): JsonResponse
    {
        $request = $request->all();

        $data = $this->repository->findAll($request);

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified brand
     */
    public function show(int|string $handle): JsonResponse
    {
        $data = $this->repository->findOne($handle);

        return $this->successResponse(new BrandResource($data));
    }

    /**
     * Store a newly created brand
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        try {
            $brand = $this->repository->createOne($request->validated());

            return $this->created(new BrandResource($brand), 'Brand creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del brand: '.$e->getMessage());
        }
    }

    /**
     * Update the specified brand
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        try {
            $updatedBrand = $this->repository->updateOne($brand->id, $request->validated());

            return $this->successResponse(new BrandResource($updatedBrand), 'Brand aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del brand: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified brand
     */
    public function destroy(Brand $brand): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($brand->id)) {
                return $this->errorResponse('Impossibile eliminare il brand: è associato a dei prodotti', 422);
            }

            $this->repository->deleteOne($brand->id);

            return $this->successResponse(null, 'Brand eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del brand: '.$e->getMessage());
        }
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(Brand $brand): JsonResponse
    {
        try {
            $updatedBrand = $this->repository->toggleStatus($brand->id);

            return $this->successResponse(new BrandResource($updatedBrand), 'Stato del brand aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }

    /**
     * Get brand products
     */
    public function products(Brand $brand): JsonResponse
    {
        $products = $this->repository->getBrandProducts($brand->id);

        return $this->successResponse($products);
    }
}
