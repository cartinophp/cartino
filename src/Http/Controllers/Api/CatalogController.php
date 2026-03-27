<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Resources\CatalogResource;
use Cartino\Models\Catalog;
use Cartino\Repositories\CatalogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogController extends ApiController
{
    public function __construct(
        private readonly CatalogRepository $repository,
    ) {}

    /**
     * Display a listing of catalogs
     *
     * Supports filtering by site:
     * - GET /api/catalogs?filter[site]=1
     * - GET /api/catalogs?filter[site]=site-slug
     * - GET /api/catalogs?filter[site]=site-handle
     */
    public function index(Request $request): JsonResponse
    {
        $request = $request->all();

        $data = $this->repository->findAll($request);

        return $this->paginatedResponse($data);
    }

    /**
     * Display a single catalog
     */
    public function show(Catalog $catalog): JsonResource
    {
        $catalog->load(['sites', 'products']);

        return new CatalogResource($catalog);
    }

    /**
     * Get active catalogs
     */
    public function active(Request $request): JsonResponse
    {
        $params = $request->all();
        $params['filter']['active'] = true;

        $data = $this->repository->findAll($params);

        return $this->paginatedResponse($data);
    }

    /**
     * Get published catalogs
     */
    public function published(Request $request): JsonResponse
    {
        $params = $request->all();
        $params['filter']['published'] = true;

        $data = $this->repository->findAll($params);

        return $this->paginatedResponse($data);
    }
}
