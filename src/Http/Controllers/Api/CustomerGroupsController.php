<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreCustomerGroupRequest;
use Cartino\Http\Requests\Api\UpdateCustomerGroupRequest;
use Cartino\Http\Resources\CustomerGroupResource;
use Cartino\Models\CustomerGroup;
use Cartino\Repositories\CustomerGroupRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerGroupsController extends ApiController
{
    public function __construct(
        private readonly CustomerGroupRepository $repository,
    ) {}

    /**
     * Display a listing of customer groups
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified customer group
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->findOne($id);

        return $this->successResponse(new CustomerGroupResource($data));
    }

    /**
     * Store a newly created customer group
     */
    public function store(StoreCustomerGroupRequest $request): JsonResponse
    {
        try {
            $customerGroup = $this->repository->createOne($request->validated());

            return $this->created(new CustomerGroupResource($customerGroup), 'CustomerGroup creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del customer group: '.$e->getMessage());
        }
    }

    /**
     * Update the specified customer group
     */
    public function update(UpdateCustomerGroupRequest $request, CustomerGroup $customerGroup): JsonResponse
    {
        try {
            $updatedCustomerGroup = $this->repository->updateOne($customerGroup->id, $request->validated());

            return $this->successResponse(
                new CustomerGroupResource($updatedCustomerGroup),
                'CustomerGroup aggiornato con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del customer group: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified customer group
     */
    public function destroy(CustomerGroup $customerGroup): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($customerGroup->id)) {
                return $this->errorResponse('Impossibile eliminare il customer group: ha clienti associati', 422);
            }

            $this->repository->deleteOne($customerGroup->id);

            return $this->successResponse(null, 'CustomerGroup eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del customer group: '.$e->getMessage());
        }
    }
}
