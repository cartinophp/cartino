<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();

            // FK su collections — integrita' referenziale garantita (non piu' stringa)
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();

            // Slug univoco per collection — titolo e dati localizzati in entry_translations
            $table->string('slug');

            // Gerarchia
            $table->foreignId('parent_id')->nullable()->constrained('entries')->nullOnDelete();
            $table->integer('order')->default(0);

            // Stato e pubblicazione
            $table->string('status')->default('draft')->comment('draft, published, scheduled, archived');
            $table->timestamp('published_at')->nullable();

            // Autore
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['collection_id', 'slug']);
            $table->index(['collection_id', 'status']);
            $table->index(['collection_id', 'published_at']);
            $table->index(['status', 'published_at']);
            $table->index('parent_id');
            $table->index('order');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
