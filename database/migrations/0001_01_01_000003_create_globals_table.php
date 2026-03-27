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
        Schema::create('global_sets', function (Blueprint $table) {
            $table->id();
            $table->string('handle')->unique();
            $table->string('title');
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index('handle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_sets');
    }
};
