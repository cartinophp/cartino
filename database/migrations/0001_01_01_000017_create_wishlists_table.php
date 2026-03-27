<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('name')->default('My Wishlist');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('share_token')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'is_public']);
            $table->index('share_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
