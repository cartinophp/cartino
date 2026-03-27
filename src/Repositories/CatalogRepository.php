<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CatalogRepository extends BaseRepository
{
    protected string $cachePrefix = 'catalogs';

    protected int $cacheTtl = 3600;

    protected function makeModel(): Model
    {
        return new Catalog;
    }

    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $dynamicIncludes = Arr::get($filters, 'includes', []);
        $perPage = $filters['per_page'] ?? config('settings.pagination.per_page', 15);

        $query = QueryBuilder::for(Catalog::class)
            ->select(['catalogs.*'])
            ->allowedFilters([
                'title',
                'slug',
                'status',
                'currency',
                AllowedFilter::exact('is_default'),
                AllowedFilter::scope('published'),
                AllowedFilter::scope('active'),
                // Optimized: WHERE EXISTS instead of whereHas for better performance
                AllowedFilter::callback('site', function ($query, $value) {
                    $query->whereExists(function ($q) use ($value) {
                        $q->select(DB::raw(1))
                            ->from('catalog_site')
                            ->join('sites', 'catalog_site.site_id', '=', 'sites.id')
                            ->whereColumn('catalog_site.catalog_id', 'catalogs.id')
                            ->where(function ($q2) use ($value) {
                                $q2->where('sites.id', $value)
                                    ->orWhere('sites.slug', $value)
                                    ->orWhere('sites.handle', $value);
                            });
                    });
                }),
            ])
            ->allowedSorts(['title', 'created_at', 'published_at', 'priority'])
            ->allowedIncludes([
                'sites',
                'products',
                'variants',
                'customerGroups',
                ...$dynamicIncludes,
            ])
            ->defaultSort('-created_at');

        return $query->paginate($perPage)->appends($filters);
    }

    /**
     * Find one by ID or slug
     */
    public function findOne(int|string $slugOrId): ?Catalog
    {
        $cacheKey = "catalog:{$slugOrId}";

        return $this->cacheQuery($cacheKey, function () use ($slugOrId) {
            return $this->model
                ->with(['sites', 'products'])
                ->where('id', $slugOrId)
                ->orWhere('slug', $slugOrId)
                ->firstOrFail();
        });
    }

    /**
     * Find by slug
     */
    public function findBySlug(string $slug): ?Catalog
    {
        return Catalog::findBySlug($slug);
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Catalog
    {
        $catalog = $this->findOrFail($id);
        $catalog->update($data);
        $this->clearModelCache();

        // Optimized: Use with() for eager loading instead of fresh()
        return Catalog::with(['sites', 'products'])->findOrFail($id);
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $catalog = $this->findOrFail($id);
        $this->clearModelCache();

        return $catalog->delete();
    }

    /**
     * Get active catalogs for a specific site
     */
    public function getForSite(int|string $siteId): LengthAwarePaginator
    {
        return $this->findAll(['site' => $siteId, 'filter' => ['active' => true]]);
    }
}
