<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable');
            $table->enum('type', ['billing', 'shipping', 'both'])->default('shipping');
            $table->string('label')->nullable(); // "Home", "Office", "Warehouse", etc.
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            // Geocoding fields (Shopify/Shopware style)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('formatted_address')->nullable();
            $table->string('place_id')->nullable(); // Google Places ID

            // Validation
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->string('validation_source')->nullable(); // google, ups, usps, manual

            // Default address
            $table->boolean('is_default')->default(false);
            $table->boolean('is_default_billing')->default(false);
            $table->boolean('is_default_shipping')->default(false);

            // Additional metadata
            $table->jsonb('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['type', 'is_default']);
            $table->index(['addressable_type', 'addressable_id', 'type']);
            $table->index(['country_id', 'city']);
            $table->index(['postal_code', 'country_id']);
            $table->index(['latitude', 'longitude']);
            $table->index(['is_validated', 'validation_source']);

            // Composite indexes for common queries
            $table->index(['addressable_type', 'addressable_id', 'is_default_shipping'], 'addr_addrable_def_ship_idx');
            $table->index(['addressable_type', 'addressable_id', 'is_default_billing'], 'addr_addrable_def_bill_idx');

            // Full text search
            if (config('database.default') === 'mysql') {
                $table->fullText(['first_name', 'last_name', 'company', 'address_line_1', 'city'], 'addr_fulltext_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
