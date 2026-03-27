<?php

namespace Cartino\Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Cartino\Models\Product;
use Cartino\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class ProductControllerTest extends TestCase
{
    use WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'can_access_control_panel' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/cartino/products');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'price_amount',
                        'sale_price_amount',
                        'sku',
                        'status',
                        'is_visible',
                        'seo_title',
                        'seo_description',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    /** @test */
    public function it_can_show_single_product()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price_amount' => 1999,
        ]);

        $response = $this->getJson("/api/cartino/products/{$product->id}");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                    'sku' => 'TEST-001',
                    'price_amount' => 1999,
                ],
            ]);
    }

    /** @test */
    public function it_can_create_product()
    {
        $productData = [
            'name' => 'New Product',
            'slug' => 'new-product',
            'sku' => 'NEW-001',
            'price_amount' => 2999,
            'status' => 'published',
            'is_visible' => true,
            'seo_title' => 'New Product SEO',
            'seo_description' => 'Description for SEO',
        ];

        $response = $this->postJson('/api/cartino/products', $productData);

        $response
            ->assertCreated()
            ->assertJsonFragment([
                'name' => 'New Product',
                'sku' => 'NEW-001',
                'price_amount' => 2999,
            ]);

        $this->assertDatabaseHas('shopper_products', [
            'name' => 'New Product',
            'sku' => 'NEW-001',
            'price_amount' => 2999,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_product()
    {
        $response = $this->postJson('/api/cartino/products', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'sku']);
    }

    /** @test */
    public function it_can_update_product()
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price_amount' => 1000,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'price_amount' => 2000,
        ];

        $response = $this->putJson("/api/cartino/products/{$product->id}", $updateData);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Updated Name',
                'price_amount' => 2000,
            ]);

        $this->assertDatabaseHas('shopper_products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price_amount' => 2000,
        ]);
    }

    /** @test */
    public function it_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/cartino/products/{$product->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('shopper_products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function it_can_search_products_by_name()
    {
        Product::factory()->create(['name' => 'iPhone 14']);
        Product::factory()->create(['name' => 'Samsung Galaxy']);
        Product::factory()->create(['name' => 'iPhone 15']);

        $response = $this->getJson('/api/cartino/products?search=iPhone');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_filter_products_by_status()
    {
        Product::factory()->create(['status' => 'published']);
        Product::factory()->create(['status' => 'draft']);
        Product::factory()->create(['status' => 'published']);

        $response = $this->getJson('/api/cartino/products?status=published');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_requires_authentication_for_protected_endpoints()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/cartino/products');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_requires_control_panel_access_permission()
    {
        $user = User::factory()->create([
            'can_access_control_panel' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/cartino/products');

        $response->assertForbidden();
    }
}
