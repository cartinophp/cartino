<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Models\Country;
use Cartino\Repositories\CountryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends ApiController
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
    ) {}

    /**
     * Display a listing of countries
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active']);
        $perPage = $request->get('per_page', 50);

        $countries = $this->countryRepository->findAll($filters, $perPage);

        return $this->paginatedResponse($countries);
    }

    /**
     * Get enabled countries for select
     */
    public function enabled(): JsonResponse
    {
        $countries = $this->countryRepository->getEnabled();

        return $this->successResponse($countries->map(fn ($country) => [
            'code' => $country->code,
            'name' => $country->name,
        ]));
    }

    /**
     * Get countries grouped by region
     */
    public function byRegion(): JsonResponse
    {
        $countries = $this->countryRepository->getByRegion();

        return $this->successResponse($countries);
    }

    /**
     * Get list of regions
     */
    public function regions(): JsonResponse
    {
        $regions = $this->countryRepository->getRegions();

        return $this->successResponse($regions);
    }

    /**
     * Search countries
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        $limit = $request->get('limit', 10);

        $filters = ['search' => $search, 'is_active' => true];
        $countries = $this->countryRepository->findAll($filters, $limit);

        return $this->successResponse($countries->items());
    }

    /**
     * Display the specified country
     */
    public function show(string $code): JsonResponse
    {
        try {
            $country = Country::where('code', strtoupper($code))->firstOrFail();

            return response()->json([
                'data' => $country,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Paese non trovato',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
