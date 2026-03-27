<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('provider');
            $table->json('config')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'deprecated'])->default('inactive');
            $table->boolean('is_default')->default(false);
            $table->json('supported_currencies')->nullable();
            $table->string('webhook_url')->nullable();
            $table->boolean('test_mode')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
