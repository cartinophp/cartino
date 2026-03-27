<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fulfillment tracking (Shopify-style)
        Schema::create('fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->string('fulfillment_number')->unique(); // FUL-XXXXXX
            $table->enum('status', ['pending', 'in_progress', 'shipped', 'delivered', 'failed', 'cancelled'])->default('pending');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_company')->nullable();
            $table->string('tracking_url')->nullable();
            $table->jsonb('shipping_address');
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->string('shipping_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->jsonb('metadata')->nullable(); // Carrier-specific data
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['location_id', 'status']);
            $table->index(['shipped_at', 'status']);
        });

        // Line items per fulfillment
        Schema::create('fulfillment_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fulfillment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_line_id')->constrained('order_lines')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['fulfillment_id', 'order_line_id']);
            $table->index(['order_line_id']);
        });

        // Returns & refunds tracking
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('return_number')->unique(); // RET-XXXXXX
            $table->enum('status', ['requested', 'approved', 'rejected', 'in_transit', 'received', 'processed', 'refunded'])->default('requested');
            $table->enum('reason', ['defective', 'wrong_item', 'not_as_described', 'changed_mind', 'damaged', 'other']);
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->boolean('restockable')->default(true);
            $table->string('tracking_number')->nullable(); // Return shipping
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['reason', 'status']);
            $table->index(['requested_at', 'status']);
        });

        // Return line items
        Schema::create('return_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_line_id')->constrained('order_lines')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('refund_amount', 15, 2);
            $table->enum('condition', ['new', 'opened', 'damaged', 'defective'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['return_id', 'order_line_id']);
            $table->index(['order_line_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_line_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('fulfillment_line_items');
        Schema::dropIfExists('fulfillments');
    }
};
