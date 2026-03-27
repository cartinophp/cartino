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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome descrittivo della API key');
            $table->string('key')->unique()->comment('API key (hashed)');
            $table->text('description')->nullable()->comment('Descrizione uso della key');
            $table->enum('type', ['full_access', 'read_only', 'custom'])->default('custom')->comment('Tipo di accesso');
            $table->json('permissions')->nullable()->comment('Permessi specifici (per type=custom)');
            $table->timestamp('last_used_at')->nullable()->comment('Ultimo utilizzo');
            $table->timestamp('expires_at')->nullable()->comment('Data scadenza');
            $table->boolean('is_active')->default(true)->comment('Se la key è attiva');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('Utente che ha creato la key');
            $table->timestamps();

            $table->index('key');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
