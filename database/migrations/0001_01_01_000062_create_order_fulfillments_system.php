<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Order Fulfillments System
 *
 * Enables partial fulfillments, split shipments, and detailed tracking.
 * Inspired by Shopify's fulfillment system.
 *
 * Features:
 * - Multiple fulfillments per order (split shipments)
 * - Partial fulfillments (ship some items now, others later)
 * - Tracking integration with carriers
 * - Multi-warehouse support
 * - Returns/RMA management
 */
return new class extends Migration
{
    public function up(): void
    {
        // Order Fulfillments
        Schema::create('order_fulfillments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('fulfillment_number')
                ->unique()
                ->comment('Unique identifier (e.g., FUL-2025-001234)');

            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'in_transit',
                'delivered',
                'failed',
            ])->default('pending');

            // Warehouse information
            $table->foreignId('warehouse_id')
                ->nullable()
                ->comment('Which warehouse fulfilled this (if using multi-warehouse)');

            // Carrier/shipping information
            $table->foreignId('carrier_id')
                ->nullable()
                ->constrained('couriers')
                ->nullOnDelete();

            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();

            // Which items are in this fulfillment (JSON array)
            $table->json('items')
                ->comment('Array of {order_line_id, quantity} for partial fulfillments');

            $table->text('notes')->nullable();

            // Status timestamps
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index('tracking_number');
            $table->index('fulfillment_number');
            $table->index(['status', 'created_at']);

            // Add foreign key for warehouse (will be created in warehouse migration)
            // $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
        });

        // Order Returns / RMA (Return Merchandise Authorization)
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('return_number')
                ->unique()
                ->comment('Unique identifier (e.g., RET-2025-001234)');

            $table->enum('status', [
                'requested',
                'approved',
                'rejected',
                'received',
                'refunded',
            ])->default('requested');

            $table->enum('reason', [
                'damaged',
                'defective',
                'wrong_item',
                'not_as_described',
                'unwanted',
                'other',
            ])->nullable();

            $table->text('reason_details')->nullable();

            // Which items are being returned (JSON array)
            $table->json('items')
                ->comment('Array of {order_line_id, quantity, condition}');

            $table->decimal('refund_amount', 10, 2);

            // Restocking
            $table->boolean('restock')
                ->default(true)
                ->comment('Add items back to inventory?');

            $table->foreignId('warehouse_id')
                ->nullable()
                ->comment('Which warehouse receives the return');

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('merchant_notes')->nullable();

            // Status timestamps
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index('return_number');
            $table->index(['status', 'created_at']);

            // Add foreign key for warehouse
            // $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
        });

        // Add fulfillment tracking to order_lines
        Schema::table('order_lines', function (Blueprint $table) {
            $table->integer('quantity_fulfilled')
                ->default(0)
                ->after('quantity')
                ->comment('How many units have been shipped');

            $table->integer('quantity_returned')
                ->default(0)
                ->after('quantity_fulfilled')
                ->comment('How many units have been returned');

            $table->index(['quantity', 'quantity_fulfilled']);
        });
    }

    public function down(): void
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropIndex(['quantity', 'quantity_fulfilled']);
            $table->dropColumn(['quantity_fulfilled', 'quantity_returned']);
        });

        Schema::dropIfExists('order_returns');
        Schema::dropIfExists('order_fulfillments');
    }
};
