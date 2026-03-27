<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('product_sku');
            $table->json('product_options')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'product_variant_id']);

            // Additional filter indexes
            $table->index('product_sku');
            $table->index('quantity');
            $table->index('unit_price');
            $table->index('line_total');
            $table->index('created_at');

            // Composite indexes for common filter combinations (covers order_id standalone)
            $table->index(['order_id', 'product_id']);
            $table->index(['product_id', 'quantity']);
            $table->index(['unit_price', 'quantity']);
            $table->index(['line_total', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};
