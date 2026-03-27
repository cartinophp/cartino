<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shipping Zones (Geographic regions with shipping rules)
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();

            // Zone Coverage
            $table->jsonb('countries')->comment('Array of country codes');
            $table->jsonb('regions')->nullable()->comment('Specific states/provinces per country');
            $table->jsonb('postal_codes')->nullable()->comment('Specific postal code ranges');

            // Priority (higher zones take precedence for overlapping regions)
            $table->integer('priority')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->jsonb('data')->nullable();

            $table->index(['site_id', 'is_active']);
            $table->index(['priority', 'is_active']);
        });

        // Shipping Rates (Methods available per zone)
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();

            // Rate Type
            $table->enum('calculation_method', [
                'flat_rate',
                'per_item',
                'weight_based',
                'price_based',
                'carrier_calculated',
            ])->default('flat_rate');

            // Pricing
            $table->decimal('price', 10, 2);
            $table->string('currency', 3);
            $table->decimal('min_price', 10, 2)->nullable()->comment('Price-based minimum');
            $table->decimal('max_price', 10, 2)->nullable()->comment('Price-based maximum');

            // Weight-based
            $table->decimal('min_weight', 10, 2)->nullable();
            $table->decimal('max_weight', 10, 2)->nullable();
            $table->string('weight_unit', 10)->default('kg');

            // Conditions
            $table->decimal('min_order_value', 10, 2)->nullable()->comment('Free shipping threshold');
            $table->decimal('max_order_value', 10, 2)->nullable();

            // Delivery Time Estimates
            $table->integer('min_delivery_days')->nullable();
            $table->integer('max_delivery_days')->nullable();

            // Carrier Integration
            $table->string('carrier')->nullable();
            $table->string('service_code')->nullable();
            $table->jsonb('carrier_settings')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);

            $table->timestamps();
            $table->softDeletes();
            $table->jsonb('data')->nullable();

            $table->index(['shipping_zone_id', 'is_active']);
            $table->index(['calculation_method', 'is_active']);
            $table->index(['channel_id', 'currency']);
        });

        // Weight-based Rate Tiers
        Schema::create('shipping_rate_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_rate_id')->constrained('shipping_rates')->cascadeOnDelete();

            $table->decimal('min_value', 10, 2)->comment('Min weight or price');
            $table->decimal('max_value', 10, 2)->nullable()->comment('Max weight or price');
            $table->decimal('price', 10, 2);

            $table->timestamps();

            $table->index(['shipping_rate_id', 'min_value', 'max_value']);
        });

        // Shipping Rate Exclusions (Products that don't qualify)
        Schema::create('shipping_rate_product_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_rate_id')->constrained('shipping_rates')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shipping_rate_id', 'product_id'], 'ship_rate_prod_excl_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rate_product_exclusions');
        Schema::dropIfExists('shipping_rate_tiers');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
    }
};
