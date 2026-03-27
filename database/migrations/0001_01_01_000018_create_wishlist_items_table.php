<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained('wishlists')->cascadeOnDelete();
            $table->string('product_type'); // entry, collection, external
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_handle')->nullable();
            $table->json('product_data')->nullable(); // cached product info
            $table->json('variant_data')->nullable(); // selected variant
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_time', 10, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['wishlist_id', 'product_type']);
            $table->index(['product_type', 'product_id']);
            $table->unique(['wishlist_id', 'product_type', 'product_id', 'product_handle'], 'unique_wishlist_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
