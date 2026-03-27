<?php

declare(strict_types=1);

namespace Cartino\Tests\Feature\Api;

use Cartino\Models\Catalog;
use Cartino\Models\Product;
use Cartino\Models\Site;
use Cartino\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CatalogFilteringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_catalogs()
    {
        Catalog::factory()->count(3)->active()->create();

        $response = $this->getJson('/api/catalogs');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'status',
                        'currency',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    /** @test */
    public function it_can_filter_catalogs_by_site_id()
    {
        $siteA = Site::factory()->active()->create(['handle' => 'site-a']);
        $siteB = Site::factory()->active()->create(['handle' => 'site-b']);

        $catalogA = Catalog::factory()->active()->create(['title' => 'Catalog A']);
        $catalogB = Catalog::factory()->active()->create(['title' => 'Catalog B']);
        $catalogC = Catalog::factory()->active()->create(['title' => 'Catalog C']);

        // Attach catalogs to sites
        $siteA->catalogs()->attach($catalogA->id, ['is_active' => true]);
        $siteA->catalogs()->attach($catalogB->id, ['is_active' => true]);
        $siteB->catalogs()->attach($catalogC->id, ['is_active' => true]);

        $response = $this->getJson("/api/catalogs?filter[site]={$siteA->id}");

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['title' => 'Catalog A'])
            ->assertJsonFragment(['title' => 'Catalog B'])
            ->assertJsonMissing(['title' => 'Catalog C']);
    }

    /** @test */
    public function it_can_filter_catalogs_by_site_slug()
    {
        $siteA = Site::factory()->active()->create(['handle' => 'site-alpha']);
        $siteB = Site::factory()->active()->create(['handle' => 'site-beta']);

        $catalogA = Catalog::factory()->active()->create();
        $catalogB = Catalog::factory()->active()->create();

        $siteA->catalogs()->attach($catalogA->id, ['is_active' => true]);
        $siteB->catalogs()->attach($catalogB->id, ['is_active' => true]);

        $response = $this->getJson('/api/catalogs?filter[site]=site-alpha');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $catalogA->id]);
    }

    /** @test */
    public function it_can_filter_catalogs_by_site_handle()
    {
        $site = Site::factory()->active()->create(['handle' => 'main-store']);
        $catalog = Catalog::factory()->active()->create();

        $site->catalogs()->attach($catalog->id, ['is_active' => true]);

        $response = $this->getJson('/api/catalogs?filter[site]=main-store');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $catalog->id]);
    }

    /** @test */
    public function it_shows_site_isolation_between_catalogs()
    {
        $siteA = Site::factory()->active()->create(['handle' => 'site-a']);
        $siteB = Site::factory()->active()->create(['handle' => 'site-b']);

        $catalogA = Catalog::factory()->active()->create();
        $catalogB = Catalog::factory()->active()->create();

        $productA = Product::factory()->create(['name' => 'Product A']);
        $productB = Product::factory()->create(['name' => 'Product B']);

        $siteA->catalogs()->attach($catalogA->id, ['is_active' => true]);
        $siteB->catalogs()->attach($catalogB->id, ['is_active' => true]);

        $catalogA->products()->attach($productA->id);
        $catalogB->products()->attach($productB->id);

        // Verify site A catalogs
        $responseA = $this->getJson("/api/catalogs?filter[site]={$siteA->id}");
        $responseA
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $catalogA->id])
            ->assertJsonMissing(['id' => $catalogB->id]);

        // Verify site B catalogs
        $responseB = $this->getJson("/api/catalogs?filter[site]={$siteB->id}");
        $responseB
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $catalogB->id])
            ->assertJsonMissing(['id' => $catalogA->id]);
    }

    /** @test */
    public function it_can_get_active_catalogs()
    {
        Catalog::factory()->count(2)->active()->create();
        Catalog::factory()->create(['status' => 'draft']);

        $response = $this->getJson('/api/catalogs/active');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_get_published_catalogs()
    {
        Catalog::factory()->count(2)->published()->create();
        Catalog::factory()->create(['status' => 'draft']);

        $response = $this->getJson('/api/catalogs/published');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_show_single_catalog_with_sites()
    {
        $site = Site::factory()->active()->create();
        $catalog = Catalog::factory()->active()->create();

        $site->catalogs()->attach($catalog->id, [
            'is_active' => true,
            'priority' => 10,
        ]);

        $response = $this->getJson("/api/catalogs/{$catalog->id}?include=sites");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'sites' => [
                        '*' => [
                            'id',
                            'handle',
                            'name',
                        ],
                    ],
                ],
            ]);
    }
}
