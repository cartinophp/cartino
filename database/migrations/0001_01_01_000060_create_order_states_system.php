<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Order States System
 *
 * Implements a flexible order state machine inspired by Shopware and Sylius.
 * Instead of hardcoded statuses, orders transition through configurable states
 * with flags, notifications, and permissions.
 *
 * Benefits:
 * - Customizable workflow per business needs
 * - Track state history for audit trail
 * - Automated notifications per state
 * - Permission control (customer can cancel?)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Order States Definition
        Schema::create('order_states', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)
                ->unique()
                ->comment('Unique identifier for programmatic access');

            $table->string('name')
                ->comment('Display name');

            $table->string('color', 7)
                ->default('#6b7280')
                ->comment('Hex color for UI (e.g., #10b981)');

            $table->text('description')->nullable();

            // State flags for business logic
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_shipped')->default(false);
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_cancelled')->default(false);
            $table->boolean('is_refunded')->default(false);
            $table->boolean('is_final')
                ->default(false)
                ->comment('No further state changes allowed');

            // Notification settings
            $table->boolean('send_email')->default(false);
            $table->string('email_template')->nullable();
            $table->boolean('send_sms')->default(false);

            // Permission settings
            $table->boolean('customer_can_view')->default(true);
            $table->boolean('customer_can_cancel')->default(false);

            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
            $table->index(['is_paid', 'is_shipped']);
        });

        // Order State History (audit trail)
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('from_state_id')
                ->nullable()
                ->constrained('order_states')
                ->nullOnDelete()
                ->comment('Previous state (null for initial state)');

            $table->foreignId('to_state_id')
                ->constrained('order_states')
                ->cascadeOnDelete()
                ->comment('New state');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Who made the change (null = system)');

            $table->text('notes')->nullable();
            $table->json('metadata')
                ->nullable()
                ->comment('Additional context (e.g., tracking number, refund ID)');

            $table->timestamps();

            $table->index(['order_id', 'created_at']);
            $table->index('to_state_id');
        });

        // Add state_id to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('state_id')
                ->after('status')
                ->nullable() // Temporary nullable for migration
                ->constrained('order_states');

            // Additional order enhancements
            $table->boolean('is_test')
                ->default(false)
                ->after('state_id')
                ->comment('Test/sandbox orders');

            $table->timestamp('confirmed_at')
                ->nullable()
                ->after('is_test');

            $table->timestamp('processed_at')
                ->nullable()
                ->after('confirmed_at');

            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('processed_at');

            $table->string('cancel_reason')
                ->nullable()
                ->after('cancelled_at');

            // Risk assessment
            $table->enum('risk_level', ['low', 'medium', 'high'])
                ->nullable()
                ->after('cancel_reason')
                ->comment('Fraud risk assessment');

            $table->text('risk_message')
                ->nullable()
                ->after('risk_level');

            // Order source
            $table->enum('source', ['web', 'mobile', 'pos', 'api', 'manual'])
                ->default('web')
                ->after('risk_message');

            // Cart reference
            $table->foreignId('cart_id')
                ->nullable()
                ->after('source')
                ->constrained('carts')
                ->nullOnDelete();

            // Customer data snapshot (preserve at order time)
            $table->json('customer_snapshot')
                ->nullable()
                ->after('cart_id')
                ->comment('Customer data at order time (email, name, etc.)');

            // Notes
            $table->text('customer_note')
                ->nullable()
                ->after('customer_snapshot');

            $table->text('merchant_note')
                ->nullable()
                ->after('customer_note');

            // Tags and custom attributes
            $table->json('tags')
                ->nullable()
                ->after('merchant_note');

            $table->json('custom_attributes')
                ->nullable()
                ->after('tags');

            // Indexes
            $table->index('state_id');
            $table->index('is_test');
            $table->index('risk_level');
            $table->index('source');
            $table->index(['state_id', 'is_test']);
        });

        // Seed default order states
        $this->seedDefaultOrderStates();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['cart_id']);

            $table->dropIndex(['state_id', 'is_test']);
            $table->dropIndex(['source']);
            $table->dropIndex(['risk_level']);
            $table->dropIndex(['is_test']);
            $table->dropIndex(['state_id']);

            $table->dropColumn([
                'state_id',
                'is_test',
                'confirmed_at',
                'processed_at',
                'cancelled_at',
                'cancel_reason',
                'risk_level',
                'risk_message',
                'source',
                'cart_id',
                'customer_snapshot',
                'customer_note',
                'merchant_note',
                'tags',
                'custom_attributes',
            ]);
        });

        Schema::dropIfExists('order_histories');
        Schema::dropIfExists('order_states');
    }

    /**
     * Seed default order states.
     */
    protected function seedDefaultOrderStates(): void
    {
        $states = [
            [
                'code' => 'pending',
                'name' => 'Pending Payment',
                'color' => '#f59e0b',
                'description' => 'Order created, waiting for payment',
                'is_paid' => false,
                'send_email' => true,
                'email_template' => 'order_pending',
                'customer_can_cancel' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'paid',
                'name' => 'Paid',
                'color' => '#10b981',
                'description' => 'Payment received, ready to process',
                'is_paid' => true,
                'send_email' => true,
                'email_template' => 'order_paid',
                'sort_order' => 2,
            ],
            [
                'code' => 'processing',
                'name' => 'Processing',
                'color' => '#3b82f6',
                'description' => 'Order is being prepared',
                'is_paid' => true,
                'send_email' => false,
                'sort_order' => 3,
            ],
            [
                'code' => 'shipped',
                'name' => 'Shipped',
                'color' => '#8b5cf6',
                'description' => 'Order has been shipped',
                'is_paid' => true,
                'is_shipped' => true,
                'send_email' => true,
                'email_template' => 'order_shipped',
                'sort_order' => 4,
            ],
            [
                'code' => 'in_transit',
                'name' => 'In Transit',
                'color' => '#a855f7',
                'description' => 'Package is on the way',
                'is_paid' => true,
                'is_shipped' => true,
                'send_email' => false,
                'sort_order' => 5,
            ],
            [
                'code' => 'delivered',
                'name' => 'Delivered',
                'color' => '#059669',
                'description' => 'Order delivered to customer',
                'is_paid' => true,
                'is_shipped' => true,
                'is_delivered' => true,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'order_delivered',
                'sort_order' => 6,
            ],
            [
                'code' => 'cancelled',
                'name' => 'Cancelled',
                'color' => '#dc2626',
                'description' => 'Order cancelled',
                'is_cancelled' => true,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'order_cancelled',
                'sort_order' => 98,
            ],
            [
                'code' => 'refunded',
                'name' => 'Refunded',
                'color' => '#dc2626',
                'description' => 'Order refunded',
                'is_refunded' => true,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'order_refunded',
                'sort_order' => 99,
            ],
        ];

        foreach ($states as $state) {
            DB::table('order_states')->insert(array_merge($state, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
};
