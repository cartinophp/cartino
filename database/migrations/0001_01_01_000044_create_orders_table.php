<?php

use Cartino\Enums\FulfillmentStatus;
use Cartino\Enums\OrderStatus;
use Cartino\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('order_number');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_email');
            $table->jsonb('customer_details');
            $table->foreignId('currency_id')->constrained();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('shipping_total', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('status')->default(OrderStatus::PENDING->value);
            $table->string('payment_status')->default(PaymentStatus::PENDING->value);
            $table->string('fulfillment_status')->default(FulfillmentStatus::UNFULFILLED->value);
            $table->jsonb('shipping_address');
            $table->jsonb('billing_address');
            $table->jsonb('applied_discounts')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('payment_method')->nullable();
            $table->jsonb('payment_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable();

            $table->unique(['order_number', 'site_id']);
            $table->index(['site_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['status', 'payment_status', 'fulfillment_status']);
            $table->index(['customer_email', 'site_id']);
            $table->index(['created_at', 'status']);
            $table->index(['total', 'status']);

            // Indici singoli su decimali aggregati rimossi (inutili per il query planner)
            // Usare gli indici compositi sotto per query di reporting

            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'status']);
            $table->index(['fulfillment_status', 'status']);
            $table->index(['shipped_at', 'status']);
            $table->index(['delivered_at', 'status']);
            $table->index(['currency_id', 'status']);

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
