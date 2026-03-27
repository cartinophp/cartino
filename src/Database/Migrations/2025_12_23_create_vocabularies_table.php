<?php

declare(strict_types=1);

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
        Schema::create('vocabularies', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index()->comment('Vocabulary group: order_status, payment_status, etc.');
            $table->string('code', 50)->index()->comment('Unique code within group');
            $table->json('labels')->comment('Translations: {"it":"In attesa","en":"Pending"}');
            $table->integer('sort_order')->default(0)->index()->comment('Display order');
            $table->json('meta')->nullable()->comment('Metadata: color, is_final, allowed_transitions, etc.');
            $table->boolean('is_system')->default(false)->comment('System vocabulary (cannot be deleted)');
            $table->boolean('is_active')->default(true)->index()->comment('Active status');
            $table->timestamps();

            // Unique constraint on group + code
            $table->unique(['group', 'code'], 'vocabularies_group_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabularies');
    }
};
