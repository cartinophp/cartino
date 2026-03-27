<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('favoritable_type'); // entries, collections, brands, etc.
            $table->unsignedBigInteger('favoritable_id');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'favoritable_type']);
            $table->index(['favoritable_type', 'favoritable_id']);
            $table->unique(['customer_id', 'favoritable_type', 'favoritable_id'], 'unique_customer_favorite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
