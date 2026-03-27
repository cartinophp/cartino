<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreCountryRequest;
use Cartino\Http\Requests\Api\UpdateCountryRequest;
use Cartino\Http\Resources\CountryResource;
use Cartino\Models\Country;
use Cartino\Repositories\CountryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountriesController extends ApiController
{
    public function __construct(
        private readonly CountryRepository $repository,
    ) {}

    /**
     * Display a listing of countries
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified country
     */
    public function show(int|string $code): JsonResponse
    {
        $data = $this->repository->findOne($code);

        return $this->successResponse(new CountryResource($data));
    }

    /**
     * Store a newly created country
     */
    public function store(StoreCountryRequest $request): JsonResponse
    {
        try {
            $country = $this->repository->createOne($request->validated());

            return $this->created(new CountryResource($country), 'Country creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del country: '.$e->getMessage());
        }
    }

    /**
     * Update the specified country
     */
    public function update(UpdateCountryRequest $request, Country $country): JsonResponse
    {
        try {
            $updatedCountry = $this->repository->updateOne($country->id, $request->validated());

            return $this->successResponse(new CountryResource($updatedCountry), 'Country aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del country: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified country
     */
    public function destroy(Country $country): JsonResponse
    {
        try {
            $this->repository->deleteOne($country->id);

            return $this->successResponse(null, 'Country eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del country: '.$e->getMessage());
        }
    }
}
