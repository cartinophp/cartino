<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->string('reference', 100)->unique(); // PO-2025-001
            $table->string('supplier_reference')->nullable(); // Supplier's order number

            // Order details
            $table->string('status')->default('draft'); // draft, sent, confirmed, partial, completed, cancelled
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('requested_delivery_date')->nullable();

            // Financial information
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('EUR');

            // Shipping information
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->jsonb('shipping_address')->nullable();

            // Additional information
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->jsonb('metadata')->nullable();

            // Timestamps for workflow
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // User tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index(['order_date', 'status']);
            $table->index(['expected_delivery_date', 'status']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
