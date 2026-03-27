<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('restrict');

            // Order item details
            $table->string('sku'); // Product SKU at time of order
            $table->string('name'); // Product name at time of order
            $table->text('description')->nullable();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->integer('quantity_cancelled')->default(0);

            // Pricing
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->string('currency', 3)->default('EUR');

            // Supplier information
            $table->string('supplier_sku')->nullable();
            $table->string('supplier_name')->nullable(); // Product name from supplier

            // Receiving tracking
            $table->string('status')->default('pending'); // pending, partial, received, cancelled
            $table->jsonb('received_batches')->nullable(); // Track multiple receives
            $table->timestamp('first_received_at')->nullable();
            $table->timestamp('fully_received_at')->nullable();

            // Quality control
            $table->text('notes')->nullable();
            $table->jsonb('quality_checks')->nullable(); // QC results
            $table->boolean('requires_inspection')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_order_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index(['status', 'requires_inspection']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
