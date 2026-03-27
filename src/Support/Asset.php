<?php

namespace Cartino\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;

class Asset
{
    protected static ?array $manifest = null;

    /**
     * Get the URL for a Shopper asset.
     */
    public static function url(string $path): string
    {
        $manifest = static::getManifest();

        if (isset($manifest[$path])) {
            return asset('vendor/cartino/'.$manifest[$path]['file']);
        }

        // Fallback to direct path if manifest not found
        return asset('vendor/cartino/'.ltrim($path, '/'));
    }

    /**
     * Generate script tags for Shopper app.
     */
    public static function scripts(): HtmlString
    {
        $manifest = static::getManifest();
        $scripts = '';

        if (isset($manifest['resources/js/app.js'])) {
            $entry = $manifest['resources/js/app.js'];

            // Add preload links for imports
            if (isset($entry['imports'])) {
                foreach ($entry['imports'] as $import) {
                    if (isset($manifest[$import])) {
                        $importFile = $manifest[$import]['file'];
                        $scripts .=
                            '<link rel="modulepreload" href="'.asset('vendor/cartino/'.$importFile).'">'."\n";
                    }
                }
            }

            // Add main script
            $scripts .= '<script type="module" src="'.static::url('resources/js/app.js').'"></script>';
        }

        return new HtmlString($scripts);
    }

    /**
     * Generate CSS links for Shopper app.
     */
    public static function styles(): HtmlString
    {
        $manifest = static::getManifest();
        $styles = '';

        if (isset($manifest['resources/js/app.js']['css'])) {
            foreach ($manifest['resources/js/app.js']['css'] as $css) {
                $styles .= '<link rel="stylesheet" href="'.asset('vendor/cartino/'.$css).'">'."\n";
            }
        }

        return new HtmlString($styles);
    }

    /**
     * Get the Vite manifest.
     */
    protected static function getManifest(): array
    {
        if (static::$manifest !== null) {
            return static::$manifest;
        }

        $manifestPath = public_path('vendor/cartino/.vite/manifest.json');

        if (! File::exists($manifestPath)) {
            static::$manifest = [];

            return static::$manifest;
        }

        static::$manifest = json_decode(File::get($manifestPath), true) ?: [];

        return static::$manifest;
    }

    /**
     * Check if assets are built and available.
     */
    public static function isBuilt(): bool
    {
        return File::exists(public_path('vendor/cartino/.vite/manifest.json'));
    }
}
