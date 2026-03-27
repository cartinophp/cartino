<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Order Approvals Table
 *
 * Manages B2B order approval workflow.
 * When an order exceeds company threshold, it requires manager approval.
 *
 * Workflow:
 * 1. Buyer creates order above threshold
 * 2. System creates approval request
 * 3. Manager approves/rejects
 * 4. Order status updated accordingly
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_approvals', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();

            // Approval Status
            $table->string('status')->default('pending'); // pending, approved, rejected, expired

            // Order Details (snapshot)
            $table->decimal('order_total', 12, 2);
            $table->decimal('approval_threshold', 12, 2)->nullable();
            $table->boolean('threshold_exceeded')->default(false);

            // Approval Decision
            $table->text('approval_reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable(); // Internal notes

            // Timestamps
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Auto-expiration (e.g., 7 days)

            // Additional Data
            $table->jsonb('data')->nullable(); // Approval metadata

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('order_id');
            $table->index('company_id');
            $table->index('approver_id');
            $table->index(['company_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_approvals');
    }
};
