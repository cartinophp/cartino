<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreChannelRequest;
use Cartino\Http\Requests\Api\UpdateChannelRequest;
use Cartino\Http\Resources\ChannelResource;
use Cartino\Models\Channel;
use Cartino\Repositories\ChannelRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelsController extends ApiController
{
    public function __construct(
        private readonly ChannelRepository $repository,
    ) {}

    /**
     * Display a listing of channels
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified channel
     */
    public function show(int|string $slug): JsonResponse
    {
        $data = $this->repository->findOne($slug);

        return $this->successResponse(new ChannelResource($data));
    }

    /**
     * Store a newly created channel
     */
    public function store(StoreChannelRequest $request): JsonResponse
    {
        try {
            $channel = $this->repository->createOne($request->validated());

            return $this->created(new ChannelResource($channel), 'Channel creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del channel: '.$e->getMessage());
        }
    }

    /**
     * Update the specified channel
     */
    public function update(UpdateChannelRequest $request, Channel $channel): JsonResponse
    {
        try {
            $updatedChannel = $this->repository->updateOne($channel->id, $request->validated());

            return $this->successResponse(new ChannelResource($updatedChannel), 'Channel aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del channel: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified channel
     */
    public function destroy(Channel $channel): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($channel->id)) {
                return $this->errorResponse('Impossibile eliminare il channel: è il channel di default', 422);
            }

            $this->repository->deleteOne($channel->id);

            return $this->successResponse(null, 'Channel eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del channel: '.$e->getMessage());
        }
    }

    /**
     * Toggle channel status
     */
    public function toggleStatus(Channel $channel): JsonResponse
    {
        try {
            $updatedChannel = $this->repository->toggleStatus($channel->id);

            return $this->successResponse(new ChannelResource($updatedChannel), 'Stato del channel aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
