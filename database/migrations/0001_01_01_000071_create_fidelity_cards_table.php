<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fidelity_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('card_number')->unique();
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('available_points')->default(0);
            $table->unsignedInteger('total_earned')->default(0);
            $table->unsignedInteger('total_redeemed')->default(0);
            $table->decimal('total_spent_amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('issued_at');
            $table->timestamp('last_activity_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'is_active']);
            $table->index(['card_number']);
            $table->index(['total_spent_amount']);
            $table->index(['available_points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fidelity_cards');
    }
};
