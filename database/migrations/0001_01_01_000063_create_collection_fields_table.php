<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->string('field_name'); // Nome del campo
            $table->string('field_type'); // text, number, date, boolean, json, etc.
            $table->string('label'); // Label da mostrare nell'interfaccia
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->json('validation_rules')->nullable(); // Regole di validazione
            $table->json('field_options')->nullable(); // Opzioni specifiche del tipo di campo
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['collection_id', 'field_name']);
            $table->index(['collection_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_fields');
    }
};
