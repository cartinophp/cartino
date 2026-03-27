<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CountryRepository extends BaseRepository
{
    protected string $cachePrefix = 'countries';

    protected function makeModel(): Model
    {
        return new Country;
    }

    public function findAll(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function getEnabled(): Category
    {
        return Cache::remember(
            $this->getCacheKey('enabled', 'all'),
            $this->cacheTtl,
            function () {
                return $this->model
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            },
        );
    }

    public function getByRegion(): array
    {
        return Cache::remember(
            $this->getCacheKey('by_region', 'all'),
            $this->cacheTtl,
            function () {
                return $this->model
                    ->where('is_active', true)
                    ->get()
                    ->groupBy('region')
                    ->map(fn ($countries) => $countries->sortBy('name')->values())
                    ->toArray();
            },
        );
    }

    public function getRegions(): Category
    {
        return Cache::remember(
            $this->getCacheKey('regions', 'all'),
            $this->cacheTtl,
            function () {
                return $this->model
                    ->select('region')
                    ->distinct()
                    ->whereNotNull('region')
                    ->orderBy('region')
                    ->pluck('region');
            },
        );
    }
}
