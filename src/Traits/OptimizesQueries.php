<?php

declare(strict_types=1);

namespace Cartino\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\LazyCollection;

/**
 * Trait per ottimizzare performance delle query con caching e eager loading
 */
trait OptimizesQueries
{
    /**
     * Cache key prefix per questo model
     */
    protected function getCachePrefix(): string
    {
        return strtolower(class_basename($this->model ?? static::class));
    }

    /**
     * Get cache TTL in secondi
     */
    protected function getCacheTTL(): int
    {
        return property_exists($this, 'cacheTTL') ? $this->cacheTTL : 3600;
    }

    /**
     * Cache una query con tags
     */
    protected function cacheQuery(string $key, \Closure $callback, ?int $ttl = null)
    {
        $fullKey = $this->getCachePrefix().':'.$key;
        $ttl = $ttl ?? $this->getCacheTTL();

        if (Cache::supportsTags()) {
            return Cache::tags([$this->getCachePrefix()])->remember($fullKey, $ttl, $callback);
        }

        return Cache::remember($fullKey, $ttl, $callback);
    }

    /**
     * Clear cache per questo model
     */
    protected function clearModelCache(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([$this->getCachePrefix()])->flush();
        }
    }

    /**
     * Applica eager loading intelligente
     */
    protected function withOptimizedRelations(Builder $query, array $relations = []): Builder
    {
        if (empty($relations)) {
            return $query;
        }

        // Eager load con count solo quando necessario
        $withCount = [];
        $with = [];

        foreach ($relations as $relation) {
            if (str_ends_with($relation, '_count')) {
                $withCount[] = str_replace('_count', '', $relation);
            } else {
                $with[] = $relation;
            }
        }

        if (! empty($with)) {
            $query->with($with);
        }

        if (! empty($withCount)) {
            $query->withCount($withCount);
        }

        return $query;
    }

    /**
     * Applica select ottimizzato (evita SELECT *)
     */
    protected function selectOptimized(Builder $query, ?array $fields = null): Builder
    {
        if ($fields === null) {
            return $query;
        }

        // Assicurati sempre di includere la chiave primaria
        if (! in_array('id', $fields) && ! in_array('*', $fields)) {
            array_unshift($fields, 'id');
        }

        return $query->select($fields);
    }

    /**
     * Chunk processing per grandi dataset
     */
    protected function processInChunks(Builder $query, \Closure $callback, int $chunkSize = 100): void
    {
        $query->chunk($chunkSize, function ($items) use ($callback) {
            foreach ($items as $item) {
                $callback($item);
            }
        });
    }

    /**
     * Lazy collection per memory efficiency
     */
    protected function getLazyCollection(Builder $query, int $chunkSize = 100): LazyCollection
    {
        return $query->lazy($chunkSize);
    }

    /**
     * Batch insert ottimizzato
     */
    protected function batchInsert(array $data, int $batchSize = 500): bool
    {
        $chunks = array_chunk($data, $batchSize);

        foreach ($chunks as $chunk) {
            $this->model->insert($chunk);
        }

        $this->clearModelCache();

        return true;
    }

    /**
     * Scope per evitare N+1 con exists checks
     */
    protected function withExistsCheck(Builder $query, string $relation, ?string $as = null): Builder
    {
        $as = $as ?? "has_{$relation}";

        return $query->addSelect([
            $as => function ($query) use ($relation) {
                return $query
                    ->selectRaw('1')
                    ->from($relation)
                    ->whereColumn($this->model->getTable().'.id', $relation.'.'.$this->model->getForeignKey())
                    ->limit(1);
            },
        ]);
    }

    /**
     * Prefetch IDs per batch loading
     */
    protected function prefetchIds(Builder $query): array
    {
        return $query->pluck('id')->toArray();
    }

    /**
     * Load missing relations evitando N+1
     */
    protected function loadMissingOptimized($collection, array $relations): void
    {
        if ($collection instanceof Collection) {
            $collection->loadMissing($relations);
        }
    }
}
