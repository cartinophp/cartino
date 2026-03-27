<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreSupplierRequest;
use Cartino\Http\Requests\Api\UpdateSupplierRequest;
use Cartino\Http\Resources\SupplierResource;
use Cartino\Models\Supplier;
use Cartino\Repositories\SupplierRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuppliersController extends ApiController
{
    public function __construct(
        private readonly SupplierRepository $repository,
    ) {}

    /**
     * Display a listing of suppliers
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified supplier
     */
    public function show(int|string $code): JsonResponse
    {
        $data = $this->repository->findOne($code);

        return $this->successResponse(new SupplierResource($data));
    }

    /**
     * Store a newly created supplier
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            $supplier = $this->repository->createOne($request->validated());

            return $this->created(new SupplierResource($supplier), 'Supplier creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del supplier: '.$e->getMessage());
        }
    }

    /**
     * Update the specified supplier
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        try {
            $updatedSupplier = $this->repository->updateOne($supplier->id, $request->validated());

            return $this->successResponse(new SupplierResource($updatedSupplier), 'Supplier aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del supplier: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified supplier
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($supplier->id)) {
                return $this->errorResponse('Impossibile eliminare il supplier: ha ordini associati', 422);
            }

            $this->repository->deleteOne($supplier->id);

            return $this->successResponse(null, 'Supplier eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del supplier: '.$e->getMessage());
        }
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(Supplier $supplier): JsonResponse
    {
        try {
            $updatedSupplier = $this->repository->toggleStatus($supplier->id);

            return $this->successResponse(new SupplierResource($updatedSupplier), 'Stato del supplier aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
