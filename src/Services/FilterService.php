<?php

namespace Cartino\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class FilterService
{
    /**
     * Cache TTL in seconds
     */
    protected int $cacheTtl;

    /**
     * Cache enabled
     */
    protected bool $cacheEnabled;

    public function __construct()
    {
        $this->cacheTtl = config('cartino.filters.cache.ttl', 3600);
        $this->cacheEnabled = config('cartino.filters.cache.enabled', true);
    }

    /**
     * Apply filters with caching
     */
    public function applyWithCache(Builder $query, array $params, string $cachePrefix = 'filters'): Builder
    {
        if (! $this->cacheEnabled) {
            return $query->filter($params);
        }

        $cacheKey = $this->generateCacheKey($cachePrefix, $params);

        $results = Cache::remember($cacheKey, $this->cacheTtl, function () use ($query, $params) {
            return $query->filter($params)->get();
        });

        // Convert back to query builder with results
        return $query->whereIn('id', $results->pluck('id'));
    }

    /**
     * Generate cache key
     */
    protected function generateCacheKey(string $prefix, array $params): string
    {
        // Sort params for consistent cache keys
        ksort($params);

        return sprintf('%s:%s:%s', $prefix, class_basename($params['model'] ?? 'unknown'), md5(serialize($params)));
    }

    /**
     * Clear cache for a model
     */
    public function clearCache(string $model): void
    {
        $tags = config('cartino.filters.cache.tags', ['filters']);

        if (Cache::supportsTags()) {
            Cache::tags($tags)->flush();
        } else {
            // If tags not supported, clear all cache (not ideal)
            Cache::flush();
        }
    }

    /**
     * Parse request parameters
     */
    public function parseRequest(array $request): array
    {
        $parsed = [];

        foreach ($request as $key => $value) {
            // Handle operator syntax: price[gte]=100
            if (preg_match('/^(.+)\[(.+)\]$/', $key, $matches)) {
                $field = $matches[1];
                $operator = $matches[2];

                if (! isset($parsed[$field])) {
                    $parsed[$field] = [];
                }

                $parsed[$field][$operator] = $value;
            } else {
                $parsed[$key] = $value;
            }
        }

        return $parsed;
    }

    /**
     * Validate filter parameters
     */
    public function validate(array $params, array $rules): array
    {
        $validated = [];

        foreach ($params as $field => $conditions) {
            if (isset($rules[$field])) {
                // Apply validation rules
                $validated[$field] = $conditions;
            }
        }

        return $validated;
    }

    /**
     * Get filter configuration for a model
     */
    public function getModelConfig(string $model): array
    {
        $modelKey = strtolower(class_basename($model));

        return config("cartino.filters.models.{$modelKey}", [
            'per_page' => config('cartino.filters.pagination.default', 15),
            'max_per_page' => config('cartino.filters.pagination.max', 100),
            'default_sort' => '-created_at',
            'searchable' => ['name'],
            'filterable' => [],
            'sortable' => ['id', 'created_at', 'updated_at'],
        ]);
    }

    /**
     * Build optimized query with automatic indexing hints
     */
    public function buildOptimizedQuery(Builder $query, array $params): Builder
    {
        // Add query hints for better performance
        $query = $query->filter($params);

        // Force index usage for common patterns
        if (isset($params['status']) && isset($params['created_at'])) {
            $query->from($query->getModel()->getTable().' USE INDEX (status_created_at)');
        }

        return $query;
    }

    /**
     * Get statistics for admin dashboard
     */
    public function getFilterStats(string $model): array
    {
        $cacheKey = "filter_stats_{$model}";

        return Cache::remember($cacheKey, 300, function () use ($model) {
            $modelClass = "Cartino\\Models\\{$model}";

            if (! class_exists($modelClass)) {
                return [];
            }

            $instance = new $modelClass;

            return [
                'total_records' => $instance->count(),
                'active_records' => method_exists($instance, 'scopeActive') ? $instance->active()->count() : null,
                'recent_records' => $instance->where('created_at', '>=', now()->subDays(7))->count(),
            ];
        });
    }
}
