<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Product Options System (v2)
 *
 * Replaces the old product_options structure with a more flexible global system:
 * - OLD: product_options with JSONB values tied to specific products
 * - NEW: Global options with separate values table, reusable across products
 *
 * Structure:
 * - product_options: Global option definitions (Color, Size, etc.)
 * - product_option_values: Values for each option (Red, Blue, XL, etc.)
 * - product_product_option: Links products to their available options
 * - product_variant_option_value: Links variants to their specific option values
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop old structure (from migration 2025_01_01_000013)
        // Order matters: drop tables with foreign keys first
        Schema::dropIfExists('product_product_option');
        Schema::dropIfExists('product_options');

        // 1. Global Product Options (reusable across products)
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Color, Size, Material, etc.
            $table->string('slug')->unique();
            $table->string('type')->default('select'); // select, swatch, text, radio
            $table->integer('position')->default(0);
            $table->boolean('is_global')->default(true);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('use_for_variants')->default(true);
            $table->jsonb('configuration')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['slug', 'is_global']);
            $table->index('position');
        });

        // 2. Product Option Values (Red, Blue, XL, Cotton, etc.)
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // Display name: "Red", "Blue", "XL"
            $table->string('value'); // Internal value: "red", "blue", "xl"
            $table->string('color_hex')->nullable(); // For color swatches: #FF0000
            $table->string('image_url')->nullable(); // For image swatches
            $table->integer('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['product_option_id', 'position']);
            $table->index('value');
        });

        // 3. Pivot: Products <-> Options (many-to-many)
        Schema::create('product_product_option', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            // Constraints & Indexes
            $table->unique(['product_id', 'product_option_id']);
            $table->index('position');
        });

        // 4. Pivot: Variants <-> Option Values (many-to-many)
        // Defines which option values a specific variant has
        // Example: Variant #123 -> Color: Red, Size: XL
        Schema::create('product_variant_option_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_value_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Constraints & Indexes
            $table->unique(['product_variant_id', 'product_option_id'], 'variant_option_unique');
            $table->index('product_option_value_id');
        });
    }

    public function down(): void
    {
        // Drop in reverse order (foreign keys first)
        Schema::dropIfExists('product_variant_option_value');
        Schema::dropIfExists('product_product_option');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');

        // Recreate old structure for rollback compatibility
        // This matches the original structure from migration 2025_01_01_000013
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->integer('position')->default(1);
            $table->jsonb('values');
            $table->timestamps();

            // Constraints & Indexes
            $table->unique(['product_id', 'name']);
            $table->index(['product_id', 'position']);
        });
    }
};
