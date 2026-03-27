<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Missing Indexes to B2B Tables
 *
 * Adds critical indexes for performance optimization on companies and order_approvals tables.
 * These indexes improve query performance for common filtering, sorting, and reporting queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Companies table indexes
        Schema::table('companies', function (Blueprint $table) {
            // Multi-column indexes for common query patterns
            if (! $this->hasIndex('companies', 'companies_status_type_index')) {
                $table->index(['status', 'type'], 'companies_status_type_index');
            }

            if (! $this->hasIndex('companies', 'companies_risk_level_status_index')) {
                $table->index(['risk_level', 'status'], 'companies_risk_level_status_index');
            }

            // Single column indexes for filtering
            if (! $this->hasIndex('companies', 'companies_requires_approval_index')) {
                $table->index('requires_approval', 'companies_requires_approval_index');
            }

            if (! $this->hasIndex('companies', 'companies_last_order_at_index')) {
                $table->index('last_order_at', 'companies_last_order_at_index');
            }

            // Composite index for credit monitoring queries
            if (! $this->hasIndex('companies', 'companies_credit_monitoring_index')) {
                $table->index(['status', 'credit_limit', 'outstanding_balance'], 'companies_credit_monitoring_index');
            }

            // Index for parent company hierarchy queries
            if (! $this->hasIndex('companies', 'companies_parent_status_index')) {
                $table->index(['parent_company_id', 'status'], 'companies_parent_status_index');
            }
        });

        // Order approvals table indexes
        Schema::table('order_approvals', function (Blueprint $table) {
            // Index for user-specific approval queries
            if (! $this->hasIndex('order_approvals', 'order_approvals_requested_by_id_index')) {
                $table->index('requested_by_id', 'order_approvals_requested_by_id_index');
            }

            // Multi-column index for dashboard pending approvals
            if (! $this->hasIndex('order_approvals', 'order_approvals_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'order_approvals_status_created_at_index');
            }

            // Composite index for expiration cleanup and monitoring
            if (! $this->hasIndex('order_approvals', 'order_approvals_pending_expiration_index')) {
                $table->index(['status', 'expires_at'], 'order_approvals_pending_expiration_index');
            }

            // Index for company-specific approval history
            if (! $this->hasIndex('order_approvals', 'order_approvals_company_status_created_index')) {
                $table->index(['company_id', 'status', 'created_at'], 'order_approvals_company_status_created_index');
            }

            // Index for approver performance metrics
            if (! $this->hasIndex('order_approvals', 'order_approvals_approver_status_index')) {
                $table->index(['approver_id', 'status'], 'order_approvals_approver_status_index');
            }

            // Index for threshold analysis
            if (! $this->hasIndex('order_approvals', 'order_approvals_threshold_exceeded_index')) {
                $table->index(['threshold_exceeded', 'status'], 'order_approvals_threshold_exceeded_index');
            }
        });

        // Company-User pivot table indexes
        if (Schema::hasTable('company_user')) {
            Schema::table('company_user', function (Blueprint $table) {
                // Index for role-based queries
                if (! $this->hasIndex('company_user', 'company_user_role_index')) {
                    $table->index('role', 'company_user_role_index');
                }

                // Composite index for permission checks
                if (! $this->hasIndex('company_user', 'company_user_role_permissions_index')) {
                    $table->index(['company_id', 'role', 'can_approve_orders'], 'company_user_role_permissions_index');
                }
            });
        }

        // Add index to orders table for company-related queries if not exists
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! $this->hasIndex('orders', 'orders_company_status_index')) {
                    $table->index(['company_id', 'approval_status'], 'orders_company_status_index');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex('companies_status_type_index');
            $table->dropIndex('companies_risk_level_status_index');
            $table->dropIndex('companies_requires_approval_index');
            $table->dropIndex('companies_last_order_at_index');
            $table->dropIndex('companies_credit_monitoring_index');
            $table->dropIndex('companies_parent_status_index');
        });

        Schema::table('order_approvals', function (Blueprint $table) {
            $table->dropIndex('order_approvals_requested_by_id_index');
            $table->dropIndex('order_approvals_status_created_at_index');
            $table->dropIndex('order_approvals_pending_expiration_index');
            $table->dropIndex('order_approvals_company_status_created_index');
            $table->dropIndex('order_approvals_approver_status_index');
            $table->dropIndex('order_approvals_threshold_exceeded_index');
        });

        if (Schema::hasTable('company_user')) {
            Schema::table('company_user', function (Blueprint $table) {
                $table->dropIndex('company_user_role_index');
                $table->dropIndex('company_user_role_permissions_index');
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_company_status_index');
            });
        }
    }

    /**
     * Check if an index exists on a table (Laravel 11+ compatible, no Doctrine).
     */
    protected function hasIndex(string $table, string $index): bool
    {
        try {
            $indexes = Schema::getIndexes($table);

            return collect($indexes)->contains(fn ($idx) => $idx['name'] === $index);
        } catch (Throwable) {
            return false;
        }
    }
};
