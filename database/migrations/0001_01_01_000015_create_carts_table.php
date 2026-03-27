<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'abandoned', 'converted', 'expired'])->default('active');
            $table->jsonb('items')->nullable(); // Cart items data
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('EUR');

            // Abandonment tracking
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->integer('recovery_emails_sent')->default(0);
            $table->timestamp('last_recovery_email_sent_at')->nullable();
            $table->boolean('recovered')->default(false);
            $table->timestamp('recovered_at')->nullable();

            // Shipping and billing info
            $table->jsonb('shipping_address')->nullable();
            $table->jsonb('billing_address')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->jsonb('data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['site_id', 'status']);
            $table->index(['status', 'last_activity_at']);
            $table->index(['status', 'created_at']);
            $table->index(['recovered', 'abandoned_at']);
            $table->index(['customer_id', 'status']);
            $table->index(['session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
