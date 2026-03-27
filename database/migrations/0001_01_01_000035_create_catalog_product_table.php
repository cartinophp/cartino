<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained('catalogs')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Product inclusion/exclusion
            $table->boolean('is_included')->default(true);

            // Pricing per catalog gestita in variant_prices (con catalog_id valorizzato).
            // Rimossi fixed_price e compare_at_price — unica fonte di pricing e' variant_prices.

            // Quantity rules for B2B
            $table->integer('quantity_increment')->nullable()->comment('Items must be purchased in multiples of this quantity');
            $table->integer('minimum_order_quantity')->nullable()->comment('Minimum quantity that can be purchased');
            $table->integer('maximum_order_quantity')->nullable()->comment('Maximum quantity that can be purchased');

            // Volume pricing (quantity breaks) - stored as JSON
            $table->jsonb('quantity_breaks')->nullable()->comment('Array of quantity break rules: [{"quantity": 10, "price": 9.99}, ...]');

            // Publishing control at product level
            $table->boolean('is_published')->default(true);

            // Timestamps
            $table->timestamps();

            $table->jsonb('data')->nullable();

            // Indexes
            $table->unique(['catalog_id', 'product_id']);
            $table->index(['catalog_id', 'is_included', 'is_published']);
            $table->index(['product_id', 'is_included']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product');
    }
};
