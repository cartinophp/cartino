<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StorePurchaseOrderRequest;
use Cartino\Http\Requests\Api\UpdatePurchaseOrderRequest;
use Cartino\Http\Resources\PurchaseOrderResource;
use Cartino\Models\PurchaseOrder;
use Cartino\Repositories\PurchaseOrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrdersController extends ApiController
{
    public function __construct(
        private readonly PurchaseOrderRepository $repository,
    ) {}

    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified purchase order
     */
    public function show(int|string $poNumber): JsonResponse
    {
        $data = $this->repository->findOne($poNumber);

        return $this->successResponse(new PurchaseOrderResource($data));
    }

    /**
     * Store a newly created purchase order
     */
    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        try {
            $purchaseOrder = $this->repository->createOne($request->validated());

            return $this->created(new PurchaseOrderResource($purchaseOrder), 'Purchase Order creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del purchase order: '.$e->getMessage());
        }
    }

    /**
     * Update the specified purchase order
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        try {
            $updatedPurchaseOrder = $this->repository->updateOne($purchaseOrder->id, $request->validated());

            return $this->successResponse(
                new PurchaseOrderResource($updatedPurchaseOrder),
                'Purchase Order aggiornato con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del purchase order: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($purchaseOrder->id)) {
                return $this->errorResponse('Impossibile eliminare il purchase order: stato non valido', 422);
            }

            $this->repository->deleteOne($purchaseOrder->id);

            return $this->successResponse(null, 'Purchase Order eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del purchase order: '.$e->getMessage());
        }
    }

    /**
     * Receive purchase order
     */
    public function receive(PurchaseOrder $purchaseOrder): JsonResponse
    {
        try {
            $receivedPO = $this->repository->receivePurchaseOrder($purchaseOrder->id);

            return $this->successResponse(
                new PurchaseOrderResource($receivedPO),
                'Purchase Order ricevuto con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella ricezione del purchase order: '.$e->getMessage());
        }
    }
}
