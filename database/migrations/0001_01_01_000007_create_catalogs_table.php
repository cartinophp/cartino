<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Currency settings
            $table->string('currency', 3)->default('USD');

            // Price adjustments (applied to all products in catalog)
            $table->enum('adjustment_type', ['percentage', 'fixed_amount'])->nullable();
            $table->enum('adjustment_direction', ['increase', 'decrease'])->nullable();
            $table->decimal('adjustment_value', 10, 4)->nullable();

            // Catalog behavior settings
            $table->boolean('auto_include_new_products')->default(false);
            $table->boolean('is_default')->default(false);

            // Status and publishing
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable();

            // Indexes
            $table->index(['status', 'published_at']);
            $table->index(['currency', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
