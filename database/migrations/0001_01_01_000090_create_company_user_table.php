<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Company-User Pivot Table
 *
 * Links users to companies with role-based access.
 * Supports:
 * - Multiple users per company
 * - Role assignment (buyer, manager, admin, finance)
 * - Approval permissions and limits
 * - Primary contact designation
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Role & Permissions
            $table->string('role')->default('buyer'); // buyer, manager, admin, finance
            $table->boolean('can_approve_orders')->default(false);
            $table->decimal('approval_limit', 12, 2)->nullable(); // Max amount user can approve
            $table->boolean('is_primary')->default(false); // Primary contact for company
            $table->string('status')->default('active'); // active, inactive, suspended

            // Additional Info
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->jsonb('permissions')->nullable(); // Custom permissions
            $table->jsonb('settings')->nullable(); // User-specific settings for this company

            $table->timestamps();

            // Constraints & Indexes
            $table->unique(['company_id', 'user_id']);
            $table->index('role');
            $table->index('status');
            $table->index(['company_id', 'role']);
            $table->index(['company_id', 'can_approve_orders']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
