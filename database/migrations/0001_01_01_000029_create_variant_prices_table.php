<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Advanced Pricing Table - Hierarchical with fallbacks
        Schema::create('variant_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();

            // Context: What price applies to
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->cascadeOnDelete();
            $table->foreignId('catalog_id')->nullable()->constrained('catalogs')->cascadeOnDelete();
            $table->string('currency', 3);

            // Pricing
            $table->decimal('price', 15, 4);
            $table->decimal('compare_at_price', 15, 4)->nullable();
            $table->decimal('cost', 15, 4)->nullable();

            // Tier Pricing (B2B wholesale)
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();

            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Priority (higher = takes precedence)
            $table->integer('priority')->default(0)->comment('Higher priority prices override lower ones');

            $table->timestamps();
            $table->jsonb('data')->nullable();

            // Indexes for fast lookups
            $table->index(['product_variant_id', 'site_id', 'customer_group_id', 'min_quantity'], 'idx_variant_context');
            $table->index(['product_variant_id', 'currency', 'starts_at', 'ends_at'], 'idx_variant_schedule');
            $table->index(['priority', 'starts_at', 'ends_at']);
            $table->index(['catalog_id', 'currency']);

            // Composite for most common query
            $table->index([
                'product_variant_id',
                'site_id',
                'channel_id',
                'customer_group_id',
                'currency',
                'priority',
            ], 'idx_price_resolution');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('variant_prices');
    }
};
