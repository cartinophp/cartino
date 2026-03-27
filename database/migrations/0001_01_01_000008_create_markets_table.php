<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('handle')->unique();
            $table->string('name');
            $table->string('code', 10)->unique(); // IT-B2C, EU-Retail, US-B2B
            $table->text('description')->nullable();

            // Market configuration
            $table->string('type')->default('b2c'); // b2c, b2b, wholesale, marketplace
            $table->jsonb('countries')->nullable(); // ["IT", "FR", "ES"] - target countries
            $table->string('default_currency', 3)->default('EUR')->index();
            $table->jsonb('supported_currencies')->nullable(); // ["EUR", "USD", "GBP"]
            $table->string('default_locale', 10)->default('en_US')->index();
            $table->jsonb('supported_locales')->nullable(); // ["en_US", "it_IT", "fr_FR"]

            // Tax configuration
            $table->boolean('tax_included_in_prices')->default(false);
            $table->string('tax_region')->nullable()->index();

            // Catalog & Pricing
            $table->foreignId('catalog_id')->nullable()->constrained('catalogs')->nullOnDelete();
            $table->boolean('use_catalog_prices')->default(false);

            // Business rules
            $table->jsonb('payment_methods')->nullable(); // ["stripe", "paypal", "invoice"]
            $table->jsonb('shipping_methods')->nullable(); // available shipping methods
            $table->jsonb('fulfillment_locations')->nullable(); // warehouse/store IDs

            // Status & Priority
            $table->integer('priority')->default(0)->index();
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active')->index();
            $table->integer('order')->default(0)->index();

            // Publishing
            $table->timestamp('published_at')->nullable();
            $table->timestamp('unpublished_at')->nullable();

            // Metadata
            $table->jsonb('settings')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['handle', 'status']);
            $table->index(['code', 'status']);
            $table->index(['type', 'status']);
            $table->index(['default_currency', 'status']);
            $table->index(['is_default', 'status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
