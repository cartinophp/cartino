<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Channel Type (sales method)
            $table->enum('type', [
                'web',           // E-commerce website
                'mobile',        // Mobile app
                'pos',           // Point of Sale
                'marketplace',   // Amazon, eBay, etc.
                'b2b_portal',    // B2B wholesale portal
                'social',        // Social commerce (Instagram, Facebook)
                'api',           // Headless API
            ])->default('web');

            $table->string('url')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active');

            // Multi-locale support (array of locale codes)
            $table->jsonb('locales')->nullable()->comment('Supported locales for this channel ["en", "it", "fr"]');

            // Multi-currency support (array of currency codes)
            $table->jsonb('currencies')->nullable()->comment('Supported currencies for this channel ["EUR", "USD", "GBP"]');

            // Channel-specific settings
            $table->jsonb('settings')->nullable()->comment('Channel-specific configuration');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['site_id', 'type', 'status']);
            $table->index(['slug', 'site_id']);
            $table->index(['is_default', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
