<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->json('product_options')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['cart_id', 'product_id', 'product_variant_id']);
            $table->index('cart_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_lines');
    }
};
