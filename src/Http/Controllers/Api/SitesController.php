<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\DTO\BrandDto;
use Cartino\DTO\BulkOperationDTO;
use Cartino\Http\Requests\Api\BulkActionRequest;
use Cartino\Http\Requests\Api\CreateManyBrandsRequest;
use Cartino\Http\Requests\Api\DestroyManyBrandsRequest;
use Cartino\Http\Requests\Api\StoreBrandRequest;
use Cartino\Http\Requests\Api\UpdateBrandRequest;
use Cartino\Http\Requests\Api\UpdateManyBrandsRequest;
use Cartino\Http\Resources\BrandResource;
use Cartino\Models\Site;
use Cartino\Repositories\SiteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SitesController extends ApiController
{
    public function __construct(
        private readonly SiteRepository $repository,
    ) {}

    /**
     * Display a listing of brands
     */
    public function index(Request $request): JsonResponse
    {
        $request = $request->all();

        $data = $this->repository->findAll($request);

        return $this->paginatedResponse($data);
    }

    /**
     * Store a newly created brand
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        try {
            $brand = $this->repository->createOne($request->validated());

            return $this->created(new BrandResource($brand), 'Site creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del brand: '.$e->getMessage());
        }
    }

    /**
     * Store multiple brands
     */
    public function createMany(CreateManyBrandsRequest $request): JsonResponse
    {
        try {
            $brandDTOs = collect($request->getBrandsData())
                ->map(fn (array $data) => BrandDto::fromArray($data))
                ->toArray();

            $brandsData = collect($brandDTOs)->map(fn (BrandDto $dto) => $dto->toCreateArray())->toArray();

            $brands = $this->repository->createMany($brandsData);

            return $this->created(BrandResource::collection($brands), count($brands).' brand creati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione multipla dei brand: '.$e->getMessage());
        }
    }

    /**
     * Display the specified brand
     */
    public function show(int|string $handle): JsonResponse
    {
        $data = $this->repository->findOne($handle);

        return $this->successResponse(new BrandResource($data));
    }

    /**
     * Update the specified brand
     */
    public function update(UpdateBrandRequest $request, Site $brand): JsonResponse
    {
        try {
            $updatedBrand = $this->repository->updateOne($brand->id, $request->validated());

            return $this->successResponse(new BrandResource($updatedBrand), 'Site aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del brand: '.$e->getMessage());
        }
    }

    /**
     * Update multiple brands
     */
    public function updateMany(UpdateManyBrandsRequest $request): JsonResponse
    {
        try {
            $bulkOperation = BulkOperationDTO::forUpdate($request->getIds(), $request->getUpdateData());

            $count = $this->repository->updateMany($bulkOperation->getIds(), $bulkOperation->getData());

            return $this->successResponse(['updated_count' => $count], $count.' brand aggiornati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento multiplo dei brand: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified brand
     */
    public function destroy(Site $brand): JsonResponse
    {
        try {
            if (! $this->repository->canDelete($brand->id)) {
                return $this->errorResponse('Impossibile eliminare il brand: è associato a dei prodotti', 422);
            }

            $this->repository->deleteOne($brand->id);

            return $this->successResponse(null, 'Site eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del brand: '.$e->getMessage());
        }
    }

    /**
     * Remove multiple brands
     */
    public function destroyMany(DestroyManyBrandsRequest $request): JsonResponse
    {
        try {
            $bulkOperation = BulkOperationDTO::forDelete($request->getIds(), $request->isForceDelete());

            $errors = [];
            $validIds = [];

            foreach ($bulkOperation->getIds() as $id) {
                if ($bulkOperation->isForceOperation() || $this->repository->canDelete($id)) {
                    $validIds[] = $id;
                } else {
                    $errors[] = "Site ID {$id} non può essere eliminato: è associato a dei prodotti";
                }
            }

            $deleted = empty($validIds) ? 0 : $this->repository->deleteMany($validIds);

            $response = ['deleted_count' => $deleted];
            if (! empty($errors)) {
                $response['errors'] = $errors;
                $response['skipped_count'] = count($errors);
            }

            return $this->successResponse(
                $response,
                $deleted.
                ' brand eliminati con successo'.
                (! empty($errors) ? (' ('.count($errors).' saltati)') : ''),
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione multipla dei brand: '.$e->getMessage());
        }
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(Site $brand): JsonResponse
    {
        try {
            $updatedBrand = $this->repository->toggleStatus($brand->id);

            return $this->successResponse(new BrandResource($updatedBrand), 'Stato del brand aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }

    /**
     * Get brand products
     */
    public function products(Site $brand): JsonResponse
    {
        $products = $this->repository->getBrandProducts($brand->id);

        return $this->successResponse($products);
    }

    /**
     * Bulk activate brands
     */
    public function bulkActivate(BulkActionRequest $request): JsonResponse
    {
        try {
            $bulkOperation = BulkOperationDTO::forUpdate($request->getIds(), ['status' => 'active']);

            $count = $this->repository->updateMany($bulkOperation->getIds(), $bulkOperation->getData());

            return $this->successResponse(['activated_count' => $count], $count.' brand attivati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'attivazione multipla dei brand: '.$e->getMessage());
        }
    }

    /**
     * Bulk deactivate brands
     */
    public function bulkDeactivate(BulkActionRequest $request): JsonResponse
    {
        try {
            $bulkOperation = BulkOperationDTO::forUpdate($request->getIds(), ['status' => 'inactive']);

            $count = $this->repository->updateMany($bulkOperation->getIds(), $bulkOperation->getData());

            return $this->successResponse(['deactivated_count' => $count], $count.' brand disattivati con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella disattivazione multipla dei brand: '.$e->getMessage());
        }
    }

    /**
     * Bulk delete brands
     */
    public function bulkDelete(BulkActionRequest $request): JsonResponse
    {
        try {
            $ids = $request->getIds();
            $errors = [];
            $validIds = [];

            foreach ($ids as $id) {
                if ($this->repository->canDelete($id)) {
                    $validIds[] = $id;
                } else {
                    $errors[] = "Site ID {$id} non può essere eliminato: è associato a dei prodotti";
                }
            }

            $deleted = empty($validIds) ? 0 : $this->repository->deleteMany($validIds);

            $response = ['deleted_count' => $deleted];
            if (! empty($errors)) {
                $response['errors'] = $errors;
                $response['skipped_count'] = count($errors);
            }

            return $this->successResponse(
                $response,
                $deleted.
                ' brand eliminati con successo'.
                (! empty($errors) ? (' ('.count($errors).' saltati)') : ''),
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione multipla dei brand: '.$e->getMessage());
        }
    }

    /**
     * Bulk export brands
     */
    public function bulkExport(BulkActionRequest $request): JsonResponse
    {
        try {
            $brands = $this->repository->findByIds($request->getIds());

            // Qui potresti implementare l'export in CSV, Excel, etc.
            $exportData = $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'status' => $brand->status,
                    'created_at' => $brand->created_at,
                ];
            });

            return $this->successResponse(
                ['export_data' => $exportData, 'total_exported' => $exportData->count()],
                $exportData->count().' brand esportati con successo',
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'esportazione dei brand: '.$e->getMessage());
        }
    }
}
