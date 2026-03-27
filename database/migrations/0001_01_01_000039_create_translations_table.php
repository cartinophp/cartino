<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation
            $table->morphs('translatable');

            // Translation context
            $table->string('locale', 10)->index();
            $table->string('key')->index(); // field name: 'name', 'description', 'slug', etc.

            // Translation value
            $table->text('value')->nullable();

            // Metadata
            $table->boolean('is_verified')->default(false);
            $table->string('source')->nullable(); // manual, auto, import
            $table->foreignId('translated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Unique constraint: one translation per model/field/locale
            $table->unique(['translatable_type', 'translatable_id', 'locale', 'key'], 'translations_unique');

            // Composite indexes for queries
            $table->index(['translatable_type', 'translatable_id', 'locale']);
            $table->index(['locale', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
