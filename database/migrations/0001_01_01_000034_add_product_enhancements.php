<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Product Enhancement Fields
 *
 * Based on enterprise platform analysis (PrestaShop, Shopware, Shopify, Sylius)
 * These fields enhance product capabilities for large-scale operations (5M+ products).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Ordering policies
            $table->integer('min_order_quantity')
                ->default(1)
                ->after('requires_selling_plan')
                ->comment('Minimum quantity that must be ordered');

            $table->integer('order_increment')
                ->default(1)
                ->after('min_order_quantity')
                ->comment('Quantity must be in multiples of this value');

            // Closeout & restocking
            $table->boolean('is_closeout')
                ->default(false)
                ->after('order_increment')
                ->comment('Marked for clearance, no restock');

            $table->integer('restock_days')
                ->nullable()
                ->after('is_closeout')
                ->comment('Expected days until restock if out of stock');

            // Product condition
            $table->enum('condition', ['new', 'used', 'refurbished'])
                ->default('new')
                ->after('restock_days')
                ->comment('Product condition');

            // Customs & compliance
            $table->string('hs_code', 20)
                ->nullable()
                ->after('condition')
                ->comment('Harmonized System tariff code');

            $table->string('country_of_origin', 2)
                ->nullable()
                ->after('hs_code')
                ->comment('ISO 3166-1 alpha-2 country code');

            // Visibility control (Shopify-style)
            $table->enum('visibility', ['everywhere', 'catalog', 'search', 'none'])
                ->default('everywhere')
                ->after('status')
                ->comment('Where product appears: everywhere, catalog only, search only, or hidden');

            // Indexes for filtering and performance
            $table->index('is_closeout');
            $table->index('condition');
            $table->index('visibility');
            $table->index(['is_closeout', 'status']);
            $table->index(['visibility', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['visibility', 'status']);
            $table->dropIndex(['is_closeout', 'status']);
            $table->dropIndex(['visibility']);
            $table->dropIndex(['condition']);
            $table->dropIndex(['is_closeout']);

            $table->dropColumn([
                'min_order_quantity',
                'order_increment',
                'is_closeout',
                'restock_days',
                'condition',
                'hs_code',
                'country_of_origin',
                'visibility',
            ]);
        });
    }
};
