<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreShippingZoneRequest;
use Cartino\Http\Requests\Api\UpdateShippingZoneRequest;
use Cartino\Http\Resources\ShippingZoneResource;
use Cartino\Models\ShippingZone;
use Cartino\Repositories\ShippingZoneRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingZonesController extends ApiController
{
    public function __construct(
        private readonly ShippingZoneRepository $repository,
    ) {}

    /**
     * Display a listing of shipping zones
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified shipping zone
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->findOne($id);

        return $this->successResponse(new ShippingZoneResource($data));
    }

    /**
     * Store a newly created shipping zone
     */
    public function store(StoreShippingZoneRequest $request): JsonResponse
    {
        try {
            $shippingZone = $this->repository->createOne($request->validated());

            return $this->created(new ShippingZoneResource($shippingZone), 'ShippingZone creata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione della shipping zone: '.$e->getMessage());
        }
    }

    /**
     * Update the specified shipping zone
     */
    public function update(UpdateShippingZoneRequest $request, ShippingZone $shippingZone): JsonResponse
    {
        try {
            $updatedShippingZone = $this->repository->updateOne($shippingZone->id, $request->validated());

            return $this->successResponse(
                new ShippingZoneResource($updatedShippingZone),
                'ShippingZone aggiornata con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento della shipping zone: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified shipping zone
     */
    public function destroy(ShippingZone $shippingZone): JsonResponse
    {
        try {
            $this->repository->deleteOne($shippingZone->id);

            return $this->successResponse(null, 'ShippingZone eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione della shipping zone: '.$e->getMessage());
        }
    }
}
