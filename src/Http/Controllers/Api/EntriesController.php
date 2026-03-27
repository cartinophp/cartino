<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreEntryRequest;
use Cartino\Http\Requests\Api\UpdateEntryRequest;
use Cartino\Http\Resources\EntryResource;
use Cartino\Models\Entry;
use Cartino\Repositories\EntryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntriesController extends ApiController
{
    public function __construct(
        private readonly EntryRepository $repository,
    ) {}

    /**
     * Display a listing of entries
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Entry::class);

        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified entry
     */
    public function show(int|string $id): JsonResponse
    {
        $data = $this->repository->findOne($id);

        if (! $data) {
            return $this->errorResponse('Entry non trovata', 404);
        }

        $this->authorize('view', $data);

        return $this->successResponse(new EntryResource($data));
    }

    /**
     * Store a newly created entry
     */
    public function store(StoreEntryRequest $request): JsonResponse
    {
        $this->authorize('create', Entry::class);

        try {
            $entry = $this->repository->createOne($request->validated());

            return $this->created(new EntryResource($entry), 'Entry creata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione dell\'entry: '.$e->getMessage());
        }
    }

    /**
     * Update the specified entry
     */
    public function update(UpdateEntryRequest $request, Entry $entry): JsonResponse
    {
        $this->authorize('update', $entry);

        try {
            $updatedEntry = $this->repository->updateOne($entry->id, $request->validated());

            return $this->successResponse(new EntryResource($updatedEntry), 'Entry aggiornata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento dell\'entry: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified entry
     */
    public function destroy(Entry $entry): JsonResponse
    {
        $this->authorize('delete', $entry);

        try {
            if (! $this->repository->canDelete($entry->id)) {
                return $this->errorResponse('Impossibile eliminare l\'entry: ha delle entry figlie associate', 422);
            }

            $this->repository->deleteOne($entry->id);

            return $this->successResponse(null, 'Entry eliminata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione dell\'entry: '.$e->getMessage());
        }
    }

    /**
     * Get entries by collection
     */
    public function byCollection(Request $request, string $collection): JsonResponse
    {
        $data = $this->repository->getByCollection($collection, $request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Get entry by slug in collection
     */
    public function bySlug(string $collection, string $slug): JsonResponse
    {
        $locale = request()->get('locale', 'it');
        $entry = $this->repository->findBySlug($collection, $slug, $locale);

        if (! $entry) {
            return $this->errorResponse('Entry non trovata', 404);
        }

        return $this->successResponse(new EntryResource($entry));
    }

    /**
     * Publish an entry
     */
    public function publish(Request $request, Entry $entry): JsonResponse
    {
        $this->authorize('publish', $entry);

        $request->validate([
            'published_at' => ['nullable', 'date'],
        ]);

        try {
            $publishedAt = $request->published_at ? new \DateTime($request->published_at) : null;
            $publishedEntry = $this->repository->publish($entry->id, $publishedAt);

            return $this->successResponse(new EntryResource($publishedEntry), 'Entry pubblicata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella pubblicazione dell\'entry: '.$e->getMessage());
        }
    }

    /**
     * Unpublish an entry
     */
    public function unpublish(Entry $entry): JsonResponse
    {
        $this->authorize('unpublish', $entry);

        try {
            $unpublishedEntry = $this->repository->unpublish($entry->id);

            return $this->successResponse(new EntryResource($unpublishedEntry), 'Entry rimossa dalla pubblicazione');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore: '.$e->getMessage());
        }
    }

    /**
     * Schedule an entry for future publication
     */
    public function schedule(Request $request, Entry $entry): JsonResponse
    {
        $this->authorize('schedule', $entry);

        $request->validate([
            'published_at' => ['required', 'date', 'after:now'],
        ]);

        try {
            $publishedAt = new \DateTime($request->published_at);
            $scheduledEntry = $this->repository->schedule($entry->id, $publishedAt);

            return $this->successResponse(new EntryResource($scheduledEntry), 'Entry schedulata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella schedulazione dell\'entry: '.$e->getMessage());
        }
    }

    /**
     * Reorder entries
     */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('reorder', Entry::class);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:entries,id'],
        ]);

        try {
            $this->repository->reorder($request->order);

            return $this->successResponse(null, 'Entries riordinate con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel riordinamento: '.$e->getMessage());
        }
    }

    /**
     * Get tree structure for hierarchical entries
     */
    public function tree(Request $request, string $collection): JsonResponse
    {
        $this->authorize('viewAny', Entry::class);

        $locale = $request->get('locale', 'it');
        $tree = $this->repository->getTree($collection, $locale);

        return $this->successResponse(EntryResource::collection($tree));
    }

    /**
     * Duplicate an entry
     */
    public function duplicate(Entry $entry): JsonResponse
    {
        $this->authorize('duplicate', $entry);

        try {
            $duplicate = $this->repository->duplicate($entry->id);

            return $this->created(new EntryResource($duplicate), 'Entry duplicata con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella duplicazione dell\'entry: '.$e->getMessage());
        }
    }
}
