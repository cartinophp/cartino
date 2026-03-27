<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreCourierRequest;
use Cartino\Http\Requests\Api\UpdateCourierRequest;
use Cartino\Http\Resources\CourierResource;
use Cartino\Models\Courier;
use Cartino\Repositories\CourierRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouriersController extends ApiController
{
    public function __construct(
        private readonly CourierRepository $repository,
    ) {}

    /**
     * Display a listing of couriers
     */
    public function index(Request $request): JsonResponse
    {
        $request = $request->all();

        $data = $this->repository->findAll($request);

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified courier
     */
    public function show(int|string $handle): JsonResponse
    {
        $data = $this->repository->findOne($handle);

        return $this->successResponse(new CourierResource($data));
    }

    /**
     * Store a newly created courier
     */
    public function store(StoreCourierRequest $request): JsonResponse
    {
        try {
            $courier = $this->repository->createOne($request->validated());

            return $this->created(new CourierResource($courier), 'Corriere creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del corriere: '.$e->getMessage());
        }
    }

    /**
     * Update the specified courier
     */
    public function update(UpdateCourierRequest $request, Courier $courier): JsonResponse
    {
        try {
            $updatedCourier = $this->repository->updateOne($courier->id, $request->validated());

            return $this->successResponse(new CourierResource($updatedCourier), 'Corriere aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del corriere: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified courier
     */
    public function destroy(Courier $courier): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($courier->id)) {
                return $this->errorResponse('Impossibile eliminare il corriere: è associato a degli ordini', 422);
            }

            $this->repository->deleteOne($courier->id);

            return $this->successResponse(null, 'Corriere eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del corriere: '.$e->getMessage());
        }
    }

    /**
     * Toggle courier status
     */
    public function toggleStatus(Courier $courier): JsonResponse
    {
        try {
            $updatedCourier = $this->repository->toggleStatus($courier->id);

            return $this->successResponse(new CourierResource($updatedCourier), 'Stato del corriere aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }

    /**
     * Toggle courier enabled status
     */
    public function toggleEnabled(Courier $courier): JsonResponse
    {
        try {
            $updatedCourier = $this->repository->toggleEnabled($courier->id);

            return $this->successResponse(
                new CourierResource($updatedCourier),
                'Stato abilitazione del corriere aggiornato',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato abilitazione: '.$e->getMessage());
        }
    }

    /**
     * Get courier orders
     */
    public function orders(Courier $courier): JsonResponse
    {
        $orders = $this->repository->getCourierOrders($courier->id);

        return $this->successResponse($orders);
    }

    /**
     * Get enabled couriers
     */
    public function enabled(): JsonResponse
    {
        try {
            $couriers = $this->repository->getEnabled();

            return $this->successResponse(CourierResource::collection($couriers));
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel recupero dei corrieri abilitati: '.$e->getMessage());
        }
    }
}
