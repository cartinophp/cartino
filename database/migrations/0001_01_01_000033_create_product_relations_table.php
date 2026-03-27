<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product Relations Table
 *
 * Enables product relationships for merchandising and recommendations:
 * - Upsell: Higher-priced alternative products
 * - Cross-sell: Complementary products
 * - Related: Similar products
 * - Frequently Bought Together: Amazon-style recommendations
 *
 * Example:
 * Product: "Gaming Laptop"
 * - Upsell: "Gaming Laptop Pro" (better model)
 * - Cross-sell: "Laptop Bag", "Gaming Mouse"
 * - Related: "Other Gaming Laptops"
 * - Frequently Bought Together: "Laptop Stand", "USB Hub"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_relations', function (Blueprint $table) {
            $table->id();

            // The source product
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('The source product');

            // The related product
            $table->foreignId('related_product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('The related product');

            // Type of relationship
            $table->enum('type', [
                'upsell',
                'cross_sell',
                'related',
                'frequently_bought_together',
            ])
                ->default('related')
                ->comment('Type of product relationship');

            // Display order
            $table->integer('sort_order')
                ->default(0)
                ->comment('Order in which relations are displayed');

            $table->timestamps();

            // Constraints - a product can be related multiple times with different types
            $table->unique(['product_id', 'related_product_id', 'type'], 'product_relation_unique');

            // Indexes for filtering
            $table->index(['product_id', 'type', 'sort_order']);
            $table->index(['related_product_id', 'type']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_relations');
    }
};
