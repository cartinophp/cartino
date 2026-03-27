<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('preference_type'); // table_columns, dashboard_widgets, etc.
            $table->string('preference_key');
            $table->json('preference_value');
            $table->timestamps();

            $table->unique(['user_id', 'preference_type', 'preference_key'], 'user_prefs_unique');
            $table->index(['user_id', 'preference_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
