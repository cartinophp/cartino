<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('product_type'); // entry, variant
            $table->unsignedBigInteger('product_id');
            $table->string('product_handle')->nullable();
            $table->json('variant_data')->nullable(); // if product_type is variant
            $table->integer('requested_quantity')->default(1);
            $table->boolean('is_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('notification_token')->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['product_type', 'product_id', 'is_active']);
            $table->index(['email', 'is_active']);
            $table->index(['customer_id', 'is_active']);
            $table->index('notification_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_notifications');
    }
};
