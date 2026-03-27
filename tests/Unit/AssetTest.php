<?php

namespace Cartino\Tests\Unit;

use Cartino\Support\Asset;
use Cartino\Tests\TestCase;
use Illuminate\Support\Facades\File;

class AssetTest extends TestCase
{
    public function test_asset_url_with_manifest()
    {
        // Mock manifest file
        $manifestContent = [
            'resources/js/app.js' => [
                'file' => 'assets/app-B0iDKKsX.js',
                'name' => 'app',
                'css' => ['assets/app-UoZfsuqP.css'],
            ],
        ];

        $manifestPath = public_path('vendor/cartino/.vite/manifest.json');
        File::shouldReceive('exists')->with($manifestPath)->andReturn(true);
        File::shouldReceive('get')->with($manifestPath)->andReturn(json_encode($manifestContent));

        $url = Asset::url('resources/js/app.js');

        $this->assertEquals(asset('vendor/cartino/assets/app-B0iDKKsX.js'), $url);
    }

    public function test_asset_url_without_manifest()
    {
        $manifestPath = public_path('vendor/cartino/.vite/manifest.json');
        File::shouldReceive('exists')->with($manifestPath)->andReturn(false);

        $url = Asset::url('resources/js/app.js');

        $this->assertEquals(asset('vendor/cartino/resources/js/app.js'), $url);
    }

    public function test_is_built_returns_true_when_manifest_exists()
    {
        $manifestPath = public_path('vendor/cartino/.vite/manifest.json');
        File::shouldReceive('exists')->with($manifestPath)->andReturn(true);

        $this->assertTrue(Asset::isBuilt());
    }

    public function test_is_built_returns_false_when_manifest_not_exists()
    {
        $manifestPath = public_path('vendor/cartino/.vite/manifest.json');
        File::shouldReceive('exists')->with($manifestPath)->andReturn(false);

        $this->assertFalse(Asset::isBuilt());
    }

    public function test_scripts_generates_correct_html()
    {
        $manifestContent = [
            'resources/js/app.js' => [
                'file' => 'assets/app-B0iDKKsX.js',
                'name' => 'app',
                'imports' => ['_vendor-2Ew9QOZ7.js'],
                'css' => ['assets/app-UoZfsuqP.css'],
            ],
            '_vendor-2Ew9QOZ7.js' => [
                'file' => 'assets/vendor-2Ew9QOZ7.js',
                'name' => 'vendor',
            ],
        ];

        $manifestPath = public_path('vendor/cartino/.vite/manifest.json');
        File::shouldReceive('exists')->with($manifestPath)->andReturn(true);
        File::shouldReceive('get')->with($manifestPath)->andReturn(json_encode($manifestContent));

        $scripts = Asset::scripts();

        $this->assertStringContains('modulepreload', $scripts->toHtml());
        $this->assertStringContains('assets/vendor-2Ew9QOZ7.js', $scripts->toHtml());
        $this->assertStringContains('assets/app-B0iDKKsX.js', $scripts->toHtml());
    }

    public function test_styles_generates_correct_html()
    {
        $manifestContent = [
            'resources/js/app.js' => [
                'file' => 'assets/app-B0iDKKsX.js',
                'name' => 'app',
                'css' => ['assets/app-UoZfsuqP.css'],
            ],
        ];

        $manifestPath = public_path('vendor/cartino/.vite/manifest.json');
        File::shouldReceive('exists')->with($manifestPath)->andReturn(true);
        File::shouldReceive('get')->with($manifestPath)->andReturn(json_encode($manifestContent));

        $styles = Asset::styles();

        $this->assertStringContains('stylesheet', $styles->toHtml());
        $this->assertStringContains('assets/app-UoZfsuqP.css', $styles->toHtml());
    }
}
