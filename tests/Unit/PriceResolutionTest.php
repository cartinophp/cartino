<?php

declare(strict_types=1);

namespace Cartino\Tests\Unit;

use Cartino\DTO\PricingContext;
use Cartino\Models\Channel;
use Cartino\Models\Market;
use Cartino\Models\Price;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Cartino\Models\Site;
use Cartino\Services\PriceResolutionService;
use Cartino\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PriceResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected PriceResolutionService $service;

    protected Market $marketEU;

    protected Market $marketUS;

    protected Site $siteIT;

    protected Channel $channelWeb;

    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PriceResolutionService::class);

        // Setup test data
        $this->setupMarkets();
        $this->setupProducts();
    }

    protected function setupMarkets(): void
    {
        $this->marketEU = Market::create([
            'handle' => 'eu-b2c',
            'name' => 'EU B2C',
            'code' => 'EU-B2C',
            'type' => 'b2c',
            'countries' => ['IT', 'FR', 'DE'],
            'default_currency' => 'EUR',
            'supported_currencies' => ['EUR'],
            'default_locale' => 'it_IT',
            'supported_locales' => ['it_IT', 'en_US'],
            'status' => 'active',
        ]);

        $this->marketUS = Market::create([
            'handle' => 'us-b2c',
            'name' => 'US B2C',
            'code' => 'US-B2C',
            'type' => 'b2c',
            'countries' => ['US'],
            'default_currency' => 'USD',
            'supported_currencies' => ['USD'],
            'default_locale' => 'en_US',
            'status' => 'active',
        ]);

        $this->siteIT = Site::create([
            'market_id' => $this->marketEU->id,
            'handle' => 'it-shop',
            'name' => 'IT Shop',
            'domain' => 'shop.it',
            'locale' => 'it_IT',
            'lang' => 'it',
            'default_currency' => 'EUR',
            'status' => 'active',
        ]);

        $this->channelWeb = Channel::create([
            'site_id' => $this->siteIT->id,
            'name' => 'Web',
            'slug' => 'web',
            'type' => 'web',
            'locales' => ['it_IT', 'en_US'],
            'currencies' => ['EUR'],
            'status' => 'active',
        ]);
    }

    protected function setupProducts(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'status' => 'active',
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-001',
            'price' => 10000, // €100.00 base
        ]);
    }

    public function test_it_resolves_base_price_when_no_specific_context()
    {
        // Create base price (no market, site, channel)
        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 10000, // €100.00
            'is_active' => true,
        ]);

        $context = new PricingContext(
            currency: 'EUR',
            quantity: 1,
        );

        $price = $this->service->resolve($this->variant, $context);

        $this->assertNotNull($price);
        $this->assertEquals(10000, $price->amount);
        $this->assertNull($price->market_id);
        $this->assertNull($price->site_id);
    }

    public function test_it_resolves_market_specific_price_over_base_price()
    {
        // Base price
        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 10000, // €100.00
            'is_active' => true,
        ]);

        // Market-specific price (higher priority)
        Price::create([
            'product_variant_id' => $this->variant->id,
            'market_id' => $this->marketEU->id,
            'currency' => 'EUR',
            'amount' => 9500, // €95.00
            'is_active' => true,
        ]);

        $context = new PricingContext(
            market: $this->marketEU,
            currency: 'EUR',
            quantity: 1,
        );

        $price = $this->service->resolve($this->variant, $context);

        $this->assertNotNull($price);
        $this->assertEquals(9500, $price->amount);
        $this->assertEquals($this->marketEU->id, $price->market_id);
    }

    public function test_it_resolves_site_specific_price_over_market_price()
    {
        // Market price
        Price::create([
            'product_variant_id' => $this->variant->id,
            'market_id' => $this->marketEU->id,
            'currency' => 'EUR',
            'amount' => 9500,
            'is_active' => true,
        ]);

        // Market + Site price (higher priority)
        Price::create([
            'product_variant_id' => $this->variant->id,
            'market_id' => $this->marketEU->id,
            'site_id' => $this->siteIT->id,
            'currency' => 'EUR',
            'amount' => 9000, // €90.00
            'is_active' => true,
        ]);

        $context = new PricingContext(
            market: $this->marketEU,
            site: $this->siteIT,
            currency: 'EUR',
            quantity: 1,
        );

        $price = $this->service->resolve($this->variant, $context);

        $this->assertNotNull($price);
        $this->assertEquals(9000, $price->amount);
        $this->assertEquals($this->siteIT->id, $price->site_id);
    }

    public function test_it_resolves_most_specific_price_with_all_context()
    {
        // Create prices with increasing specificity
        $basePrice = Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 10000,
            'is_active' => true,
        ]);

        $marketPrice = Price::create([
            'product_variant_id' => $this->variant->id,
            'market_id' => $this->marketEU->id,
            'currency' => 'EUR',
            'amount' => 9500,
            'is_active' => true,
        ]);

        $sitePrice = Price::create([
            'product_variant_id' => $this->variant->id,
            'market_id' => $this->marketEU->id,
            'site_id' => $this->siteIT->id,
            'currency' => 'EUR',
            'amount' => 9000,
            'is_active' => true,
        ]);

        $channelPrice = Price::create([
            'product_variant_id' => $this->variant->id,
            'market_id' => $this->marketEU->id,
            'site_id' => $this->siteIT->id,
            'channel_id' => $this->channelWeb->id,
            'currency' => 'EUR',
            'amount' => 8500, // €85.00 - most specific
            'is_active' => true,
        ]);

        $context = new PricingContext(
            market: $this->marketEU,
            site: $this->siteIT,
            channel: $this->channelWeb,
            currency: 'EUR',
            quantity: 1,
        );

        $price = $this->service->resolve($this->variant, $context);

        $this->assertNotNull($price);
        $this->assertEquals(8500, $price->amount);
        $this->assertEquals($channelPrice->id, $price->id);
    }

    public function test_it_respects_quantity_tiers()
    {
        // Base price for qty 1-9
        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 10000,
            'min_quantity' => 1,
            'max_quantity' => 9,
            'is_active' => true,
        ]);

        // Tier price for qty 10+
        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 9000, // 10% discount
            'min_quantity' => 10,
            'is_active' => true,
        ]);

        // Test qty 5 - should get base price
        $context1 = new PricingContext(
            currency: 'EUR',
            quantity: 5,
        );
        $price1 = $this->service->resolve($this->variant, $context1);
        $this->assertEquals(10000, $price1->amount);

        // Test qty 10 - should get tier price
        $context2 = new PricingContext(
            currency: 'EUR',
            quantity: 10,
        );
        $price2 = $this->service->resolve($this->variant, $context2);
        $this->assertEquals(9000, $price2->amount);
    }

    public function test_it_filters_by_currency()
    {
        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 10000,
            'is_active' => true,
        ]);

        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'USD',
            'amount' => 11000,
            'is_active' => true,
        ]);

        $contextEUR = new PricingContext(
            currency: 'EUR',
            quantity: 1,
        );
        $priceEUR = $this->service->resolve($this->variant, $contextEUR);
        $this->assertEquals(10000, $priceEUR->amount);
        $this->assertEquals('EUR', $priceEUR->currency);

        $contextUSD = new PricingContext(
            currency: 'USD',
            quantity: 1,
        );
        $priceUSD = $this->service->resolve($this->variant, $contextUSD);
        $this->assertEquals(11000, $priceUSD->amount);
        $this->assertEquals('USD', $priceUSD->currency);
    }

    public function test_it_ignores_inactive_prices()
    {
        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 5000,
            'is_active' => false, // Inactive
        ]);

        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 10000,
            'is_active' => true,
        ]);

        $context = new PricingContext(
            currency: 'EUR',
            quantity: 1,
        );
        $price = $this->service->resolve($this->variant, $context);

        $this->assertEquals(10000, $price->amount);
    }

    public function test_it_respects_price_scheduling()
    {
        // Future price (not yet active)
        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 5000,
            'starts_at' => now()->addWeek(),
            'is_active' => true,
        ]);

        // Current price
        Price::create([
            'product_variant_id' => $this->variant->id,
            'currency' => 'EUR',
            'amount' => 10000,
            'is_active' => true,
        ]);

        $context = new PricingContext(
            currency: 'EUR',
            quantity: 1,
        );
        $price = $this->service->resolve($this->variant, $context);

        $this->assertEquals(10000, $price->amount);
    }

    public function test_it_resolves_bulk_prices_efficiently()
    {
        $variants = collect();

        // Create 5 variants
        for ($i = 1; $i <= 5; $i++) {
            $variant = ProductVariant::create([
                'product_id' => $this->variant->product_id,
                'sku' => "TEST-00{$i}",
            ]);

            // Each variant has a price
            Price::create([
                'product_variant_id' => $variant->id,
                'currency' => 'EUR',
                'amount' => 10000 + ($i * 100),
                'is_active' => true,
            ]);

            $variants->push($variant);
        }

        $context = new PricingContext(
            currency: 'EUR',
            quantity: 1,
        );
        $prices = $this->service->resolveBulk($variants, $context);

        $this->assertCount(5, $prices);
        $this->assertEquals(10100, $prices[$variants[0]->id]->amount);
        $this->assertEquals(10500, $prices[$variants[4]->id]->amount);
    }

    public function test_pricing_context_resolves_defaults_from_market()
    {
        $context = new PricingContext(
            market: $this->marketEU,
            quantity: 1,
        );

        $this->assertEquals('EUR', $context->currency);
        $this->assertEquals('it_IT', $context->locale);
    }

    public function test_pricing_context_supports_cache_key_generation()
    {
        $context = new PricingContext(
            market: $this->marketEU,
            site: $this->siteIT,
            currency: 'EUR',
            quantity: 5,
        );

        $cacheKey = $context->getCacheKey('test');

        $this->assertStringContainsString('test:', $cacheKey);
        $this->assertStringContainsString('m'.$this->marketEU->id, $cacheKey);
        $this->assertStringContainsString('s'.$this->siteIT->id, $cacheKey);
        $this->assertStringContainsString('curEUR', $cacheKey);
        $this->assertStringContainsString('qty5', $cacheKey);
    }

    public function test_it_returns_null_when_no_price_found()
    {
        $context = new PricingContext(
            currency: 'EUR',
            quantity: 1,
        );
        $price = $this->service->resolve($this->variant, $context);

        $this->assertNull($price);
    }
}
