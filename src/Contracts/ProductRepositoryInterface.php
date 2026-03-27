<?php

declare(strict_types=1);

namespace Cartino\Contracts;

use Cartino\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findWithRelations(int $id, array $relations = []): ?Product;

    public function findAll(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function createWithRelations(array $data, array $relations = []): Product;

    public function getByCategory(int $categoryId): Category;

    public function getByBrand(int $brandId): Category;

    public function getByCollection(int $collectionId): Category;

    public function getPublished(): Category;

    public function getVisible(): Category;

    public function getOnSale(): Category;

    public function getFeatured(): Category;

    public function getPopular(int $limit = 10): Category;

    public function getRelated(Product $product, int $limit = 4): Category;

    public function getBySku(string $sku): ?Product;

    public function searchByName(string $name): Category;

    public function filterByPrice(int $minPrice, int $maxPrice): static;

    public function sortByPopularity(): static;

    public function sortByNewest(): static;

    public function sortByPrice(string $direction = 'asc'): static;
}
