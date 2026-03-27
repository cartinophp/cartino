<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('app_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained()->onDelete('cascade');

            // Webhook details
            $table->string('event'); // e.g., 'order.created', 'product.updated'
            $table->string('endpoint_url');
            $table->string('secret')->nullable(); // For webhook verification
            $table->enum('method', ['GET', 'POST', 'PUT', 'PATCH'])->default('POST');
            $table->json('headers')->nullable(); // Custom headers

            // Status and tracking
            $table->boolean('is_active')->default(true);
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->text('last_error')->nullable();

            // Rate limiting
            $table->integer('max_attempts')->default(3);
            $table->integer('timeout_seconds')->default(30);

            $table->timestamps();

            // Indexes
            $table->index(['app_id', 'event']);
            $table->index(['is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_webhooks');
    }
};
