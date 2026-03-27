<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreCurrencyRequest;
use Cartino\Http\Requests\Api\UpdateCurrencyRequest;
use Cartino\Http\Resources\CurrencyResource;
use Cartino\Models\Currency;
use Cartino\Repositories\CurrencyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrenciesController extends ApiController
{
    public function __construct(
        private readonly CurrencyRepository $repository,
    ) {}

    /**
     * Display a listing of currencies
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified currency
     */
    public function show(int|string $code): JsonResponse
    {
        $data = $this->repository->findOne($code);

        return $this->successResponse(new CurrencyResource($data));
    }

    /**
     * Store a newly created currency
     */
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        try {
            $currency = $this->repository->createOne($request->validated());

            return $this->created(new CurrencyResource($currency), 'Currency creata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione della currency: '.$e->getMessage());
        }
    }

    /**
     * Update the specified currency
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency): JsonResponse
    {
        try {
            $updatedCurrency = $this->repository->updateOne($currency->id, $request->validated());

            return $this->successResponse(new CurrencyResource($updatedCurrency), 'Currency aggiornata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento della currency: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified currency
     */
    public function destroy(Currency $currency): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($currency->id)) {
                return $this->errorResponse(
                    'Impossibile eliminare la currency: è la currency di default o ha dati associati',
                    422,
                );
            }

            $this->repository->deleteOne($currency->id);

            return $this->successResponse(null, 'Currency eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione della currency: '.$e->getMessage());
        }
    }

    /**
     * Toggle currency status
     */
    public function toggleStatus(Currency $currency): JsonResponse
    {
        try {
            $updatedCurrency = $this->repository->toggleStatus($currency->id);

            return $this->successResponse(new CurrencyResource($updatedCurrency), 'Stato della currency aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
