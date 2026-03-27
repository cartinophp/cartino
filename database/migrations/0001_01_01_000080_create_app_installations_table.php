<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('app_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who installed

            // Installation details
            $table->string('version_installed');
            $table->json('configuration')->nullable(); // App-specific config
            $table->json('settings')->nullable(); // User settings for the app
            $table->json('permissions_granted')->nullable(); // Actual permissions granted

            // Subscription info (for paid apps)
            $table->string('subscription_id')->nullable();
            $table->string('plan_name')->nullable();
            $table->decimal('monthly_cost', 10, 2)->default(0.00);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->boolean('auto_renew')->default(true);

            // Status tracking
            $table->enum('status', ['active', 'inactive', 'suspended', 'cancelled'])->default('active');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            // Usage statistics
            $table->integer('usage_count')->default(0);
            $table->json('usage_metrics')->nullable();

            // Error tracking
            $table->integer('error_count')->default(0);
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['app_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['subscription_ends_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_installations');
    }
};
