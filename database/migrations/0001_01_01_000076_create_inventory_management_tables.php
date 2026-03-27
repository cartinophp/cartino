<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Advanced inventory management inspired by Shopify, Shopware, and Medusa.
     * Multi-location inventory tracking, stock movements, and reservations.
     */
    public function up(): void
    {
        // Inventory Items - Links variants to inventory locations
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('sku');
            $table->string('barcode')->nullable();
            $table->boolean('tracked')->default(true);
            $table->boolean('requires_shipping')->default(true);
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('country_code_of_origin', 2)->nullable();
            $table->string('province_code_of_origin')->nullable();
            $table->string('harmonized_system_code')->nullable(); // HS code for customs
            $table->timestamps();

            $table->unique(['product_variant_id']);
            $table->index(['sku', 'tracked']);
        });

        // Inventory Levels - Stock quantities per location
        Schema::create('inventory_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('inventory_location_id')->constrained('inventory_locations')->cascadeOnDelete();

            // Stock quantities
            $table->integer('available')->default(0); // Available for sale
            $table->integer('reserved')->default(0); // Reserved for orders
            $table->integer('incoming')->default(0); // Incoming stock
            $table->integer('damaged')->default(0); // Damaged stock

            // Reorder configuration
            $table->integer('reorder_point')->nullable();
            $table->integer('reorder_quantity')->nullable();
            $table->integer('safety_stock')->nullable();

            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['inventory_item_id', 'inventory_location_id']);
            $table->index(['inventory_location_id', 'available']);
            $table->index(['available', 'reserved']);
        });

        // Stock Reservations - Reserve stock for orders
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_level_id')->constrained('inventory_levels')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_line_id')->nullable()->constrained('order_lines')->cascadeOnDelete();

            $table->integer('quantity')->default(1);
            $table->string('status')->default('reserved'); // reserved, fulfilled, cancelled, expired

            $table->timestamp('reserved_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index(['inventory_level_id', 'status']);
        });

        // Stock Transfers - Move inventory between locations
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('to_location_id')->constrained('inventory_locations')->cascadeOnDelete();

            $table->string('status')->default('pending'); // pending, in_transit, received, cancelled

            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            $table->index(['from_location_id', 'status']);
            $table->index(['to_location_id', 'status']);
            $table->index(['status', 'requested_at']);
        });

        // Stock Transfer Items
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();

            $table->integer('quantity_requested');
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_received')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['stock_transfer_id', 'inventory_item_id'], 'stock_transfer_items_idx');
        });

        // Stock Adjustments - Manual inventory corrections
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->string('adjustment_number')->unique();

            $table->string('reason'); // damage, loss, found, count, correction
            $table->text('notes')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at');

            $table->timestamps();

            $table->index(['inventory_location_id', 'adjusted_at']);
            $table->index(['reason', 'adjusted_at']);
        });

        // Stock Adjustment Items
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();

            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->integer('quantity_change'); // Can be negative

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['stock_adjustment_id', 'inventory_item_id'], 'stock_adjustment_items_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_levels');
        Schema::dropIfExists('inventory_items');
    }
};
