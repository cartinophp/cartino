<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreCustomerRequest;
use Cartino\Http\Requests\Api\UpdateCustomerRequest;
use Cartino\Http\Resources\CustomerResource;
use Cartino\Models\Customer;
use Cartino\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomersController extends ApiController
{
    public function __construct(
        private readonly CustomerRepository $repository,
    ) {}

    /**
     * Display a listing of customers
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified customer
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->findOne($id);

        return $this->successResponse(new CustomerResource($data));
    }

    /**
     * Store a newly created customer
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        try {
            $customer = $this->repository->createOne($request->validated());

            return $this->created(new CustomerResource($customer), 'Customer creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del customer: '.$e->getMessage());
        }
    }

    /**
     * Update the specified customer
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        try {
            $updatedCustomer = $this->repository->updateOne($customer->id, $request->validated());

            return $this->successResponse(new CustomerResource($updatedCustomer), 'Customer aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del customer: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($customer->id)) {
                return $this->errorResponse('Impossibile eliminare il customer: ha ordini associati', 422);
            }

            $this->repository->deleteOne($customer->id);

            return $this->successResponse(null, 'Customer eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del customer: '.$e->getMessage());
        }
    }

    /**
     * Get customer orders
     */
    public function orders(Customer $customer): JsonResponse
    {
        $orders = $this->repository->getCustomerOrders($customer->id);

        return $this->successResponse($orders);
    }

    /**
     * Toggle customer status
     */
    public function toggleStatus(Customer $customer): JsonResponse
    {
        try {
            $updatedCustomer = $this->repository->toggleStatus($customer->id);

            return $this->successResponse(new CustomerResource($updatedCustomer), 'Stato del customer aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
