<?php

namespace Cartino\Repositories;

use Cartino\Models\TaxRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TaxRateRepository extends BaseRepository
{
    protected string $cachePrefix = 'tax_rates';

    protected function makeModel(): Model
    {
        return new TaxRate;
    }

    /**
     * Get paginated tax rates with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(TaxRate::class)
            ->allowedFilters([
                'name',
                'code',
                'type',
                AllowedFilter::exact('is_enabled'),
            ])
            ->allowedSorts(['name', 'code', 'rate', 'created_at'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or code
     */
    public function findOne(int|string $codeOrId): ?TaxRate
    {
        return $this->model
            ->where('id', $codeOrId)
            ->orWhere('code', $codeOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): TaxRate
    {
        $taxRate = $this->model->create($data);
        $this->clearCache();

        return $taxRate;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): TaxRate
    {
        $taxRate = $this->findOrFail($id);
        $taxRate->update($data);
        $this->clearCache();

        return $taxRate->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $taxRate = $this->findOrFail($id);
        $deleted = $taxRate->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Tax rates can always be deleted
    }

    /**
     * Toggle tax rate status
     */
    public function toggleStatus(int $id): TaxRate
    {
        $taxRate = $this->findOrFail($id);
        $taxRate->update(['is_enabled' => ! $taxRate->is_enabled]);
        $this->clearCache();

        return $taxRate->fresh();
    }

    /**
     * Get active tax rates for a location
     */
    public function getActiveForLocation(
        string $countryCode,
        ?string $stateCode = null,
        ?string $postcode = null,
    ): \Illuminate\Database\Eloquent\Collection {
        $cacheKey = $this->getCacheKey('location', md5($countryCode.'_'.$stateCode.'_'.$postcode));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use (
            $countryCode,
            $stateCode,
            $postcode,
        ) {
            $query = $this->model->active();

            // Filter by country
            $query->where(function ($q) use ($countryCode) {
                $q->whereJsonContains('countries', strtoupper($countryCode))->orWhereNull('countries');
            });

            // Filter by state if provided
            if ($stateCode) {
                $query->where(function ($q) use ($countryCode, $stateCode) {
                    $stateKey = strtoupper($countryCode.'_'.$stateCode);
                    $q->whereJsonContains('states', $stateKey)->orWhereNull('states');
                });
            }

            // Filter by postcode if provided
            if ($postcode) {
                $query->where(function ($q) use ($postcode) {
                    $q->whereNull('postcodes');

                    // Check postcode patterns
                    $taxRates = $this->model
                        ->active()
                        ->whereNotNull('postcodes')
                        ->get();
                    $matchingIds = [];

                    foreach ($taxRates as $rate) {
                        foreach ($rate->postcodes as $pattern) {
                            if (fnmatch($pattern, $postcode)) {
                                $matchingIds[] = $rate->id;
                                break;
                            }
                        }
                    }

                    if (! empty($matchingIds)) {
                        $q->orWhereIn('id', $matchingIds);
                    }
                });
            }

            return $query->orderBy('rate', 'desc')->get();
        });
    }

    /**
     * Calculate tax for an amount
     */
    public function calculateTax(
        float $amount,
        string $countryCode,
        ?string $stateCode = null,
        ?string $postcode = null,
        array $productCategories = [],
    ): array {
        $taxRates = $this->getActiveForLocation($countryCode, $stateCode, $postcode);

        $totalTax = 0;
        $appliedRates = [];
        $currentAmount = $amount;

        foreach ($taxRates as $rate) {
            // Check if tax applies to product categories
            if (! empty($rate->product_categories) && ! empty($productCategories)) {
                $hasMatchingCategory = ! empty(array_intersect($rate->product_categories, $productCategories));
                if (! $hasMatchingCategory) {
                    continue;
                }
            }

            // Check amount thresholds
            if ($rate->min_amount && $amount < $rate->min_amount) {
                continue;
            }

            if ($rate->max_amount && $amount > $rate->max_amount) {
                continue;
            }

            $taxAmount = 0;

            if ($rate->type === 'percentage') {
                $baseAmount = $rate->is_compound ? $currentAmount : $amount;
                $taxAmount = $baseAmount * $rate->rate;
            } else {
                $taxAmount = $rate->rate;
            }

            $totalTax += $taxAmount;

            if ($rate->is_compound) {
                $currentAmount += $taxAmount;
            }

            $appliedRates[] = [
                'id' => $rate->id,
                'name' => $rate->name,
                'code' => $rate->code,
                'rate' => $rate->rate,
                'type' => $rate->type,
                'amount' => round($taxAmount, 2),
                'is_inclusive' => $rate->is_inclusive,
                'is_compound' => $rate->is_compound,
            ];
        }

        return [
            'total_tax' => round($totalTax, 2),
            'applied_rates' => $appliedRates,
            'tax_inclusive_amount' => round($amount + $totalTax, 2),
        ];
    }

    /**
     * Create a new tax rate
     */
    public function create(array $data): TaxRate
    {
        $this->clearCache();

        return $this->model->create($data);
    }

    /**
     * Update tax rate
     */
    public function update(int $id, array $attributes): Model
    {
        $this->clearCache();

        $taxRate = $this->model->find($id);
        $taxRate->update($attributes);

        return $taxRate;
    }

    /**
     * Delete tax rate
     */
    public function delete(int $id): bool
    {
        $this->clearCache();

        return $this->model->find($id)->delete();
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }

    /**
     * Get all countries with tax rates
     */
    public function getCountries(): Collection
    {
        $cacheKey = $this->getCacheKey('countries', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return collect([
                'IT' => 'Italia',
                'FR' => 'Francia',
                'DE' => 'Germania',
                'ES' => 'Spagna',
                'US' => 'Stati Uniti',
                'CA' => 'Canada',
                'GB' => 'Regno Unito',
                'AU' => 'Australia',
                'JP' => 'Giappone',
            ]);
        });
    }

    /**
     * Get tax zones for dropdown
     */
    public function getTaxZones(): Collection
    {
        $cacheKey = $this->getCacheKey('tax_zones', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return collect([
                ['id' => 1, 'name' => 'Europa', 'countries' => ['IT', 'FR', 'DE', 'ES']],
                ['id' => 2, 'name' => 'Nord America', 'countries' => ['US', 'CA']],
                ['id' => 3, 'name' => 'Asia-Pacifico', 'countries' => ['JP', 'AU', 'SG']],
            ]);
        });
    }

    /**
     * Update priorities for multiple tax rates
     */
    public function updatePriorities(array $taxRates): bool
    {
        try {
            foreach ($taxRates as $taxRateData) {
                $this->model->where('id', $taxRateData['id'])->update(['priority' => $taxRateData['priority']]);
            }

            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Duplicate a tax rate
     */
    public function duplicate(int $id): ?TaxRate
    {
        $originalTaxRate = $this->model->find($id);

        if (! $originalTaxRate) {
            return null;
        }

        $duplicatedData = $originalTaxRate->toArray();
        unset($duplicatedData['id'], $duplicatedData['created_at'], $duplicatedData['updated_at']);
        $duplicatedData['name'] = $duplicatedData['name'].' (Copia)';

        return $this->create($duplicatedData);
    }

    /**
     * Get applicable tax rates based on conditions
     */
    public function getApplicableRates(array $conditions = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery()->where('is_active', true);

        if (isset($conditions['country_code'])) {
            $query->whereJsonContains('countries', strtoupper($conditions['country_code']));
        }

        if (isset($conditions['state_code'])) {
            $stateKey = strtoupper($conditions['country_code'].'_'.$conditions['state_code']);
            $query->whereJsonContains('states', $stateKey);
        }

        if (isset($conditions['category_ids'])) {
            $query->where(function ($q) use ($conditions) {
                $q->whereNull('product_categories');
                foreach ($conditions['category_ids'] as $categoryId) {
                    $q->orWhereJsonContains('product_categories', $categoryId);
                }
            });
        }

        if (isset($conditions['amount'])) {
            $query->where(function ($q) use ($conditions) {
                $amount = $conditions['amount'];
                $q->where(function ($subQuery) use ($amount) {
                    $subQuery->whereNull('min_amount')->orWhere('min_amount', '<=', $amount);
                })->where(function ($subQuery) use ($amount) {
                    $subQuery->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
                });
            });
        }

        $cacheKey = $this->getCacheKey('applicable', md5(serialize($conditions)));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($query) {
            return $query->orderBy('priority', 'desc')->get();
        });
    }

    /**
     * Bulk action for tax rates
     */
    public function bulkAction(string $action, array $ids, array $metadata = []): array
    {
        $validatedIds = $this->model
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
        $processedCount = 0;
        $errors = [];

        foreach ($validatedIds as $id) {
            try {
                switch ($action) {
                    case 'activate':
                        $this->model->where('id', $id)->update(['is_active' => true]);
                        $processedCount++;
                        break;

                    case 'deactivate':
                        $this->model->where('id', $id)->update(['is_active' => false]);
                        $processedCount++;
                        break;

                    case 'delete':
                        $this->model->where('id', $id)->delete();
                        $processedCount++;
                        break;

                    case 'update_rate':
                        if (isset($metadata['rate'])) {
                            $this->model->where('id', $id)->update(['rate' => $metadata['rate']]);
                            $processedCount++;
                        } else {
                            $errors[] = "Tax rate ID {$id}: Missing rate value";
                        }
                        break;

                    case 'update_priority':
                        if (isset($metadata['priority'])) {
                            $this->model->where('id', $id)->update(['priority' => $metadata['priority']]);
                            $processedCount++;
                        } else {
                            $errors[] = "Tax rate ID {$id}: Missing priority value";
                        }
                        break;

                    default:
                        $errors[] = "Tax rate ID {$id}: Unknown action '{$action}'";
                }
            } catch (\Exception $e) {
                $errors[] = "Tax rate ID {$id}: {$e->getMessage()}";
            }
        }

        $this->clearCache();

        return [
            'processed' => $processedCount,
            'total' => count($ids),
            'errors' => $errors,
            'success' => count($errors) === 0,
        ];
    }
}
