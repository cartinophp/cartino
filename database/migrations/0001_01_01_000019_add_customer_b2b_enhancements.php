<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer B2B Enhancements
 *
 * Adds enterprise B2B features to customers inspired by Sylius and Shopware.
 *
 * Features:
 * - Customer numbering system
 * - Company/VAT information
 * - Tax exemptions
 * - Credit limits & outstanding balance
 * - Risk assessment
 * - Multiple customer groups per customer
 * - Customer state management (active, disabled, invited)
 * - Marketing consent tracking (GDPR compliant)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Unique identifiers
            $table->string('customer_number', 50)
                ->unique()
                ->nullable()
                ->after('id')
                ->comment('Auto-generated customer number (e.g., CUST-000001)');

            $table->string('handle')
                ->unique()
                ->nullable()
                ->after('customer_number')
                ->comment('URL-friendly identifier');

            // State management (beyond simple active/inactive)
            $table->enum('state', ['active', 'disabled', 'invited', 'declined'])
                ->default('active')
                ->after('email')
                ->comment('Customer account state');

            $table->timestamp('email_verified_at')
                ->nullable()
                ->after('state');

            $table->timestamp('invited_at')
                ->nullable()
                ->after('email_verified_at');

            // B2B fields
            $table->string('company_name')
                ->nullable()
                ->after('last_name');

            $table->string('vat_number', 30)
                ->nullable()
                ->after('company_name')
                ->comment('VAT/Tax ID for businesses');

            $table->string('tax_id', 30)
                ->nullable()
                ->after('vat_number');

            $table->boolean('tax_exempt')
                ->default(false)
                ->after('tax_id');

            $table->json('tax_exemptions')
                ->nullable()
                ->after('tax_exempt')
                ->comment('Specific tax exemptions (e.g., ["IT_VAT", "US_SALES_TAX"])');

            // Credit management
            $table->decimal('credit_limit', 10, 2)
                ->default(0)
                ->after('tax_exemptions')
                ->comment('Maximum allowed credit');

            $table->decimal('outstanding_balance', 10, 2)
                ->default(0)
                ->after('credit_limit')
                ->comment('Current unpaid balance');

            // Risk & fraud detection
            $table->enum('risk_level', ['low', 'medium', 'high'])
                ->default('low')
                ->after('outstanding_balance');

            // Cached aggregates for performance (updated via observers/events)
            $table->timestamp('last_order_at')
                ->nullable()
                ->after('risk_level')
                ->comment('Most recent order date');

            $table->decimal('lifetime_value', 10, 2)
                ->default(0)
                ->after('last_order_at')
                ->comment('Total spent all-time');

            $table->integer('order_count')
                ->default(0)
                ->after('lifetime_value')
                ->comment('Total number of orders');

            // Ensure marketing opt-in flag exists before timestamps
            $table->boolean('accepts_marketing')
                ->default(false)
                ->after('status');

            // Marketing consent (GDPR)
            $table->timestamp('marketing_consent_at')
                ->nullable()
                ->after('accepts_marketing')
                ->comment('When customer consented to email marketing');

            $table->boolean('sms_marketing_consent')
                ->default(false)
                ->after('marketing_consent_at');

            $table->timestamp('sms_marketing_consent_at')
                ->nullable()
                ->after('sms_marketing_consent');

            $table->enum('marketing_opt_in_level', ['single', 'confirmed', 'unknown'])
                ->default('unknown')
                ->after('sms_marketing_consent_at')
                ->comment('Email opt-in verification level');

            // Internal notes
            $table->text('merchant_notes')
                ->nullable()
                ->after('marketing_opt_in_level')
                ->comment('Private notes for merchants');

            // Indexes
            $table->index('customer_number');
            $table->index('state');
            $table->index('tax_exempt');
            $table->index('risk_level');
            $table->index(['state', 'email_verified_at']);
            $table->index(['lifetime_value', 'order_count']);
        });

        // Customer tags (flexible categorization)
        Schema::create('customer_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('customer_count')->default(0);
            $table->timestamps();

            $table->index('slug');
        });

        Schema::create('customer_tag', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('customer_tag_id')
                ->constrained('customer_tags')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['customer_id', 'customer_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tag');
        Schema::dropIfExists('customer_tags');
        Schema::dropIfExists('customer_customer_group');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['lifetime_value', 'order_count']);
            $table->dropIndex(['state', 'email_verified_at']);
            $table->dropIndex(['risk_level']);
            $table->dropIndex(['tax_exempt']);
            $table->dropIndex(['state']);
            $table->dropIndex(['customer_number']);

            $table->dropColumn([
                'customer_number',
                'handle',
                'state',
                'email_verified_at',
                'invited_at',
                'company_name',
                'vat_number',
                'tax_id',
                'tax_exempt',
                'tax_exemptions',
                'credit_limit',
                'outstanding_balance',
                'risk_level',
                'last_order_at',
                'lifetime_value',
                'order_count',
                'marketing_consent_at',
                'sms_marketing_consent',
                'sms_marketing_consent_at',
                'marketing_opt_in_level',
                'merchant_notes',
            ]);
        });
    }
};
