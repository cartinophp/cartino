<?php

declare(strict_types=1);

namespace Cartino\Traits;

use Cartino\Models\Asset;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait HasAssets
{
    /**
     * Asset collections configuration
     * Override in model to customize
     */
    protected array $assetCollections = [
        'images' => [
            'multiple' => true,
            'max_files' => 10,
            'mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        ],
    ];

    /**
     * Get all assets for this model
     */
    public function assets(): MorphToMany
    {
        return $this->morphToMany(Asset::class, 'assetable')
            ->withPivot(['collection', 'sort_order', 'is_primary', 'is_featured', 'meta'])
            ->withTimestamps()
            ->orderBy('assetables.sort_order');
    }

    /**
     * Get assets for a specific collection
     */
    public function getAssets(string $collection = 'images'): Collection
    {
        return $this->assets()->wherePivot('collection', $collection)->get();
    }

    /**
     * Get primary asset for collection
     */
    public function getPrimaryAsset(string $collection = 'images'): ?Asset
    {
        return $this->assets()
            ->wherePivot('collection', $collection)
            ->wherePivot('is_primary', true)
            ->first();
    }

    /**
     * Get featured assets for collection
     */
    public function getFeaturedAssets(string $collection = 'images'): Collection
    {
        return $this->assets()
            ->wherePivot('collection', $collection)
            ->wherePivot('is_featured', true)
            ->get();
    }

    /**
     * Get primary image URL with optional preset
     */
    public function image(?string $preset = null): ?string
    {
        $asset = $this->getPrimaryAsset('images');

        return $asset ? $asset->glide([], $preset) : null;
    }

    /**
     * Get image URL with preset (alias)
     */
    public function imageUrl(string $preset = 'product_card'): ?string
    {
        return $this->image($preset);
    }

    /**
     * Get all image URLs with preset
     */
    public function imageUrls(string $collection = 'images', ?string $preset = null): array
    {
        return $this->getAssets($collection)->map(fn (Asset $asset) => $asset->glide([], $preset))->toArray();
    }

    /**
     * Get gallery images as array of objects
     */
    public function gallery(string $collection = 'gallery', ?string $preset = null): array
    {
        return $this->getAssets($collection)
            ->map(fn (Asset $asset) => [
                'id' => $asset->id,
                'url' => $asset->glide([], $preset),
                'alt' => $asset->alt(),
                'title' => $asset->title(),
                'width' => $asset->width,
                'height' => $asset->height,
            ])
            ->toArray();
    }

    /**
     * Attach asset to model
     */
    public function attachAsset(Asset|int $asset, string $collection = 'images', array $attributes = []): void
    {
        $assetId = ($asset instanceof Asset) ? $asset->id : $asset;

        // Check if already attached
        $exists = $this->assets()
            ->wherePivot('collection', $collection)
            ->where('asset_id', $assetId)
            ->exists();

        if ($exists) {
            return;
        }

        // Validate collection config
        $this->validateAssetCollection($collection);

        // Get current count
        $currentCount = $this->getAssets($collection)->count();
        $maxFiles = $this->assetCollections[$collection]['max_files'] ?? null;

        if ($maxFiles && $currentCount >= $maxFiles) {
            throw new \InvalidArgumentException("Collection '{$collection}' has reached maximum of {$maxFiles} files");
        }

        // Attach with attributes
        $this->assets()->attach(
            $assetId,
            array_merge(
                [
                    'collection' => $collection,
                    'sort_order' => $currentCount,
                    'is_primary' => $currentCount === 0, // First image is primary by default
                    'is_featured' => false,
                ],
                $attributes,
            ),
        );
    }

    /**
     * Attach multiple assets at once
     */
    public function attachAssets(array $assetIds, string $collection = 'images'): void
    {
        foreach ($assetIds as $index => $assetId) {
            $this->attachAsset($assetId, $collection, [
                'sort_order' => $index,
                'is_primary' => $index === 0,
            ]);
        }
    }

    /**
     * Detach asset from model
     */
    public function detachAsset(Asset|int $asset, ?string $collection = null): void
    {
        $assetId = ($asset instanceof Asset) ? $asset->id : $asset;

        $query = $this->assets()->where('asset_id', $assetId);

        if ($collection) {
            $query->wherePivot('collection', $collection);
        }

        $query->detach();

        // Reorder remaining assets in collection
        if ($collection) {
            $this->reorderAssets($collection);
        }
    }

    /**
     * Detach all assets from collection
     */
    public function detachAllAssets(?string $collection = null): void
    {
        $query = $this->assets();

        if ($collection) {
            $query->wherePivot('collection', $collection);
        }

        $query->detach();
    }

    /**
     * Set primary asset for collection
     */
    public function setPrimaryAsset(Asset|int $asset, string $collection = 'images'): void
    {
        $assetId = ($asset instanceof Asset) ? $asset->id : $asset;

        // Remove primary flag from all in collection
        $this->assets()->wherePivot('collection', $collection)->update(['assetables.is_primary' => false]);

        // Set new primary
        $this->assets()->wherePivot('collection', $collection)->updateExistingPivot($assetId, ['is_primary' => true]);
    }

    /**
     * Set featured status for asset
     */
    public function setFeaturedAsset(Asset|int $asset, string $collection = 'images', bool $featured = true): void
    {
        $assetId = ($asset instanceof Asset) ? $asset->id : $asset;

        $this->assets()->wherePivot('collection', $collection)->updateExistingPivot($assetId, [
            'is_featured' => $featured,
        ]);
    }

    /**
     * Sync assets for a collection (replace all)
     */
    public function syncAssets(array $assetIds, string $collection = 'images'): void
    {
        // Detach all current assets in collection
        $this->detachAllAssets($collection);

        // Attach new assets
        $this->attachAssets($assetIds, $collection);
    }

    /**
     * Reorder assets in collection
     */
    public function reorderAssets(string $collection = 'images', ?array $order = null): void
    {
        if ($order === null) {
            // Auto-reorder (0, 1, 2, 3...)
            $assets = $this->getAssets($collection);

            foreach ($assets as $index => $asset) {
                $this->assets()->updateExistingPivot($asset->id, ['sort_order' => $index]);
            }
        } else {
            // Custom order: ['asset_id' => sort_order]
            foreach ($order as $assetId => $sortOrder) {
                $this->assets()->wherePivot('collection', $collection)->updateExistingPivot($assetId, [
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    /**
     * Update asset metadata in pivot
     */
    public function updateAssetMeta(Asset|int $asset, array $meta, string $collection = 'images'): void
    {
        $assetId = ($asset instanceof Asset) ? $asset->id : $asset;

        $current = $this->assets()
            ->wherePivot('collection', $collection)
            ->where('asset_id', $assetId)
            ->first();

        if (! $current) {
            throw new \InvalidArgumentException('Asset not attached to this model in collection '.$collection);
        }

        $currentMeta = $current->pivot->meta ?? [];
        $newMeta = array_merge($currentMeta, $meta);

        $this->assets()->updateExistingPivot($assetId, ['meta' => $newMeta]);
    }

    /**
     * Get asset count for collection
     */
    public function assetCount(string $collection = 'images'): int
    {
        return $this->assets()->wherePivot('collection', $collection)->count();
    }

    /**
     * Check if model has assets in collection
     */
    public function hasAssets(string $collection = 'images'): bool
    {
        return $this->assetCount($collection) > 0;
    }

    /**
     * Get responsive images for primary asset
     */
    public function responsiveImage(string $collection = 'images'): ?array
    {
        $asset = $this->getPrimaryAsset($collection);

        return $asset ? $asset->responsive() : null;
    }

    /**
     * Validate collection configuration
     */
    protected function validateAssetCollection(string $collection): void
    {
        if (! isset($this->assetCollections[$collection])) {
            throw new \InvalidArgumentException(
                "Asset collection '{$collection}' is not defined in \$assetCollections",
            );
        }
    }

    /**
     * Get collection configuration
     */
    public function getAssetCollectionConfig(string $collection): ?array
    {
        return $this->assetCollections[$collection] ?? null;
    }

    /**
     * Get all defined collections
     */
    public function getAssetCollections(): array
    {
        return array_keys($this->assetCollections);
    }

    /**
     * Boot trait - register observers if needed
     */
    protected static function bootHasAssets(): void
    {
        // Delete all assets when model is deleted (optional)
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            // Only detach, don't delete physical assets
            // Assets can be shared across multiple models
            $model->assets()->detach();
        });
    }
}
