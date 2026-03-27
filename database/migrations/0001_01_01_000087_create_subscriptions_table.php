<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();

            // Subscription details
            $table->string('subscription_number')->unique();
            $table->string('status')->default('active'); // active, paused, cancelled, expired, past_due

            // Billing configuration
            $table->string('billing_interval'); // day, week, month, year
            $table->integer('billing_interval_count')->default(1); // every X intervals
            $table->decimal('price', 15, 2); // subscription price per billing cycle
            $table->foreignId('currency_id')->constrained();

            // Dates
            $table->timestamp('started_at')->nullable();
            $table->timestamp('trial_end_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            // Payment & billing
            $table->string('payment_method')->nullable();
            $table->jsonb('payment_details')->nullable();
            $table->integer('billing_cycle_count')->default(0); // how many times billed
            $table->decimal('total_billed', 15, 2)->default(0);

            // Cancellation
            $table->string('cancel_reason')->nullable();
            $table->text('cancel_comment')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);

            // Pause
            $table->string('pause_reason')->nullable();
            $table->timestamp('pause_resumes_at')->nullable();

            // Metadata
            $table->jsonb('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->jsonb('data')->nullable();

            // Indexes
            $table->index(['site_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index(['status', 'next_billing_date']);
            $table->index(['billing_interval', 'billing_interval_count']);
            $table->index('started_at');
            $table->index('trial_end_at');
            $table->index('current_period_end');
            $table->index('cancelled_at');
            $table->index('paused_at');

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Add subscription_id to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->index(['subscription_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn('subscription_id');
        });

        Schema::dropIfExists('subscriptions');
    }
};
