<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PurchaseOrderRepository extends BaseRepository
{
    protected string $cachePrefix = 'purchase_orders';

    protected function makeModel(): Model
    {
        return new PurchaseOrder;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(PurchaseOrder::class)
            ->allowedFilters([
                'po_number',
                'status',
                AllowedFilter::exact('supplier_id'),
            ])
            ->allowedSorts(['po_number', 'order_date', 'expected_delivery_date', 'created_at'])
            ->allowedIncludes(['supplier', 'items'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or PO number
     */
    public function findOne(int|string $poNumberOrId): ?PurchaseOrder
    {
        return $this->model
            ->where('id', $poNumberOrId)
            ->orWhere('po_number', $poNumberOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): PurchaseOrder
    {
        $po = $this->model->create($data);

        $this->clearCache();

        return $po;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): PurchaseOrder
    {
        $po = $this->findOrFail($id);
        $po->update($data);

        $this->clearCache();

        return $po->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $po = $this->findOrFail($id);
        $deleted = $po->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $po = $this->findOrFail($id);

        return in_array($po->status, ['draft', 'cancelled']);
    }

    /**
     * Receive purchase order
     */
    public function receivePurchaseOrder(int $id): PurchaseOrder
    {
        $po = $this->findOrFail($id);
        $po->update(['status' => 'received', 'received_date' => now()]);

        $this->clearCache();

        return $po->fresh();
    }
}
