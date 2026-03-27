<?php

declare(strict_types=1);

namespace Cartino\Database\Seeders;

use Cartino\Models\Catalog;
use Cartino\Models\Market;
use Cartino\Models\Site;
use Illuminate\Database\Seeder;

class MarketsSeeder extends Seeder
{
    public function run(): void
    {
        // Create catalogs
        $catalogRetail = Catalog::firstOrCreate(
            ['slug' => 'retail'],
            [
                'title' => 'Retail',
                'description' => 'Standard retail pricing',
                'currency' => 'EUR',
                'is_default' => true,
                'status' => 'active',
            ],
        );

        $catalogB2B = Catalog::firstOrCreate(
            ['slug' => 'b2b'],
            [
                'title' => 'B2B Wholesale',
                'description' => 'B2B wholesale pricing with discounts',
                'currency' => 'EUR',
                'adjustment_type' => 'percentage',
                'adjustment_direction' => 'decrease',
                'adjustment_value' => 20, // 20% discount
                'status' => 'active',
            ],
        );

        $catalogVIP = Catalog::firstOrCreate(
            ['slug' => 'vip'],
            [
                'title' => 'VIP',
                'description' => 'VIP customer exclusive pricing',
                'currency' => 'EUR',
                'adjustment_type' => 'percentage',
                'adjustment_direction' => 'decrease',
                'adjustment_value' => 10, // 10% discount
                'status' => 'active',
            ],
        );

        // ============================================
        // EUROPE MARKETS
        // ============================================

        $marketEU_B2C = Market::firstOrCreate(
            ['code' => 'EU-B2C'],
            [
                'handle' => 'eu-b2c',
                'name' => 'Europe B2C',
                'type' => 'b2c',
                'description' => 'European Union B2C market',
                'countries' => ['IT', 'FR', 'DE', 'ES', 'PT', 'NL', 'BE', 'AT', 'IE'],
                'default_currency' => 'EUR',
                'supported_currencies' => ['EUR'],
                'default_locale' => 'en_US',
                'supported_locales' => ['en_US', 'it_IT', 'fr_FR', 'de_DE', 'es_ES'],
                'tax_included_in_prices' => true,
                'tax_region' => 'EU',
                'catalog_id' => $catalogRetail->id,
                'use_catalog_prices' => true,
                'payment_methods' => ['stripe', 'paypal', 'bank_transfer'],
                'shipping_methods' => ['standard', 'express', 'same_day'],
                'status' => 'active',
                'is_default' => true,
                'priority' => 100,
            ],
        );

        $marketIT_B2C = Market::firstOrCreate(
            ['code' => 'IT-B2C'],
            [
                'handle' => 'it-b2c',
                'name' => 'Italia B2C',
                'type' => 'b2c',
                'description' => 'Italian B2C market',
                'countries' => ['IT', 'SM', 'VA'],
                'default_currency' => 'EUR',
                'supported_currencies' => ['EUR'],
                'default_locale' => 'it_IT',
                'supported_locales' => ['it_IT', 'en_US'],
                'tax_included_in_prices' => true,
                'tax_region' => 'IT',
                'catalog_id' => $catalogRetail->id,
                'use_catalog_prices' => true,
                'payment_methods' => ['stripe', 'paypal', 'bank_transfer', 'satispay'],
                'shipping_methods' => ['standard', 'express', 'poste_italiane'],
                'status' => 'active',
                'priority' => 90,
            ],
        );

        $marketEU_B2B = Market::firstOrCreate(
            ['code' => 'EU-B2B'],
            [
                'handle' => 'eu-b2b',
                'name' => 'Europe B2B',
                'type' => 'b2b',
                'description' => 'European B2B wholesale market',
                'countries' => ['IT', 'FR', 'DE', 'ES', 'PT', 'NL', 'BE', 'AT'],
                'default_currency' => 'EUR',
                'supported_currencies' => ['EUR'],
                'default_locale' => 'en_US',
                'supported_locales' => ['en_US', 'it_IT', 'fr_FR', 'de_DE'],
                'tax_included_in_prices' => false,
                'tax_region' => 'EU',
                'catalog_id' => $catalogB2B->id,
                'use_catalog_prices' => true,
                'payment_methods' => ['bank_transfer', 'invoice'],
                'shipping_methods' => ['standard', 'express', 'freight'],
                'status' => 'active',
                'priority' => 80,
            ],
        );

        // ============================================
        // UK MARKET (Post-Brexit)
        // ============================================

        $marketUK_B2C = Market::firstOrCreate(
            ['code' => 'UK-B2C'],
            [
                'handle' => 'uk-b2c',
                'name' => 'United Kingdom B2C',
                'type' => 'b2c',
                'description' => 'UK B2C market (post-Brexit)',
                'countries' => ['GB'],
                'default_currency' => 'GBP',
                'supported_currencies' => ['GBP', 'EUR'],
                'default_locale' => 'en_GB',
                'supported_locales' => ['en_GB', 'en_US'],
                'tax_included_in_prices' => true,
                'tax_region' => 'GB',
                'catalog_id' => $catalogRetail->id,
                'use_catalog_prices' => true,
                'payment_methods' => ['stripe', 'paypal'],
                'shipping_methods' => ['standard', 'express', 'royal_mail'],
                'status' => 'active',
                'priority' => 70,
            ],
        );

        // ============================================
        // USA MARKETS
        // ============================================

        $marketUS_B2C = Market::firstOrCreate(
            ['code' => 'US-B2C'],
            [
                'handle' => 'us-b2c',
                'name' => 'United States B2C',
                'type' => 'b2c',
                'description' => 'USA B2C market',
                'countries' => ['US'],
                'default_currency' => 'USD',
                'supported_currencies' => ['USD'],
                'default_locale' => 'en_US',
                'supported_locales' => ['en_US', 'es_ES'],
                'tax_included_in_prices' => false,
                'tax_region' => 'US',
                'catalog_id' => $catalogRetail->id,
                'use_catalog_prices' => true,
                'payment_methods' => ['stripe', 'paypal', 'apple_pay'],
                'shipping_methods' => ['standard', 'express', 'usps', 'ups', 'fedex'],
                'status' => 'active',
                'priority' => 60,
            ],
        );

        $marketUS_Wholesale = Market::firstOrCreate(
            ['code' => 'US-WHOLESALE'],
            [
                'handle' => 'us-wholesale',
                'name' => 'US Wholesale',
                'type' => 'wholesale',
                'description' => 'USA wholesale market for distributors',
                'countries' => ['US'],
                'default_currency' => 'USD',
                'supported_currencies' => ['USD'],
                'default_locale' => 'en_US',
                'supported_locales' => ['en_US'],
                'tax_included_in_prices' => false,
                'tax_region' => 'US',
                'catalog_id' => $catalogB2B->id,
                'use_catalog_prices' => true,
                'payment_methods' => ['bank_transfer', 'invoice'],
                'shipping_methods' => ['freight', 'ups', 'fedex'],
                'status' => 'active',
                'priority' => 50,
            ],
        );

        // ============================================
        // ASIA-PACIFIC MARKET
        // ============================================

        $marketAPAC = Market::firstOrCreate(
            ['code' => 'APAC-B2C'],
            [
                'handle' => 'apac-b2c',
                'name' => 'Asia-Pacific B2C',
                'type' => 'b2c',
                'description' => 'Asia-Pacific region B2C market',
                'countries' => ['JP', 'AU', 'NZ', 'SG', 'HK'],
                'default_currency' => 'USD',
                'supported_currencies' => ['USD', 'AUD', 'JPY', 'SGD'],
                'default_locale' => 'en_US',
                'supported_locales' => ['en_US', 'ja_JP'],
                'tax_included_in_prices' => false,
                'tax_region' => 'APAC',
                'catalog_id' => $catalogRetail->id,
                'use_catalog_prices' => true,
                'payment_methods' => ['stripe', 'paypal'],
                'shipping_methods' => ['standard', 'express', 'dhl'],
                'status' => 'active',
                'priority' => 40,
            ],
        );

        // ============================================
        // CREATE SITES FOR MARKETS
        // ============================================

        Site::firstOrCreate(
            ['handle' => 'it-shop'],
            [
                'market_id' => $marketIT_B2C->id,
                'name' => 'Shop Italia',
                'domain' => 'shop.it',
                'url' => 'https://shop.it',
                'locale' => 'it_IT',
                'lang' => 'it',
                'countries' => ['IT'],
                'default_currency' => 'EUR',
                'tax_included_in_prices' => true,
                'tax_region' => 'IT',
                'status' => 'active',
                'is_default' => false,
                'priority' => 10,
            ],
        );

        Site::firstOrCreate(
            ['handle' => 'eu-shop'],
            [
                'market_id' => $marketEU_B2C->id,
                'name' => 'Shop Europe',
                'domain' => 'shop.eu',
                'url' => 'https://shop.eu',
                'locale' => 'en_US',
                'lang' => 'en',
                'countries' => ['IT', 'FR', 'DE', 'ES'],
                'default_currency' => 'EUR',
                'tax_included_in_prices' => true,
                'tax_region' => 'EU',
                'status' => 'active',
                'is_default' => true,
                'priority' => 20,
            ],
        );

        Site::firstOrCreate(
            ['handle' => 'us-shop'],
            [
                'market_id' => $marketUS_B2C->id,
                'name' => 'Shop USA',
                'domain' => 'shop.com',
                'url' => 'https://shop.com',
                'locale' => 'en_US',
                'lang' => 'en',
                'countries' => ['US'],
                'default_currency' => 'USD',
                'tax_included_in_prices' => false,
                'tax_region' => 'US',
                'status' => 'active',
                'is_default' => false,
                'priority' => 15,
            ],
        );

        $this->command->info('✅ Markets and Sites seeded successfully!');
        $this->command->info("   - {$marketEU_B2C->code}: {$marketEU_B2C->name}");
        $this->command->info("   - {$marketIT_B2C->code}: {$marketIT_B2C->name}");
        $this->command->info("   - {$marketEU_B2B->code}: {$marketEU_B2B->name}");
        $this->command->info("   - {$marketUK_B2C->code}: {$marketUK_B2C->name}");
        $this->command->info("   - {$marketUS_B2C->code}: {$marketUS_B2C->name}");
        $this->command->info("   - {$marketUS_Wholesale->code}: {$marketUS_Wholesale->name}");
        $this->command->info("   - {$marketAPAC->code}: {$marketAPAC->name}");
    }
}
