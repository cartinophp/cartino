<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Company ID to Orders Table
 *
 * Links orders to companies for B2B functionality.
 * Allows tracking which company placed the order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add company_id if it doesn't exist
            if (! Schema::hasColumn('orders', 'company_id')) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained()
                    ->nullOnDelete();

                $table->index('company_id');
            }

            // Add approval status fields
            if (! Schema::hasColumn('orders', 'requires_approval')) {
                $table->boolean('requires_approval')->default(false)->after('status');
                $table->string('approval_status')->nullable()->after('requires_approval');
                // approved, pending, rejected
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }

            if (Schema::hasColumn('orders', 'requires_approval')) {
                $table->dropColumn(['requires_approval', 'approval_status']);
            }
        });
    }
};
