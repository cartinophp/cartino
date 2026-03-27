<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreSettingRequest;
use Cartino\Http\Requests\Api\UpdateSettingRequest;
use Cartino\Http\Resources\SettingResource;
use Cartino\Models\Setting;
use Cartino\Repositories\SettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends ApiController
{
    public function __construct(
        private readonly SettingRepository $repository,
    ) {}

    /**
     * Display a listing of settings
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified setting
     */
    public function show(int|string $key): JsonResponse
    {
        $data = $this->repository->findOne($key);

        return $this->successResponse(new SettingResource($data));
    }

    /**
     * Store a newly created setting
     */
    public function store(StoreSettingRequest $request): JsonResponse
    {
        try {
            $setting = $this->repository->createOne($request->validated());

            return $this->created(new SettingResource($setting), 'Setting creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del setting: '.$e->getMessage());
        }
    }

    /**
     * Update the specified setting
     */
    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        try {
            $updatedSetting = $this->repository->updateOne($setting->id, $request->validated());

            return $this->successResponse(new SettingResource($updatedSetting), 'Setting aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del setting: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified setting
     */
    public function destroy(Setting $setting): JsonResponse
    {
        try {
            $this->repository->deleteOne($setting->id);

            return $this->successResponse(null, 'Setting eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del setting: '.$e->getMessage());
        }
    }
}
