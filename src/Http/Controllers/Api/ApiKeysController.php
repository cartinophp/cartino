<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreApiKeyRequest;
use Cartino\Http\Requests\Api\UpdateApiKeyRequest;
use Cartino\Http\Resources\ApiKeyResource;
use Cartino\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiKeysController extends ApiController
{
    /**
     * Display a listing of API keys
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ApiKey::class);

        $keys = ApiKey::query()
            ->with('creator')
            ->when($request->has('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->paginatedResponse($keys, ApiKeyResource::class);
    }

    /**
     * Store a newly created API key
     */
    public function store(StoreApiKeyRequest $request): JsonResponse
    {
        $this->authorize('create', ApiKey::class);

        try {
            // Genera la chiave in chiaro
            $plainKey = ApiKey::generate();

            // Crea la API key con hash
            $apiKey = ApiKey::create([
                'name' => $request->name,
                'key' => ApiKey::hash($plainKey),
                'description' => $request->description,
                'type' => $request->type,
                'permissions' => $request->type === 'custom' ? $request->permissions : null,
                'expires_at' => $request->expires_at,
                'is_active' => $request->is_active ?? true,
                'created_by' => auth()->id(),
            ]);

            // Ritorna la risorsa con la chiave in chiaro (solo questa volta!)
            $resource = (new ApiKeyResource($apiKey))->withPlainKey($plainKey);

            return $this->created(
                $resource,
                'API key creata con successo. ATTENZIONE: Salva questa chiave, non sarà più visibile!',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione della API key: '.$e->getMessage());
        }
    }

    /**
     * Display the specified API key
     */
    public function show(ApiKey $apiKey): JsonResponse
    {
        $this->authorize('view', $apiKey);

        $apiKey->load('creator');

        return $this->successResponse(new ApiKeyResource($apiKey));
    }

    /**
     * Update the specified API key
     */
    public function update(UpdateApiKeyRequest $request, ApiKey $apiKey): JsonResponse
    {
        $this->authorize('update', $apiKey);

        try {
            $apiKey->update($request->validated());

            return $this->successResponse(new ApiKeyResource($apiKey), 'API key aggiornata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento della API key: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified API key
     */
    public function destroy(ApiKey $apiKey): JsonResponse
    {
        $this->authorize('delete', $apiKey);

        try {
            $apiKey->delete();

            return $this->successResponse(null, 'API key eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione della API key: '.$e->getMessage());
        }
    }

    /**
     * Revoke (deactivate) an API key
     */
    public function revoke(ApiKey $apiKey): JsonResponse
    {
        $this->authorize('update', $apiKey);

        try {
            $apiKey->update(['is_active' => false]);

            return $this->successResponse(new ApiKeyResource($apiKey), 'API key revocata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore: '.$e->getMessage());
        }
    }

    /**
     * Activate an API key
     */
    public function activate(ApiKey $apiKey): JsonResponse
    {
        $this->authorize('update', $apiKey);

        try {
            $apiKey->update(['is_active' => true]);

            return $this->successResponse(new ApiKeyResource($apiKey), 'API key attivata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore: '.$e->getMessage());
        }
    }

    /**
     * Regenerate an API key
     */
    public function regenerate(ApiKey $apiKey): JsonResponse
    {
        $this->authorize('update', $apiKey);

        try {
            // Genera nuova chiave
            $plainKey = ApiKey::generate();

            $apiKey->update([
                'key' => ApiKey::hash($plainKey),
            ]);

            // Ritorna con la nuova chiave in chiaro
            $resource = (new ApiKeyResource($apiKey))->withPlainKey($plainKey);

            return $this->successResponse(
                $resource,
                'API key rigenerata con successo. ATTENZIONE: Salva questa chiave!',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore: '.$e->getMessage());
        }
    }
}
