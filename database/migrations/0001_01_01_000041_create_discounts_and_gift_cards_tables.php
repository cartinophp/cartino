<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();

            $table->string('code', 100);
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->enum('type', ['percentage', 'fixed_amount', 'free_shipping', 'buy_x_get_y']);
            $table->decimal('value', 15, 2)->nullable();
            $table->decimal('maximum_discount_amount', 15, 2)->nullable();

            $table->decimal('minimum_amount', 15, 2)->nullable();
            $table->integer('minimum_quantity')->nullable();

            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_customer')->nullable();
            $table->integer('usage_count')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('once_per_customer')->default(false);

            $table->integer('prerequisite_quantity')->nullable();
            $table->integer('entitled_quantity')->nullable();
            $table->decimal('entitled_quantity_discount', 5, 2)->nullable();

            $table->enum('target_type', ['all', 'specific_products', 'specific_collections', 'categories'])->default('all');
            $table->jsonb('target_selection')->nullable();

            $table->enum('customer_eligibility', ['all', 'specific_groups', 'specific_customers'])->default('all');
            $table->jsonb('customer_selection')->nullable();

            $table->jsonb('shipping_countries')->nullable();
            $table->boolean('exclude_shipping_rates')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable();

            $table->unique(['code', 'site_id']);
            $table->index(['site_id', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
            $table->index(['expires_at', 'is_active']);
            $table->index(['usage_limit', 'usage_count']);
            $table->index(['target_type']);
            $table->index(['customer_eligibility']);
            $table->index(['created_at', 'is_active']);

            $table->index(['is_active', 'starts_at', 'expires_at']);
            $table->index(['site_id', 'type', 'is_active']);

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        Schema::create('discount_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('discount_amount', 15, 2);
            $table->decimal('cart_total', 15, 2);
            $table->string('customer_email')->nullable();

            $table->enum('status', ['active', 'used', 'expired', 'cancelled'])->default('active');

            $table->string('session_id')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['discount_id', 'status']);
            $table->index(['customer_id', 'discount_id']);
            $table->index(['order_id']);
            $table->index(['customer_email', 'discount_id']);
            $table->index(['session_id', 'status']);
            $table->index(['created_at', 'status']);
            $table->index(['expires_at', 'status']);

            $table->index(['discount_id', 'customer_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        if (Schema::hasTable('orders')) {
            Schema::table('discount_usages', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            });
        }

        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();

            $table->string('code', 100)->unique();
            $table->string('title')->nullable();

            $table->decimal('initial_value', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->decimal('used_amount', 15, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_name')->nullable();
            $table->text('message')->nullable();

            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreignId('purchased_by')->nullable()->constrained('customers')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable();

            $table->index(['site_id', 'is_active']);
            $table->index(['customer_id', 'is_active']);
            $table->index(['expires_at', 'is_active']);
            $table->index(['balance']);
            $table->index(['recipient_email']);
            $table->index(['order_id']);
            $table->index(['purchased_by']);
            $table->index(['last_used_at']);

            $table->index(['is_active', 'balance', 'expires_at']);
            $table->index(['site_id', 'customer_id', 'is_active']);

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        if (Schema::hasTable('orders')) {
            Schema::table('gift_cards', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            });
        }

        Schema::create('gift_card_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_card_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount_used', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);

            $table->enum('type', ['purchase', 'usage', 'refund', 'adjustment']);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['gift_card_id', 'type']);
            $table->index(['customer_id', 'gift_card_id']);
            $table->index(['order_id']);
            $table->index(['type', 'created_at']);

            $table->index(['gift_card_id', 'created_at']);
            $table->index(['type', 'amount_used', 'created_at']);
        });

        if (Schema::hasTable('orders')) {
            Schema::table('gift_card_usages', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_usages');
        Schema::dropIfExists('gift_cards');
        Schema::dropIfExists('discount_usages');
        Schema::dropIfExists('discounts');
    }
};
