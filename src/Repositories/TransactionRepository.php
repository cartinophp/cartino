<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TransactionRepository extends BaseRepository
{
    protected string $cachePrefix = 'transactions';

    protected function makeModel(): Model
    {
        return new Transaction;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Transaction::class)
            ->allowedFilters([
                'transaction_id',
                'gateway',
                'type',
                'status',
                AllowedFilter::exact('order_id'),
            ])
            ->allowedSorts(['created_at', 'amount', 'processed_at'])
            ->allowedIncludes(['order'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or transaction_id
     */
    public function findOne(int|string $transactionIdOrId): ?Transaction
    {
        return $this->model
            ->where('id', $transactionIdOrId)
            ->orWhere('transaction_id', $transactionIdOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Transaction
    {
        $transaction = $this->model->create($data);

        $this->clearCache();

        return $transaction;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Transaction
    {
        $transaction = $this->findOrFail($id);
        $transaction->update($data);

        $this->clearCache();

        return $transaction->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $transaction = $this->findOrFail($id);
        $deleted = $transaction->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Transactions can always be deleted (soft delete recommended)
    }
}
