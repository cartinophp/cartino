<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // price_tiers rimossa: era un duplicato esatto di variant_prices.
        // Usare variant_prices (con min_quantity, max_quantity, customer_group_id,
        // currency, starts_at, ends_at, priority) come unica fonte per il tiered pricing.

        // Dynamic pricing rules engine
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed', 'bulk', 'tiered', 'bogo', 'conditional']);
            $table->jsonb('conditions'); // Complex rule conditions
            $table->jsonb('actions'); // Rule actions to apply
            $table->integer('priority')->default(0); // Higher number = higher priority
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('applies_to_all_products')->default(false);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'is_active', 'priority']);
            $table->index(['type', 'is_active']);
            $table->index(['valid_from', 'valid_until', 'is_active']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Products affected by pricing rules
        Schema::create('pricing_rule_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pricing_rule_id', 'product_id']);
            $table->index(['product_id']);
        });

        // Customer groups affected by pricing rules
        Schema::create('pricing_rule_customer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pricing_rule_id', 'customer_group_id'], 'pricing_rule_groups_unique');
            $table->index(['customer_group_id']);
        });

        // Track pricing rule applications for analytics
        Schema::create('pricing_rule_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('original_amount', 15, 2);
            $table->decimal('discounted_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->jsonb('applied_products'); // Products affected
            $table->timestamp('applied_at');

            $table->index(['pricing_rule_id', 'applied_at']);
            $table->index(['customer_id', 'applied_at']);
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rule_applications');
        Schema::dropIfExists('pricing_rule_customer_groups');
        Schema::dropIfExists('pricing_rule_products');
        Schema::dropIfExists('pricing_rules');
    }
};
