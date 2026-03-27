<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Controllers\Api\Concerns\HasCrudActions;
use Cartino\Http\Resources\ProductResource;
use Cartino\Repositories\ProductRepository;

class ProductsController extends ApiController
{
    use HasCrudActions;

    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    protected function repository(): ProductRepository
    {
        return $this->repository;
    }

    protected function resourceClass(): string
    {
        return ProductResource::class;
    }

    protected function entityName(): string
    {
        return 'Prodotto';
    }

    // All CRUD methods (index, show, store, update, destroy, toggleStatus)
    // are now inherited from HasCrudActions trait!
    // Add only custom methods here if needed.
}
