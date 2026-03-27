<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Company-Customer Pivot Table
 *
 * Links customers to companies for B2B relationships.
 * Allows customers to be associated with one or more companies.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // Role & Status
            $table->string('role')->default('buyer'); // buyer, contact, decision_maker
            $table->boolean('is_primary')->default(false); // Primary contact for company
            $table->string('status')->default('active'); // active, inactive

            // Additional Info
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->jsonb('settings')->nullable();

            $table->timestamps();

            // Constraints & Indexes
            $table->unique(['company_id', 'customer_id']);
            $table->index('role');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_customer');
    }
};
