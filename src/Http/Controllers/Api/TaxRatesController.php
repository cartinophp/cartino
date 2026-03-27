<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreTaxRateRequest;
use Cartino\Http\Requests\Api\UpdateTaxRateRequest;
use Cartino\Http\Resources\TaxRateResource;
use Cartino\Models\TaxRate;
use Cartino\Repositories\TaxRateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRatesController extends ApiController
{
    public function __construct(
        private readonly TaxRateRepository $repository,
    ) {}

    /**
     * Display a listing of tax rates
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified tax rate
     */
    public function show(int|string $code): JsonResponse
    {
        $data = $this->repository->findOne($code);

        return $this->successResponse(new TaxRateResource($data));
    }

    /**
     * Store a newly created tax rate
     */
    public function store(StoreTaxRateRequest $request): JsonResponse
    {
        try {
            $taxRate = $this->repository->createOne($request->validated());

            return $this->created(new TaxRateResource($taxRate), 'TaxRate creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del tax rate: '.$e->getMessage());
        }
    }

    /**
     * Update the specified tax rate
     */
    public function update(UpdateTaxRateRequest $request, TaxRate $taxRate): JsonResponse
    {
        try {
            $updatedTaxRate = $this->repository->updateOne($taxRate->id, $request->validated());

            return $this->successResponse(new TaxRateResource($updatedTaxRate), 'TaxRate aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del tax rate: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified tax rate
     */
    public function destroy(TaxRate $taxRate): JsonResponse
    {
        try {
            $this->repository->deleteOne($taxRate->id);

            return $this->successResponse(null, 'TaxRate eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del tax rate: '.$e->getMessage());
        }
    }
}
