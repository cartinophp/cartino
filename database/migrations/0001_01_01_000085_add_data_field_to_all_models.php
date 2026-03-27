<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a `data` JSONB field to all custom models for dynamic field management.
     * This implements a Statamic-like system where custom fields are defined via JSON schemas
     * and stored in the `data` column, allowing flexible content management without
     * database schema changes.
     *
     * Also creates revisions table for content versioning (Statamic/Shopify style).
     */
    public function up(): void
    {
        // First, create revisions table for content versioning (Statamic-style)
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->morphs('revisionable'); // The model being versioned
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Revision metadata
            $table->string('action'); // created, updated, published, unpublished, restored
            $table->string('key')->nullable(); // Unique key for this revision

            // Content snapshot
            $table->jsonb('attributes')->comment('Full snapshot of model attributes');
            $table->jsonb('changes')->nullable()->comment('Only changed attributes (delta)');

            // Publishing workflow
            $table->boolean('is_working_copy')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            // Metadata
            $table->text('message')->nullable(); // Commit message
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['revisionable_type', 'revisionable_id', 'created_at'], 'revisions_rev_created_idx');
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['is_working_copy', 'revisionable_type', 'revisionable_id'], 'revisions_working_rev_idx');
            $table->index(['is_published', 'published_at']);
        });

        // Create taxonomies system (Statamic-style)
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('handle');
            $table->string('title');
            $table->text('description')->nullable();
            $table->jsonb('data')->nullable();
            $table->jsonb('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['site_id', 'handle']);
            $table->index(['site_id', 'is_active']);
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained('taxonomies')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('slug');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->jsonb('data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['taxonomy_id', 'slug']);
            $table->index(['taxonomy_id', 'parent_id', 'order']);
            $table->index(['taxonomy_id', 'is_active']);

            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'description']);
            }
        });

        // Pivot table for attaching terms to any model (polymorphic)
        Schema::create('termables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->morphs('termable');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['term_id', 'termable_type', 'termable_id']);
            $table->index(['termable_type', 'termable_id', 'term_id']);
        });

        // Core business entities
        $tables = [
            'products',
            'product_variants',
            'customers',
            'orders',
            'order_lines',
            'brands',
            'product_types',
            'collections',
            'collection_entries',
            'addresses',
            'pages',
            'menus',
            'menu_items',
            'carts',
            'cart_lines',
            'wishlists',
            'wishlist_items',
            'favorites',
            'payment_gateways',
            'payment_methods',
            'transactions',
            'shipping_zones',
            'shipping_methods',
            'tax_rates',
            'fidelity_cards',
            'fidelity_transactions',
            'suppliers',
            'purchase_orders',
            'purchase_order_items',
            'apps',
            'app_installations',
            'app_reviews',
            'stock_notifications',
            'customer_groups',
            'categories',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'data')) {
                Schema::table($table, function (Blueprint $table) {
                    // Add JSONB field for custom data
                    // This will store schema-defined custom fields as JSON
                    $table->jsonb('data')->nullable();

                    // Add index for better query performance on data field
                    if (config('database.default') === 'pgsql') {
                        // PostgreSQL specific JSONB index
                        $table->index('data', null, 'gin');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables first
        Schema::dropIfExists('termables');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('taxonomies');
        Schema::dropIfExists('revisions');

        $tables = [
            'products',
            'product_variants',
            'customers',
            'orders',
            'order_lines',
            'brands',
            'product_types',
            'collections',
            'collection_entries',
            'addresses',
            'pages',
            'menus',
            'menu_items',
            'carts',
            'cart_lines',
            'wishlists',
            'wishlist_items',
            'favorites',
            'payment_gateways',
            'payment_methods',
            'transactions',
            'shipping_zones',
            'shipping_methods',
            'tax_rates',
            'fidelity_cards',
            'fidelity_transactions',
            'suppliers',
            'purchase_orders',
            'purchase_order_items',
            'apps',
            'app_installations',
            'app_reviews',
            'stock_notifications',
            'customer_groups',
            'categories',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('data');
                });
            }
        }
    }
};
