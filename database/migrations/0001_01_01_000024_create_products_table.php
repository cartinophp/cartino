<?php

use Cartino\Database\Migrations\Concerns\HasJsonFields;
use Cartino\Database\Migrations\Concerns\HasPublishing;
use Cartino\Database\Migrations\Concerns\HasReference;
use Cartino\Database\Migrations\Concerns\HasSeo;
use Cartino\Database\Migrations\Concerns\HasSiteScope;
use Cartino\Database\Migrations\Concerns\HasSlug;
use Cartino\Database\Migrations\Concerns\HasStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasJsonFields, HasPublishing, HasReference, HasSeo, HasSiteScope, HasSlug, HasStatus;

    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Multi-tenancy
            $this->addSiteScope($table); // auto-index [site_id, status]

            // Base
            $table->string('title');
            $this->addSiteSlug($table); // slug with site scope
            $this->addHandle($table, unique: false); // unique per site

            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();

            // Product Classification
            $table->string('product_type')->default('physical');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained('product_types')->nullOnDelete();

            // Tags (opzioni prodotto definite in product_options — tabella relazionale normalizzata)
            $table->jsonb('tags')->nullable();

            // SEO
            $this->addSeoFields($table);

            // Shopify-specific
            $table->string('template_suffix')->nullable();
            $table->boolean('requires_selling_plan')->default(false);

            // Status & Publishing
            $this->addStatus($table, default: 'draft');
            $this->addPublishingFields($table, withScope: true);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Custom fields (Statamic-style)
            $this->addDataField($table);

            // Unique constraints
            $table->unique(['slug', 'site_id']);
            $table->unique(['handle', 'site_id']);

            // Additional indexes
            $table->index(['brand_id', 'product_type_id']);
            $table->index(['product_type', 'status']);
            $table->index('requires_selling_plan');
            $table->index('created_at');

            // Composite indexes for common queries
            $table->index(['brand_id', 'status']);
            $table->index(['product_type_id', 'status']);

            // Full text search
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'description', 'excerpt']);
            }
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
