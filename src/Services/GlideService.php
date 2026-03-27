<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\Asset;
use Cartino\Models\AssetTransformation;
use Illuminate\Support\Facades\Storage;
use League\Glide\Server;
use League\Glide\ServerFactory;

class GlideService
{
    protected Server $server;

    public function __construct()
    {
        $this->server = ServerFactory::create([
            'source' => Storage::disk(config('media.glide.source_disk'))->path(''),
            'cache' => Storage::disk(config('media.glide.cache_disk'))->path(config('media.glide.cache_path')),
            'max_image_size' => config('media.glide.max_width') * config('media.glide.max_height'),
            'defaults' => [
                'q' => config('media.glide.default_quality'),
                'fm' => config('media.glide.default_format'),
            ],
        ]);
    }

    public function url(string $path, array $params = []): string
    {
        // Filter params to only allowed ones
        $allowedParams = config('media.glide.allowed_params', []);
        $params = array_filter($params, fn ($key) => in_array($key, $allowedParams), ARRAY_FILTER_USE_KEY);

        // Enforce max dimensions
        if (isset($params['w']) && $params['w'] > config('media.glide.max_width')) {
            $params['w'] = config('media.glide.max_width');
        }
        if (isset($params['h']) && $params['h'] > config('media.glide.max_height')) {
            $params['h'] = config('media.glide.max_height');
        }

        return $this->server->makeImage($path, $params);
    }

    public function generate(Asset $asset, array $params = [], ?string $preset = null): AssetTransformation
    {
        if ($preset) {
            $presetParams = config("media.presets.{$preset}", []);
            $params = array_merge($presetParams, $params);
        }

        // Apply focus point
        if ($asset->focus_css && ! isset($params['crop'])) {
            $params['crop'] = $asset->focus_css;
        }

        $paramsHash = AssetTransformation::hashParams($params);

        // Check if transformation already exists
        $transformation = AssetTransformation::where('asset_id', $asset->id)
            ->where('params_hash', $paramsHash)
            ->first();

        if ($transformation && $transformation->exists()) {
            $transformation->trackAccess();

            return $transformation;
        }

        // Generate transformation
        $outputPath = $this->server->makeImage($asset->path, $params);
        $cacheDisk = Storage::disk(config('media.glide.cache_disk'));
        $fullPath = config('media.glide.cache_path').'/'.$outputPath;

        // Get dimensions of generated image
        $dimensions = $this->getImageDimensions($cacheDisk->path($fullPath));

        // Create or update transformation record
        if ($transformation) {
            $transformation->update([
                'path' => $fullPath,
                'size' => $cacheDisk->size($fullPath),
                'width' => $dimensions['width'] ?? null,
                'height' => $dimensions['height'] ?? null,
                'last_accessed_at' => now(),
                'access_count' => $transformation->access_count + 1,
            ]);
        } else {
            $transformation = AssetTransformation::create([
                'asset_id' => $asset->id,
                'preset' => $preset,
                'params' => $params,
                'params_hash' => $paramsHash,
                'path' => $fullPath,
                'size' => $cacheDisk->size($fullPath),
                'width' => $dimensions['width'] ?? null,
                'height' => $dimensions['height'] ?? null,
                'last_accessed_at' => now(),
                'access_count' => 1,
            ]);
        }

        return $transformation;
    }

    protected function getImageDimensions(string $path): array
    {
        try {
            [$width, $height] = getimagesize($path);

            return compact('width', 'height');
        } catch (\Exception $e) {
            return [];
        }
    }

    public function cleanupCache(?int $olderThanDays = null): int
    {
        $olderThanDays = $olderThanDays ?? (((config('media.glide.cache_cleanup_older_than') / 60) / 60) / 24);

        $transformations = AssetTransformation::olderThan($olderThanDays)->get();
        $deleted = 0;

        foreach ($transformations as $transformation) {
            $transformation->deleteFile();
            $transformation->delete();
            $deleted++;
        }

        return $deleted;
    }

    public function warmCache(Asset $asset, array $presets): void
    {
        foreach ($presets as $preset) {
            $this->generate($asset, [], $preset);
        }
    }
}
