<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Price Rules Engine
 *
 * Advanced pricing system inspired by Shopware's Rule Engine.
 * Enables complex, conditional pricing based on:
 * - Customer groups, individual customers
 * - Sales channels, sites
 * - Countries, zones
 * - Cart value, quantity
 * - Product attributes (brand, category, etc.)
 * - Custom conditions
 *
 * Rules are evaluated in priority order and can stack or stop further processing.
 *
 * Example Rules:
 * 1. "VIP Customers Get 15% Off All Products" (priority: 100)
 * 2. "Buy 10+ Get 20% Off" (priority: 90)
 * 3. "Black Friday: 30% Off Gaming Category" (priority: 80, time-limited)
 * 4. "B2B Channel: 25% Off for Italy" (priority: 70, channel-specific)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();

            // Rule Identity
            $table->string('name')->comment('Human-readable rule name');
            $table->text('description')->nullable();

            // Rule Status
            $table->boolean('is_active')->default(true);
            $table->integer('priority')
                ->default(0)
                ->comment('Higher priority = evaluated first (100 > 10 > 0)');

            // Applicability: What entities does this rule apply to?
            $table->enum('entity_type', ['product', 'variant', 'category', 'cart'])
                ->default('product')
                ->comment('What type of entity this rule applies to');

            $table->json('entity_ids')
                ->nullable()
                ->comment('Specific entity IDs (null = applies to all)');

            // Conditions: When does this rule apply? (JSONB for complex queries)
            $table->jsonb('conditions')
                ->nullable()
                ->comment('
                    Complex conditions as JSON:
                    {
                        "customer_group_ids": [1, 2],
                        "customer_ids": [10, 20],
                        "channel_ids": [1],
                        "site_ids": [1],
                        "country_ids": ["IT", "US"],
                        "zone_ids": [1],
                        "min_cart_value": 100,
                        "max_cart_value": 1000,
                        "min_quantity": 5,
                        "max_quantity": 100,
                        "product_attributes": {
                            "brand_id": 5,
                            "product_type_id": 2
                        },
                        "weekdays": [1, 2, 3, 4, 5],
                        "custom_conditions": []
                    }
                ');

            // Actions: What discount to apply?
            $table->enum('discount_type', ['percent', 'fixed', 'override'])
                ->default('percent')
                ->comment('percent: reduce by %, fixed: reduce by amount, override: set exact price');

            $table->decimal('discount_value', 10, 4)
                ->comment('The discount amount (e.g., 15.00 for 15% or 10.00 for €10 off)');

            // Rule Chaining
            $table->boolean('stop_further_rules')
                ->default(false)
                ->comment('If true, no lower-priority rules will be applied');

            // Time-based Rules
            $table->timestamp('starts_at')
                ->nullable()
                ->comment('Rule becomes active at this time');

            $table->timestamp('ends_at')
                ->nullable()
                ->comment('Rule expires at this time');

            // Usage Limits
            $table->integer('usage_limit')
                ->nullable()
                ->comment('Total number of times this rule can be used (null = unlimited)');

            $table->integer('usage_limit_per_customer')
                ->nullable()
                ->comment('Max uses per customer (null = unlimited)');

            $table->integer('usage_count')
                ->default(0)
                ->comment('Current usage count');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for filtering and performance
            $table->index(['is_active', 'priority']);
            $table->index(['starts_at', 'ends_at']);
            $table->index('entity_type');
            $table->index(['entity_type', 'is_active']);
            $table->index(['priority', 'is_active']);
            $table->index('usage_count');

            // JSONB indexes for PostgreSQL (if using PostgreSQL)
            if (config('database.default') === 'pgsql') {
                $table->rawIndex('entity_ids', 'price_rules_entity_ids_gin_index', 'gin');
                $table->rawIndex('conditions', 'price_rules_conditions_gin_index', 'gin');
            }
        });

        Schema::create('price_rule_usages', function (Blueprint $table) {
            $table->id();

            // Which rule was used
            $table->foreignId('price_rule_id')
                ->constrained('price_rules')
                ->cascadeOnDelete();

            // On which order
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // By which customer (nullable for guest orders)
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->cascadeOnDelete();

            // Discount amount that was applied
            $table->decimal('discount_amount', 10, 2)
                ->comment('The actual discount amount in currency');

            $table->timestamps();

            // Indexes
            $table->index(['price_rule_id', 'customer_id']);
            $table->index('order_id');
            $table->index(['price_rule_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_rule_usages');
        Schema::dropIfExists('price_rules');
    }
};
