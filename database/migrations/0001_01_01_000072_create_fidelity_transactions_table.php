<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fidelity_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fidelity_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted']);
            $table->integer('points'); // Can be negative for redemptions/expirations
            $table->text('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('expired')->default(false);
            $table->foreignId('reference_transaction_id')->nullable()->constrained('fidelity_transactions')->onDelete('set null');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['fidelity_card_id', 'type']);
            $table->index(['fidelity_card_id', 'created_at']);
            $table->index(['expires_at', 'expired']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fidelity_transactions');
    }
};
