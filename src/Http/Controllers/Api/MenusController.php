<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreMenuRequest;
use Cartino\Http\Requests\Api\UpdateMenuRequest;
use Cartino\Http\Resources\MenuResource;
use Cartino\Models\Menu;
use Cartino\Repositories\MenuRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenusController extends ApiController
{
    public function __construct(
        private readonly MenuRepository $repository,
    ) {}

    /**
     * Display a listing of menus
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified menu
     */
    public function show(int|string $handle): JsonResponse
    {
        $data = $this->repository->findOne($handle);

        return $this->successResponse(new MenuResource($data));
    }

    /**
     * Store a newly created menu
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        try {
            $menu = $this->repository->createOne($request->validated());

            return $this->created(new MenuResource($menu), 'Menu creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del menu: '.$e->getMessage());
        }
    }

    /**
     * Update the specified menu
     */
    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        try {
            $updatedMenu = $this->repository->updateOne($menu->id, $request->validated());

            return $this->successResponse(new MenuResource($updatedMenu), 'Menu aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del menu: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified menu
     */
    public function destroy(Menu $menu): JsonResponse
    {
        try {
            $this->repository->deleteOne($menu->id);

            return $this->successResponse(null, 'Menu eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del menu: '.$e->getMessage());
        }
    }

    /**
     * Get menu items
     */
    public function items(Menu $menu): JsonResponse
    {
        $items = $this->repository->getMenuItems($menu->id);

        return $this->successResponse($items);
    }
}
