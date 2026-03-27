<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dictionary_items', function (Blueprint $table) {
            $table->id();
            $table->string('dictionary')->index()->comment('Dictionary handle (es: address_types, order_statuses)');
            $table->string('value')->comment('Item value/key');
            $table->string('label')->comment('Display label');
            $table->json('extra')->nullable()->comment('Extra fields (icon, color, description, etc.)');
            $table->integer('order')->default(0)->comment('Sort order');
            $table->boolean('is_enabled')->default(true)->index();
            $table->boolean('is_system')->default(false)->comment('System item (cannot be deleted)');
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint
            $table->unique(['dictionary', 'value'], 'dict_value_unique');

            // Indexes for performance
            $table->index(['dictionary', 'is_enabled'], 'dict_enabled_idx');
            $table->index(['dictionary', 'order'], 'dict_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionary_items');
    }
};
