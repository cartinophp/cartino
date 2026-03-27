<?php

declare(strict_types=1);

namespace Cartino\Contracts;

use Cartino\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SupplierRepositoryInterface extends RepositoryInterface
{
    public function findAll(array $filters = []): LengthAwarePaginator;

    public function findByCode(string $code): ?Supplier;

    public function getActive(): Category;

    public function getPreferred(): Category;

    public function getByCountry(string $countryCode): Category;

    public function getByPriority(string $priority): Category;

    public function getByRating(float $minRating): Category;

    public function updateRating(int $id, float $rating): bool;

    public function getTopPerformers(int $limit = 10): Category;

    public function getWithProducts(int $id): ?Supplier;

    public function getWithPurchaseOrders(int $id): ?Supplier;

    public function bulkUpdateStatus(array $ids, string $status): int;

    public function getSupplierProducts(int $supplierId): Category;

    public function getSupplierPurchaseOrders(int $supplierId): Category;

    public function calculatePerformanceMetrics(int $id): array;

    public function toggleStatus(int $id): Supplier;

    public function canDelete(int $id): bool;
}
