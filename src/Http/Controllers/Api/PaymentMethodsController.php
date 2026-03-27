<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StorePageRequest;
use Cartino\Http\Requests\Api\UpdatePageRequest;
use Cartino\Http\Resources\PageResource;
use Cartino\Models\Page;
use Cartino\Repositories\PaymentMethodRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodsController extends ApiController
{
    public function __construct(
        private readonly PaymentMethodRepository $repository,
    ) {}

    /**
     * Display a listing of pages
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified page
     */
    public function show(int|string $slug): JsonResponse
    {
        $data = $this->repository->findOne($slug);

        return $this->successResponse(new PageResource($data));
    }

    /**
     * Store a newly created page
     */
    public function store(StorePageRequest $request): JsonResponse
    {
        try {
            $page = $this->repository->createOne($request->validated());

            return $this->created(new PageResource($page), 'Page creata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione della page: '.$e->getMessage());
        }
    }

    /**
     * Update the specified page
     */
    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        try {
            $updatedPage = $this->repository->updateOne($page->id, $request->validated());

            return $this->successResponse(new PageResource($updatedPage), 'Page aggiornata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento della page: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified page
     */
    public function destroy(Page $page): JsonResponse
    {
        try {
            $this->repository->deleteOne($page->id);

            return $this->successResponse(null, 'Page eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione della page: '.$e->getMessage());
        }
    }

    /**
     * Toggle page status
     */
    public function toggleStatus(Page $page): JsonResponse
    {
        try {
            $updatedPage = $this->repository->toggleStatus($page->id);

            return $this->successResponse(new PageResource($updatedPage), 'Stato della page aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
