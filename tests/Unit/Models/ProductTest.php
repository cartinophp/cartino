<?php

namespace Cartino\Tests\Unit\Models;

use Cartino\Models\Brand;
use Cartino\Models\Category;
use Cartino\Models\Product;
use Cartino\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_product_with_factory()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price_amount' => 1999,
        ]);

        $this->assertDatabaseHas('shopper_products', [
            'id' => $product->id,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price_amount' => 1999,
        ]);
    }

    /** @test */
    public function it_has_formatted_price_attribute()
    {
        $product = Product::factory()->create([
            'price_amount' => 1999,
        ]);

        $this->assertEquals('€19.99', $product->formatted_price);
    }

    /** @test */
    public function it_has_formatted_sale_price_attribute()
    {
        $product = Product::factory()->create([
            'price_amount' => 1999,
            'sale_price_amount' => 1599,
        ]);

        $this->assertEquals('€15.99', $product->formatted_sale_price);
    }

    /** @test */
    public function it_determines_if_product_is_on_sale()
    {
        $product = Product::factory()->create([
            'price_amount' => 1999,
            'sale_price_amount' => 1599,
        ]);

        $this->assertTrue($product->is_on_sale);

        $product->update(['sale_price_amount' => null]);
        $product->refresh();

        $this->assertFalse($product->is_on_sale);
    }

    /** @test */
    public function it_calculates_discount_percentage()
    {
        $product = Product::factory()->create([
            'price_amount' => 2000,
            'sale_price_amount' => 1600,
        ]);

        $this->assertEquals(20, $product->discount_percentage);
    }

    /** @test */
    public function it_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'shopper_category_id' => $category->id,
        ]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    /** @test */
    public function it_belongs_to_brand()
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'shopper_brand_id' => $brand->id,
        ]);

        $this->assertInstanceOf(Brand::class, $product->brand);
        $this->assertEquals($brand->id, $product->brand->id);
    }

    /** @test */
    public function it_belongs_to_many_collections()
    {
        $product = Product::factory()->create();
        $collections = Category::factory()->count(3)->create();

        $product->collections()->attach($collections->pluck('id'));

        $this->assertCount(3, $product->collections);
        $this->assertInstanceOf(Category::class, $product->collections->first());
    }

    /** @test */
    public function it_can_be_soft_deleted()
    {
        $product = Product::factory()->create();

        $product->delete();

        $this->assertSoftDeleted('shopper_products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function it_has_published_scope()
    {
        Product::factory()->create(['status' => 'published']);
        Product::factory()->create(['status' => 'draft']);
        Product::factory()->create(['status' => 'published']);

        $publishedProducts = Product::published()->get();

        $this->assertCount(2, $publishedProducts);
    }

    /** @test */
    public function it_has_visible_scope()
    {
        Product::factory()->create(['is_visible' => true]);
        Product::factory()->create(['is_visible' => false]);
        Product::factory()->create(['is_visible' => true]);

        $visibleProducts = Product::visible()->get();

        $this->assertCount(2, $visibleProducts);
    }

    /** @test */
    public function it_generates_slug_from_name()
    {
        $product = Product::factory()->create([
            'name' => 'Amazing Product Name',
        ]);

        $this->assertEquals('amazing-product-name', $product->slug);
    }

    /** @test */
    public function it_ensures_unique_slug()
    {
        Product::factory()->create([
            'name' => 'Same Name',
            'slug' => 'same-name',
        ]);

        $product = Product::factory()->create([
            'name' => 'Same Name',
        ]);

        $this->assertStringStartsWith('same-name-', $product->slug);
    }

    /** @test */
    public function it_can_search_by_name()
    {
        Product::factory()->create(['name' => 'iPhone 14 Pro']);
        Product::factory()->create(['name' => 'Samsung Galaxy S23']);
        Product::factory()->create(['name' => 'iPhone 15']);

        $results = Product::search('iPhone')->get();

        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_can_search_by_sku()
    {
        Product::factory()->create(['sku' => 'IP14-PRO-001']);
        Product::factory()->create(['sku' => 'SAM-GAL-002']);
        Product::factory()->create(['sku' => 'IP15-STD-003']);

        $results = Product::search('IP14')->get();

        $this->assertCount(1, $results);
    }
}
