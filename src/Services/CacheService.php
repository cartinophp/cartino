<?php

namespace Cartino\Services;

use Cartino\Models\Brand;
use Cartino\Models\Category;
use Cartino\Models\Product;
use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    // Cache TTL constants (in seconds)
    const PRODUCT_TTL = 3600; // 1 hour

    const BRAND_TTL = 7200; // 2 hours

    const COLLECTION_TTL = 3600; // 1 hour

    const USER_TTL = 1800; // 30 minutes

    const SETTINGS_TTL = 86400; // 24 hours

    const NAVIGATION_TTL = 3600; // 1 hour

    const STATS_TTL = 600; // 10 minutes

    // Cache tags for better invalidation
    const PRODUCT_TAG = 'products';

    const BRAND_TAG = 'brands';

    const COLLECTION_TAG = 'collections';

    const USER_TAG = 'users';

    const SETTINGS_TAG = 'settings';

    const NAVIGATION_TAG = 'navigation';

    const STATS_TAG = 'stats';

    protected bool $tagsSupported;

    public function __construct()
    {
        $this->tagsSupported = method_exists(Cache::getStore(), 'tags');
    }

    /**
     * Cache product data with appropriate tags and TTL
     */
    public function rememberProduct(mixed $key, Closure $callback, int $ttl = self::PRODUCT_TTL): mixed
    {
        $cacheKey = $this->formatKey(self::PRODUCT_TAG, $key);

        if ($this->tagsSupported) {
            return Cache::tags([self::PRODUCT_TAG])->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache brand data
     */
    public function rememberBrand(mixed $key, Closure $callback, int $ttl = self::BRAND_TTL): mixed
    {
        $cacheKey = $this->formatKey(self::BRAND_TAG, $key);

        if ($this->tagsSupported) {
            return Cache::tags([self::BRAND_TAG])->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache collection data
     */
    public function rememberCollection(mixed $key, Closure $callback, int $ttl = self::COLLECTION_TTL): mixed
    {
        $cacheKey = $this->formatKey(self::COLLECTION_TAG, $key);

        if ($this->tagsSupported) {
            return Cache::tags([self::COLLECTION_TAG])->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache user data
     */
    public function rememberUser(mixed $key, Closure $callback, int $ttl = self::USER_TTL): mixed
    {
        $cacheKey = $this->formatKey(self::USER_TAG, $key);

        if ($this->tagsSupported) {
            return Cache::tags([self::USER_TAG])->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache settings data
     */
    public function rememberSettings(mixed $key, Closure $callback, int $ttl = self::SETTINGS_TTL): mixed
    {
        $cacheKey = $this->formatKey(self::SETTINGS_TAG, $key);

        if ($this->tagsSupported) {
            return Cache::tags([self::SETTINGS_TAG])->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache navigation data
     */
    public function rememberNavigation(mixed $key, Closure $callback, int $ttl = self::NAVIGATION_TTL): mixed
    {
        $cacheKey = $this->formatKey(self::NAVIGATION_TAG, $key);

        if ($this->tagsSupported) {
            return Cache::tags([self::NAVIGATION_TAG])->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache statistics data
     */
    public function rememberStats(mixed $key, Closure $callback, int $ttl = self::STATS_TTL): mixed
    {
        $cacheKey = $this->formatKey(self::STATS_TAG, $key);

        if ($this->tagsSupported) {
            return Cache::tags([self::STATS_TAG])->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Invalidate product cache
     */
    public function invalidateProduct(?int $productId = null): void
    {
        if ($this->tagsSupported) {
            $tags = [self::PRODUCT_TAG];

            if ($productId) {
                $tags[] = "product_{$productId}";
            }

            Cache::tags($tags)->flush();
        } else {
            $this->clearPattern('product_*');
        }
    }

    /**
     * Invalidate brand cache
     */
    public function invalidateBrand(?int $brandId = null): void
    {
        if ($this->tagsSupported) {
            $tags = [self::BRAND_TAG];

            if ($brandId) {
                $tags[] = "brand_{$brandId}";
            }

            Cache::tags($tags)->flush();
        } else {
            $this->clearPattern('brand_*');
        }

        // Also invalidate products as they depend on brands
        $this->invalidateProduct();
    }

    /**
     * Invalidate collection cache
     */
    public function invalidateCollection(?int $collectionId = null): void
    {
        if ($this->tagsSupported) {
            $tags = [self::COLLECTION_TAG];

            if ($collectionId) {
                $tags[] = "collection_{$collectionId}";
            }

            Cache::tags($tags)->flush();
        } else {
            $this->clearPattern('collection_*');
        }
    }

    /**
     * Invalidate user cache
     */
    public function invalidateUser(?int $userId = null): void
    {
        if ($this->tagsSupported) {
            $tags = [self::USER_TAG];

            if ($userId) {
                $tags[] = "user_{$userId}";
            }

            Cache::tags($tags)->flush();
        } else {
            $this->clearPattern('user_*');
        }
    }

    /**
     * Invalidate settings cache
     */
    public function invalidateSettings(): void
    {
        if ($this->tagsSupported) {
            Cache::tags([self::SETTINGS_TAG])->flush();
        } else {
            $this->clearPattern('settings_*');
        }
    }

    /**
     * Invalidate navigation cache
     */
    public function invalidateNavigation(): void
    {
        if ($this->tagsSupported) {
            Cache::tags([self::NAVIGATION_TAG])->flush();
        } else {
            $this->clearPattern('navigation_*');
        }
    }

    /**
     * Invalidate statistics cache
     */
    public function invalidateStats(): void
    {
        if ($this->tagsSupported) {
            Cache::tags([self::STATS_TAG])->flush();
        } else {
            $this->clearPattern('stats_*');
        }
    }

    /**
     * Clear all Shopper-related cache
     */
    public function clearAll(): void
    {
        if ($this->tagsSupported) {
            Cache::tags([
                self::PRODUCT_TAG,
                self::BRAND_TAG,
                self::COLLECTION_TAG,
                self::USER_TAG,
                self::SETTINGS_TAG,
                self::NAVIGATION_TAG,
                self::STATS_TAG,
            ])->flush();
        } else {
            $this->clearPattern('shopper_*');
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        // This would need to be implemented based on your cache driver
        // For Redis, you could use Redis commands to get stats
        return [
            'driver' => config('cache.default'),
            'tags_supported' => $this->tagsSupported,
            'total_keys' => $this->getTotalKeys(),
            'memory_usage' => $this->getMemoryUsage(),
        ];
    }

    /**
     * Warm up frequently used cache
     */
    public function warmUp(): void
    {
        // Warm up brands
        $this->rememberBrand('all', function () {
            return Brand::all();
        });

        // Warm up featured products
        $this->rememberProduct('featured', function () {
            return Product::where('is_featured', true)->with(['brand'])->get();
        });

        // Warm up navigation (using collections instead of categories)
        $this->rememberNavigation('main', function () {
            return Category::orderBy('sort_order')->get();
        });
    }

    /**
     * Format cache key
     */
    protected function formatKey(string $prefix, mixed $key): string
    {
        return "shopper_{$prefix}_{$key}";
    }

    /**
     * Clear cache by pattern (fallback for non-tag supporting drivers)
     */
    protected function clearPattern(string $pattern): void
    {
        // Implementation depends on cache driver
        // For file cache, you'd scan files
        // For Redis, you'd use SCAN command
        // This is a simplified version
        Cache::flush();
    }

    /**
     * Get total number of cache keys
     */
    protected function getTotalKeys(): int
    {
        // Implementation depends on cache driver
        return 0;
    }

    /**
     * Get memory usage
     */
    protected function getMemoryUsage(): string
    {
        // Implementation depends on cache driver
        return '0 MB';
    }
}
