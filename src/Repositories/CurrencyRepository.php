<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CurrencyRepository extends BaseRepository
{
    protected string $cachePrefix = 'currencies';

    protected function makeModel(): Model
    {
        return new Currency;
    }

    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Currency::class)
            ->allowedFilters(['name', 'code', 'symbol', 'is_enabled'])
            ->allowedSorts(['name', 'code', 'created_at'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or code
     */
    public function findOne(int|string $codeOrId): ?Currency
    {
        return $this->model
            ->where('id', $codeOrId)
            ->orWhere('code', $codeOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Currency
    {
        $currency = $this->model->create($data);
        $this->clearCache();

        return $currency;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Currency
    {
        $currency = $this->findOrFail($id);
        $currency->update($data);
        $this->clearCache();

        return $currency->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $currency = $this->findOrFail($id);
        $deleted = $currency->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $currency = $this->findOrFail($id);

        return ! $currency->is_default;
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): Currency
    {
        $currency = $this->findOrFail($id);
        $currency->update(['is_enabled' => ! $currency->is_enabled]);
        $this->clearCache();

        return $currency->fresh();
    }

    public function getEnabled(): Category
    {
        return Cache::remember(
            $this->getCacheKey('enabled', 'all'),
            $this->cacheTtl,
            function () {
                return $this->model
                    ->where('is_enabled', true)
                    ->orderBy('name')
                    ->get();
            },
        );
    }

    public function getDefault(): ?Currency
    {
        return Cache::remember(
            $this->getCacheKey('default', 'currency'),
            $this->cacheTtl,
            function () {
                return $this->model->where('is_default', true)->first();
            },
        );
    }

    public function setAsDefault(int $id): Currency
    {
        $this->model->where('is_default', true)->update(['is_default' => false]);

        $currency = $this->findOrFail($id);
        $currency->update(['is_default' => true]);

        $this->clearCache();

        return $currency->fresh();
    }

    public function convert(string $from, string $to, float $amount): array
    {
        // Mock conversion - in real app you'd use an API
        $rate = 1.0; // This should come from external API

        return [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'converted_amount' => $amount * $rate,
            'rate' => $rate,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function getRates(): array
    {
        return Cache::remember($this->getCacheKey('rates', 'all'), 3600, function () {
            // Mock rates - in real app you'd fetch from external API
            return [
                'EUR' => 1.0,
                'USD' => 1.18,
                'GBP' => 0.85,
                'JPY' => 130.0,
            ];
        });
    }
}
