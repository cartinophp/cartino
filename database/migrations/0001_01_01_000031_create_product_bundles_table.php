<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product Bundles Table
 *
 * Enables composite products (PrestaShop "packs", Shopware "product streams")
 * A bundle product can contain multiple bundled products with configurable quantities.
 *
 * Example: "Gaming PC Bundle" contains:
 * - 1x Gaming PC
 * - 1x Gaming Mouse
 * - 1x Gaming Keyboard (optional with 10% discount)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();

            // The parent product (bundle)
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('The bundle product');

            // The bundled product
            $table->foreignId('bundled_product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('The product included in the bundle');

            // Quantity of bundled product
            $table->integer('quantity')
                ->default(1)
                ->comment('How many of this product are in the bundle');

            // Optional discount for this specific bundled product
            $table->decimal('discount_percent', 5, 2)
                ->nullable()
                ->comment('Discount percentage for this bundled item (0-100)');

            // Whether this bundled product is optional
            $table->boolean('is_optional')
                ->default(false)
                ->comment('If true, customer can choose to exclude this item');

            // Display order
            $table->integer('sort_order')
                ->default(0)
                ->comment('Order in which bundled products are displayed');

            $table->timestamps();

            // Constraints
            $table->unique(['product_id', 'bundled_product_id'], 'product_bundle_unique');

            // Indexes
            $table->index(['product_id', 'sort_order']);
            $table->index('bundled_product_id');
            $table->index(['product_id', 'is_optional']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bundles');
    }
};
