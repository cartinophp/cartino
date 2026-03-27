<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreOrderRequest;
use Cartino\Http\Requests\Api\UpdateOrderRequest;
use Cartino\Http\Resources\OrderResource;
use Cartino\Models\Order;
use Cartino\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController extends ApiController
{
    public function __construct(
        private readonly OrderRepository $repository,
    ) {}

    /**
     * Display a listing of orders
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified order
     */
    public function show(int|string $orderNumber): JsonResponse
    {
        $data = $this->repository->findOne($orderNumber);

        return $this->successResponse(new OrderResource($data));
    }

    /**
     * Store a newly created order
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->repository->createOne($request->validated());

            return $this->created(new OrderResource($order), 'Order creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione dell\'order: '.$e->getMessage());
        }
    }

    /**
     * Update the specified order
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->repository->updateOne($order->id, $request->validated());

            return $this->successResponse(new OrderResource($updatedOrder), 'Order aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento dell\'order: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(Order $order): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($order->id)) {
                return $this->errorResponse('Impossibile eliminare l\'order: stato non valido', 422);
            }

            $this->repository->deleteOne($order->id);

            return $this->successResponse(null, 'Order eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione dell\'order: '.$e->getMessage());
        }
    }

    /**
     * Cancel the specified order
     */
    public function cancel(Order $order): JsonResponse
    {
        try {
            $cancelledOrder = $this->repository->cancelOrder($order->id);

            return $this->successResponse(new OrderResource($cancelledOrder), 'Order cancellato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella cancellazione dell\'order: '.$e->getMessage());
        }
    }
}
