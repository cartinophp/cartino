<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Companies Table for B2B
 *
 * Stores company/organization information for B2B customers.
 * Supports:
 * - Company hierarchy (parent/subsidiary)
 * - Credit limits and payment terms
 * - Order approval workflows
 * - Tax exemptions
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('company_number')->unique(); // Auto-generated COMP-000001
            $table->string('name');
            $table->string('handle')->unique(); // URL-friendly identifier
            $table->string('legal_name')->nullable(); // Legal business name
            $table->string('vat_number')->nullable()->index();
            $table->string('tax_id')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Company Hierarchy
            $table->foreignId('parent_company_id')->nullable()->constrained('companies')->nullOnDelete();

            // Business Type & Status
            $table->string('type')->default('standard'); // standard, enterprise, wholesale, reseller
            $table->string('status')->default('active'); // active, suspended, closed

            // Credit & Payment
            $table->decimal('credit_limit', 12, 2)->nullable(); // Maximum credit allowed
            $table->decimal('outstanding_balance', 12, 2)->default(0); // Current debt
            $table->integer('payment_terms_days')->nullable(); // NET30, NET60, etc.
            $table->string('payment_method')->nullable(); // invoice, card, wire

            // Approval Workflow
            $table->decimal('approval_threshold', 12, 2)->nullable(); // Orders above this require approval
            $table->boolean('requires_approval')->default(false);

            // Risk & Analytics
            $table->string('risk_level')->default('low'); // low, medium, high
            $table->decimal('lifetime_value', 12, 2)->nullable();
            $table->integer('order_count')->default(0);
            $table->timestamp('last_order_at')->nullable();

            // Tax Information
            $table->boolean('tax_exempt')->default(false);
            $table->jsonb('tax_exemptions')->nullable(); // Tax exemption details

            // Addresses
            $table->jsonb('billing_address')->nullable();
            $table->jsonb('shipping_address')->nullable();

            // Additional Data
            $table->text('notes')->nullable(); // Internal notes
            $table->jsonb('settings')->nullable(); // Company-specific settings
            $table->jsonb('data')->nullable(); // Custom fields

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('type');
            $table->index('parent_company_id');
            $table->index('risk_level');
            $table->index(['status', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
