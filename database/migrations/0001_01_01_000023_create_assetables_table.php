<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Assetables - Polymorphic pivot table
     * Connects assets to any model (Product, Category, Brand, etc.)
     */
    public function up(): void
    {
        Schema::create('assetables', function (Blueprint $table) {
            $table->id();

            // Asset reference
            $table->foreignId('asset_id')
                ->constrained('assets')
                ->cascadeOnDelete();

            // Polymorphic relation
            $table->string('assetable_type'); // 'Cartino\Models\Product'
            $table->unsignedBigInteger('assetable_id'); // product.id

            // Organization
            $table->string('collection')->default('images'); // 'images', 'gallery', 'documents', 'videos'
            $table->unsignedInteger('sort_order')->default(0);

            // Flags
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_featured')->default(false);

            // Metadata override (per-relation)
            // Allows different alt text for same image on different products
            $table->jsonb('meta')->nullable();

            $table->timestamps();

            // Unique: same asset can't be attached twice to same model in same collection
            $table->unique(['asset_id', 'assetable_type', 'assetable_id', 'collection'], 'assetables_unique');
        });

        // Indexes for performance (CRITICAL for 5M+ products)
        Schema::table('assetables', function (Blueprint $table) {
            // Get all assets for a model
            $table->index(['assetable_type', 'assetable_id'], 'idx_assetables_model');

            // Get assets for a specific collection
            $table->index(['assetable_type', 'assetable_id', 'collection'], 'idx_assetables_collection');

            // Get assets sorted
            $table->index(['assetable_type', 'assetable_id', 'collection', 'sort_order'], 'idx_assetables_sorted');

            // Get primary asset
            $table->index(['assetable_type', 'assetable_id', 'is_primary'], 'idx_assetables_primary');

            // Get all models using an asset (reverse lookup)
            $table->index(['asset_id', 'assetable_type'], 'idx_assetables_reverse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assetables');
    }
};
