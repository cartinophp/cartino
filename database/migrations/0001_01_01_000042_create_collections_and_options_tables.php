<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('handle')->nullable();
            $table->text('description')->nullable();
            $table->text('body_html')->nullable();
            $table->string('collection_type')->default('manual');
            $table->jsonb('rules')->nullable();
            $table->string('sort_order')->default('manual');
            $table->boolean('disjunctive')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->jsonb('seo')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('published_scope')->default('web');
            $table->string('template_suffix')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->jsonb('data')->nullable();

            // Indexes
            $table->unique(['slug', 'site_id']);
            $table->unique(['handle', 'site_id']);
            $table->index(['site_id', 'status']);
            $table->index(['collection_type', 'status']);
            $table->index(['published_at', 'status']);

            // Additional filter indexes
            $table->index('sort_order');
            $table->index('disjunctive');
            $table->index('published_scope');
            $table->index('created_at');
            $table->index('updated_at');

            // Composite indexes for common filter combinations
            $table->index(['status', 'collection_type']);
            $table->index(['published_scope', 'status']);
            $table->index(['sort_order', 'status']);

            // Full text search (MySQL 5.6+)
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'description']);
            }

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Rimuovere body_html se non necessario — semanticamente sovrapposto a description
            // Lasciato per compatibilita' con import da Shopify
        });

        // Product Options (Color, Size, Material variants)
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Option Definition
            $table->string('name'); // Color, Size, Material
            $table->integer('position')->default(1); // 1, 2, 3 (max 3 options in Shopify)
            $table->jsonb('values'); // ["Red", "Blue", "Green"] or ["Small", "Medium", "Large"]

            $table->timestamps();

            // Indexes
            $table->unique(['product_id', 'name']);
            $table->index(['product_id', 'position']);
        });

        // Many-to-many relationship between collections and products
        Schema::create('collection_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('position')->default(0); // Manual sorting within collection
            $table->boolean('featured')->default(false); // Featured in this collection
            $table->timestamps();

            $table->unique(['collection_id', 'product_id']);
            $table->index(['product_id', 'collection_id']);
            $table->index(['collection_id', 'position']);
            $table->index(['collection_id', 'featured', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_products');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('collections');
    }
};
