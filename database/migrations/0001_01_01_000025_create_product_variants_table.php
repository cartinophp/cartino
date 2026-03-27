<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Product Variants Table
 *
 * Variants are the actual sellable SKUs of a product.
 * Each product can have multiple variants based on different option combinations.
 *
 * Example:
 * Product: "T-Shirt"
 * - Variant 1: Red / Small
 * - Variant 2: Red / Medium
 * - Variant 3: Blue / Small
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedBigInteger('site_id')->nullable();

            // Variant Identity
            $table->string('title'); // e.g., "Red / Large"
            $table->string('sku')->index();
            $table->string('barcode')->nullable();

            // Option Values (fino a 3 opzioni per variante, coerente con product_options)
            // Le definizioni delle opzioni stanno in product_options (tabella normalizzata)
            $table->string('option1')->nullable(); // e.g., "Red"
            $table->string('option2')->nullable(); // e.g., "Large"
            $table->string('option3')->nullable(); // e.g., "Cotton"

            // PRICING - I dati principali stanno qui nelle varianti
            $table->decimal('price', 15, 2);
            $table->decimal('compare_at_price', 15, 2)->nullable(); // Compare price
            $table->decimal('cost', 15, 2)->nullable(); // Cost price

            // INVENTORY
            // inventory_quantity e' una CACHE AGGREGATA di location_inventories.available_quantity.
            // Aggiornata automaticamente da LocationInventoryObserver. Non scrivere direttamente.
            $table->integer('inventory_quantity')->default(0)->comment('CACHE: somma di location_inventories.available_quantity');
            $table->boolean('track_quantity')->default(true);
            $table->string('inventory_management')->default('managed')->comment('managed, not_managed, fulfillment_service');
            $table->string('inventory_policy')->default('deny'); // deny, continue
            $table->string('fulfillment_service')->default('manual');
            $table->integer('inventory_quantity_adjustment')->default(0);
            $table->boolean('allow_out_of_stock_purchases')->default(false);

            // PHYSICAL PROPERTIES - I dati principali stanno qui nelle varianti
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit', 10)->default('kg'); // kg, g, lb, oz
            $table->jsonb('dimensions')->nullable(); // length, width, height

            // SHIPPING & TAX - I dati principali stanno qui nelle varianti
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('taxable')->default(true);
            $table->string('tax_code')->nullable();

            // Display and Ordering
            $table->integer('position')->default(1); // Display order

            // Status della variante specifica
            $table->string('status')->default('active');
            $table->boolean('available')->default(true); // Available for sale

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable();

            // Primary Indexes
            $table->unique(['sku', 'site_id']);
            $table->index(['product_id', 'position']); // List variants for a product, ordered
            $table->index(['site_id', 'status']); // Filter variants by site and status

            // Variant Options (for filtering by color, size, etc.)
            $table->index(['option1', 'option2', 'option3']);

            // Inventory & Availability
            $table->index(['product_id', 'status']);
            $table->index(['inventory_quantity', 'inventory_policy']); // Stock management
            $table->index(['track_quantity', 'inventory_quantity']); // Low stock alerts

            // Pricing
            $table->index(['price', 'compare_at_price']); // Price range filters

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
