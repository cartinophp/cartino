<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_customer_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained('catalogs')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained('customer_groups')->cascadeOnDelete();

            // Priority for when multiple catalogs are assigned (lower number = higher priority)
            $table->integer('priority')->default(0);

            // Status of the assignment
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Date range for when this catalog assignment is valid
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Timestamps
            $table->timestamps();

            $table->jsonb('data')->nullable();

            // Indexes
            $table->unique(['catalog_id', 'customer_group_id']);
            $table->index(['customer_group_id', 'status', 'priority']);
            $table->index(['catalog_id', 'status']);
            $table->index(['starts_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_customer_group');
    }
};
