<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreGlobalRequest;
use Cartino\Http\Requests\Api\UpdateGlobalRequest;
use Cartino\Http\Resources\GlobalResource;
use Cartino\Models\GlobalSet;
use Cartino\Repositories\GlobalRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalsController extends ApiController
{
    public function __construct(
        private readonly GlobalRepository $repository,
    ) {}

    /**
     * Display a listing of globals
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GlobalSet::class);

        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified global
     */
    public function show(int|string $handleOrId): JsonResponse
    {
        $data = $this->repository->findOne($handleOrId);

        if (! $data) {
            return $this->errorResponse('Global non trovato', 404);
        }

        $this->authorize('view', $data);

        return $this->successResponse(new GlobalResource($data));
    }

    /**
     * Store a newly created global
     */
    public function store(StoreGlobalRequest $request): JsonResponse
    {
        $this->authorize('create', GlobalSet::class);

        try {
            $globalSet = $this->repository->createOne($request->validated());

            return $this->created(new GlobalResource($globalSet), 'Global creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del global: '.$e->getMessage());
        }
    }

    /**
     * Update the specified global
     */
    public function update(UpdateGlobalRequest $request, GlobalSet $globalSet): JsonResponse
    {
        $this->authorize('update', $globalSet);

        try {
            $updatedGlobal = $this->repository->updateOne($globalSet->id, $request->validated());

            return $this->successResponse(new GlobalResource($updatedGlobal), 'Global aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del global: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified global
     */
    public function destroy(GlobalSet $globalSet): JsonResponse
    {
        $this->authorize('delete', $globalSet);

        try {
            $this->repository->deleteOne($globalSet->id);

            return $this->successResponse(null, 'Global eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del global: '.$e->getMessage());
        }
    }

    /**
     * Get global by handle
     */
    public function byHandle(string $handle): JsonResponse
    {
        $globalSet = $this->repository->getByHandle($handle);

        if (! $globalSet) {
            return $this->errorResponse('Global non trovato', 404);
        }

        return $this->successResponse(new GlobalResource($globalSet));
    }

    /**
     * Update global data by handle
     */
    public function updateByHandle(Request $request, string $handle): JsonResponse
    {
        $request->validate([
            'data' => ['required', 'array'],
        ]);

        try {
            $globalSet = $this->repository->updateByHandle($handle, $request->data);

            if (! $globalSet) {
                return $this->errorResponse('Global non trovato', 404);
            }

            return $this->successResponse(new GlobalResource($globalSet), 'Global aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del global: '.$e->getMessage());
        }
    }

    /**
     * Set a specific value in global data
     */
    public function setValue(Request $request, string $handle): JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string'],
            'value' => ['required'],
        ]);

        try {
            $globalSet = $this->repository->setValue($handle, $request->key, $request->value);

            if (! $globalSet) {
                return $this->errorResponse('Global non trovato', 404);
            }

            return $this->successResponse(new GlobalResource($globalSet), 'Valore aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del valore: '.$e->getMessage());
        }
    }

    /**
     * Get a specific value from global data
     */
    public function getValue(string $handle, string $key): JsonResponse
    {
        $value = $this->repository->getValue($handle, $key);

        return $this->successResponse([
            'handle' => $handle,
            'key' => $key,
            'value' => $value,
        ]);
    }
}
