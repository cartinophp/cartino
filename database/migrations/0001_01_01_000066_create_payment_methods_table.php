<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider'); // stripe, paypal, square, etc.
            $table->text('description')->nullable();
            $table->json('configuration'); // API keys, settings, etc.
            $table->string('status')->default('active'); // active, inactive, maintenance
            $table->boolean('is_test_mode')->default(false);
            $table->decimal('fixed_fee', 8, 2)->default(0);
            $table->decimal('percentage_fee', 5, 4)->default(0); // 2.9% = 0.0290
            $table->json('supported_currencies')->nullable();
            $table->json('supported_countries')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
