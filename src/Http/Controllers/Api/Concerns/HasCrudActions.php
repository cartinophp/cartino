<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait HasCrudActions
{
    /**
     * Define the repository instance in the controller
     */
    abstract protected function repository();

    /**
     * Define the resource class for responses
     */
    abstract protected function resourceClass(): string;

    /**
     * Define the entity name for messages (e.g., 'Product', 'Brand')
     */
    abstract protected function entityName(): string;

    /**
     * Display a listing of the resource
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->repository()->findAll($request->all());

            return $this->paginatedResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel recupero dei dati: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource
     */
    public function show(int|string $handle): JsonResponse
    {
        try {
            $data = $this->repository()->findOne($handle);
            $resourceClass = $this->resourceClass();

            return $this->successResponse(new $resourceClass($data));
        } catch (\Exception $e) {
            return $this->notFoundResponse($this->entityName().' non trovato');
        }
    }

    /**
     * Store a newly created resource
     */
    public function store(FormRequest $request): JsonResponse
    {
        try {
            $item = $this->repository()->createOne($request->validated());
            $resourceClass = $this->resourceClass();

            return $this->created(new $resourceClass($item), $this->entityName().' creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del '.$this->entityName().': '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource
     */
    public function update(FormRequest $request, Model $model): JsonResponse
    {
        try {
            $updated = $this->repository()->updateOne($model->id, $request->validated());
            $resourceClass = $this->resourceClass();

            return $this->successResponse(
                new $resourceClass($updated),
                $this->entityName().' aggiornato con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Errore nell'aggiornamento del ".$this->entityName().': '.$e->getMessage(),
            );
        }
    }

    /**
     * Remove the specified resource
     */
    public function destroy(Model $model): JsonResponse
    {
        try {
            if (! $this->repository()->canDelete($model->id)) {
                return $this->errorResponse(
                    'Impossibile eliminare il '.$this->entityName().': ha relazioni attive',
                    422,
                );
            }

            $this->repository()->deleteOne($model->id);

            return $this->successResponse(null, $this->entityName().' eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Errore nell'eliminazione del ".$this->entityName().': '.$e->getMessage(),
            );
        }
    }

    /**
     * Toggle status of the resource
     */
    public function toggleStatus(Model $model): JsonResponse
    {
        try {
            $updated = $this->repository()->toggleStatus($model->id);
            $resourceClass = $this->resourceClass();

            return $this->successResponse(
                new $resourceClass($updated),
                'Stato del '.$this->entityName().' aggiornato',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
