<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');

            // Supplier-specific product information
            $table->string('supplier_sku')->nullable(); // Supplier's SKU for this product
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->integer('minimum_order_quantity')->default(1);
            $table->integer('lead_time_days')->nullable();

            // Pricing tiers (quantity breaks)
            $table->jsonb('price_tiers')->nullable(); // [{"min_qty": 10, "price": 9.99}, ...]

            // Product details from supplier
            $table->string('supplier_name')->nullable(); // Supplier's name for the product
            $table->text('supplier_description')->nullable();
            $table->string('manufacturer_part_number')->nullable();
            $table->string('barcode')->nullable();

            // Status and preferences
            $table->boolean('is_primary')->default(false); // Primary supplier for this product
            $table->string('status')->default('active'); // active, inactive, discontinued
            $table->integer('priority')->default(0); // Sourcing priority

            // Quality and delivery tracking
            $table->decimal('quality_rating', 3, 2)->nullable(); // 1.00 to 5.00
            $table->decimal('delivery_rating', 3, 2)->nullable(); // 1.00 to 5.00
            $table->integer('order_count')->default(0);
            $table->timestamp('last_ordered_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'supplier_id']);
            $table->index(['supplier_id', 'status']);
            $table->index(['product_id', 'is_primary']);
            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_suppliers');
    }
};
