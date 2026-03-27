<?php

declare(strict_types=1);

namespace Cartino\Tests\Feature\Api;

use Cartino\Models\Product;
use Cartino\Models\ProductOption;
use Cartino\Models\ProductOptionValue;
use Cartino\Models\ProductVariant;
use Cartino\Models\VariantPrice;
use Cartino\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductVariantFilteringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_filter_products_by_single_variant_attribute()
    {
        // Create products with variants
        $product1 = Product::factory()->create(['name' => 'T-Shirt Red L']);
        $product2 = Product::factory()->create(['name' => 'T-Shirt Blue M']);

        // Create Size option
        $sizeOption = ProductOption::create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
            'position' => 1,
            'is_global' => true,
        ]);

        // Create option values
        $sizeL = ProductOptionValue::create([
            'product_option_id' => $sizeOption->id,
            'label' => 'L',
            'value' => 'L',
            'position' => 1,
        ]);

        $sizeM = ProductOptionValue::create([
            'product_option_id' => $sizeOption->id,
            'label' => 'M',
            'value' => 'M',
            'position' => 2,
        ]);

        // Create variants
        $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id]);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id]);

        // Attach option values to variants
        $variant1->optionValues()->attach($sizeL->id);
        $variant2->optionValues()->attach($sizeM->id);

        // Filter by Size=L
        $response = $this->getJson('/api/products?filter[option][Size]=L');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'T-Shirt Red L'])
            ->assertJsonMissing(['name' => 'T-Shirt Blue M']);
    }

    /** @test */
    public function it_can_filter_products_by_multiple_variant_attributes()
    {
        // Create product
        $product = Product::factory()->create(['name' => 'T-Shirt Red Large']);

        // Create options
        $colorOption = ProductOption::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'position' => 1,
            'is_global' => true,
        ]);

        $sizeOption = ProductOption::create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
            'position' => 2,
            'is_global' => true,
        ]);

        // Create option values
        $red = ProductOptionValue::create([
            'product_option_id' => $colorOption->id,
            'label' => 'Red',
            'value' => 'red',
            'position' => 1,
        ]);

        $large = ProductOptionValue::create([
            'product_option_id' => $sizeOption->id,
            'label' => 'L',
            'value' => 'L',
            'position' => 1,
        ]);

        // Create variant
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $variant->optionValues()->attach([$red->id, $large->id]);

        // Filter by Color=red AND Size=L
        $response = $this->getJson('/api/products?filter[option][Color]=red&filter[option][Size]=L');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'T-Shirt Red Large']);
    }

    /** @test */
    public function it_can_filter_products_by_currency()
    {
        // Create products
        $productUSD = Product::factory()->create(['name' => 'Product USD']);
        $productEUR = Product::factory()->create(['name' => 'Product EUR']);

        // Create variants
        $variantUSD = ProductVariant::factory()->create(['product_id' => $productUSD->id]);
        $variantEUR = ProductVariant::factory()->create(['product_id' => $productEUR->id]);

        // Create prices with different currencies
        VariantPrice::create([
            'product_variant_id' => $variantUSD->id,
            'currency' => 'USD',
            'price' => 29.99,
        ]);

        VariantPrice::create([
            'product_variant_id' => $variantEUR->id,
            'currency' => 'EUR',
            'price' => 24.99,
        ]);

        // Filter by currency=USD
        $response = $this->getJson('/api/products?filter[currency]=USD');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Product USD'])
            ->assertJsonMissing(['name' => 'Product EUR']);
    }

    /** @test */
    public function it_can_filter_products_by_currency_case_insensitive()
    {
        $product = Product::factory()->create(['name' => 'Product EUR']);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        VariantPrice::create([
            'product_variant_id' => $variant->id,
            'currency' => 'EUR',
            'price' => 19.99,
        ]);

        // Filter by lowercase currency
        $response = $this->getJson('/api/products?filter[currency]=eur');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Product EUR']);
    }

    /** @test */
    public function it_can_combine_variant_attribute_and_currency_filters()
    {
        // Create product
        $product = Product::factory()->create(['name' => 'Red Shirt USD']);

        // Create color option
        $colorOption = ProductOption::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'position' => 1,
            'is_global' => true,
        ]);

        $red = ProductOptionValue::create([
            'product_option_id' => $colorOption->id,
            'label' => 'Red',
            'value' => 'red',
            'position' => 1,
        ]);

        // Create variant
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $variant->optionValues()->attach($red->id);

        // Add price in USD
        VariantPrice::create([
            'product_variant_id' => $variant->id,
            'currency' => 'USD',
            'price' => 39.99,
        ]);

        // Filter by Color=red AND currency=USD
        $response = $this->getJson('/api/products?filter[option][Color]=red&filter[currency]=USD');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Red Shirt USD']);
    }

    /** @test */
    public function it_returns_empty_when_no_products_match_filters()
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $colorOption = ProductOption::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'position' => 1,
            'is_global' => true,
        ]);

        $blue = ProductOptionValue::create([
            'product_option_id' => $colorOption->id,
            'label' => 'Blue',
            'value' => 'blue',
            'position' => 1,
        ]);

        $variant->optionValues()->attach($blue->id);

        // Filter by Color=red (doesn't exist)
        $response = $this->getJson('/api/products?filter[option][Color]=red');

        $response
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_can_include_variant_data_with_filters()
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $colorOption = ProductOption::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'position' => 1,
            'is_global' => true,
        ]);

        $red = ProductOptionValue::create([
            'product_option_id' => $colorOption->id,
            'label' => 'Red',
            'value' => 'red',
            'position' => 1,
        ]);

        $variant->optionValues()->attach($red->id);

        $response = $this->getJson('/api/products?filter[option][Color]=red&include=variants,variants.optionValues');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'variants',
                    ],
                ],
            ]);
    }
}
