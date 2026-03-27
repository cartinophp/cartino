<?php

declare(strict_types=1);

namespace Cartino\Database\Seeders;

use Cartino\Models\Catalog;
use Cartino\Models\Market;
use Cartino\Models\Price;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Cartino\Models\Site;
use Illuminate\Database\Seeder;

class MultiMarketPricesSeeder extends Seeder
{
    public function run(): void
    {
        // Get markets
        $marketEU = Market::where('code', 'EU-B2C')->first();
        $marketIT = Market::where('code', 'IT-B2C')->first();
        $marketUS = Market::where('code', 'US-B2C')->first();
        $marketUK = Market::where('code', 'UK-B2C')->first();
        $marketB2B = Market::where('code', 'EU-B2B')->first();

        if (! $marketEU) {
            $this->command->warn('⚠️  Markets not found. Please run MarketsSeeder first.');

            return;
        }

        // Get catalogs
        $catalogRetail = Catalog::where('slug', 'retail')->first();
        $catalogB2B = Catalog::where('slug', 'b2b')->first();

        // Get sites
        $siteIT = Site::where('handle', 'it-shop')->first();
        $siteEU = Site::where('handle', 'eu-shop')->first();

        // Create sample products if they don't exist
        $products = $this->createSampleProducts();

        foreach ($products as $product) {
            $variant = $product->variants()->first();

            if (! $variant) {
                continue;
            }

            $this->createPricesForVariant($variant, [
                'marketEU' => $marketEU,
                'marketIT' => $marketIT,
                'marketUS' => $marketUS,
                'marketUK' => $marketUK,
                'marketB2B' => $marketB2B,
                'catalogRetail' => $catalogRetail,
                'catalogB2B' => $catalogB2B,
                'siteIT' => $siteIT,
                'siteEU' => $siteEU,
            ]);
        }

        $this->command->info('✅ Multi-market prices seeded successfully!');
    }

    protected function createSampleProducts(): array
    {
        $products = [];

        // Product 1: T-Shirt
        $products[] = Product::firstOrCreate(
            ['slug' => 'classic-tshirt'],
            [
                'name' => 'Classic T-Shirt',
                'description' => 'Comfortable cotton t-shirt',
                'status' => 'active',
            ],
        );

        // Create variant if doesn't exist
        $product = $products[0];
        if ($product->variants()->count() === 0) {
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'TSHIRT-BLK-M',
                'name' => 'Black / M',
                'price' => 2500, // Base price €25.00
            ]);
        }

        // Product 2: Sneakers
        $products[] = Product::firstOrCreate(
            ['slug' => 'sport-sneakers'],
            [
                'name' => 'Sport Sneakers',
                'description' => 'High-performance sport sneakers',
                'status' => 'active',
            ],
        );

        $product = $products[1];
        if ($product->variants()->count() === 0) {
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SNEAK-WHT-42',
                'name' => 'White / 42',
                'price' => 8900, // Base price €89.00
            ]);
        }

        return $products;
    }

    protected function createPricesForVariant(ProductVariant $variant, array $entities): void
    {
        // Determine base amount based on SKU
        $baseEUR = $variant->sku === 'TSHIRT-BLK-M' ? 2500 : 8900;
        $baseUSD = (int) ($baseEUR * 1.1); // ~10% markup for USD
        $baseGBP = (int) ($baseEUR * 0.9); // ~10% discount for GBP

        // ===========================================
        // BASE PRICE (no market/site/channel)
        // ===========================================
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => null,
                'site_id' => null,
                'channel_id' => null,
                'price_list_id' => null,
                'currency' => 'EUR',
                'min_quantity' => 1,
            ],
            [
                'amount' => $baseEUR,
                'compare_at_amount' => null,
                'tax_included' => true,
                'tax_rate' => 22.00,
                'is_active' => true,
            ],
        );

        // ===========================================
        // MARKET-SPECIFIC PRICES
        // ===========================================

        // EU Market (slight discount from base)
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => $entities['marketEU']->id,
                'currency' => 'EUR',
                'min_quantity' => 1,
            ],
            [
                'amount' => (int) ($baseEUR * 0.95), // 5% discount
                'tax_included' => true,
                'tax_rate' => 22.00,
                'is_active' => true,
            ],
        );

        // IT Market (best price)
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => $entities['marketIT']->id,
                'currency' => 'EUR',
                'min_quantity' => 1,
            ],
            [
                'amount' => (int) ($baseEUR * 0.90), // 10% discount
                'compare_at_amount' => $baseEUR,
                'tax_included' => true,
                'tax_rate' => 22.00,
                'is_active' => true,
            ],
        );

        // US Market
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => $entities['marketUS']->id,
                'currency' => 'USD',
                'min_quantity' => 1,
            ],
            [
                'amount' => $baseUSD,
                'tax_included' => false,
                'tax_rate' => null, // Tax varies by state
                'is_active' => true,
            ],
        );

        // UK Market
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => $entities['marketUK']->id,
                'currency' => 'GBP',
                'min_quantity' => 1,
            ],
            [
                'amount' => $baseGBP,
                'tax_included' => true,
                'tax_rate' => 20.00,
                'is_active' => true,
            ],
        );

        // ===========================================
        // SITE-SPECIFIC PRICES (override market)
        // ===========================================

        // IT Site (even better than market)
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => $entities['marketIT']->id,
                'site_id' => $entities['siteIT']->id,
                'currency' => 'EUR',
                'min_quantity' => 1,
            ],
            [
                'amount' => (int) ($baseEUR * 0.85), // 15% discount
                'compare_at_amount' => $baseEUR,
                'tax_included' => true,
                'tax_rate' => 22.00,
                'is_active' => true,
            ],
        );

        // ===========================================
        // B2B CATALOG PRICES
        // ===========================================

        // B2B EU
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => $entities['marketB2B']->id,
                'price_list_id' => $entities['catalogB2B']->id,
                'currency' => 'EUR',
                'min_quantity' => 1,
            ],
            [
                'amount' => (int) ($baseEUR * 0.70), // 30% B2B discount
                'tax_included' => false,
                'tax_rate' => 22.00,
                'is_active' => true,
            ],
        );

        // ===========================================
        // QUANTITY TIERS (EU Market)
        // ===========================================

        // 10-49 units
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => $entities['marketEU']->id,
                'currency' => 'EUR',
                'min_quantity' => 10,
            ],
            [
                'amount' => (int) ($baseEUR * 0.90), // 10% discount
                'max_quantity' => 49,
                'tax_included' => true,
                'tax_rate' => 22.00,
                'is_active' => true,
            ],
        );

        // 50+ units
        Price::firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'market_id' => $entities['marketEU']->id,
                'currency' => 'EUR',
                'min_quantity' => 50,
            ],
            [
                'amount' => (int) ($baseEUR * 0.80), // 20% discount
                'tax_included' => true,
                'tax_rate' => 22.00,
                'is_active' => true,
            ],
        );

        $this->command->info("   ✓ Prices created for SKU: {$variant->sku}");
    }
}
