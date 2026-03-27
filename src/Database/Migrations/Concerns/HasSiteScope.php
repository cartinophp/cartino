<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasSiteScope
{
    /**
     * Add multi-tenancy site_id with standardized definition
     * Used by 37% of tables for multi-site support
     */
    public function addSiteScope(Blueprint $table, bool $nullable = true, bool $withIndex = true): void
    {
        $column = $table->foreignId('site_id');

        if ($nullable) {
            $column->nullable();
        }

        $column->constrained('sites')->cascadeOnDelete();

        // Standard composite index for tenant queries
        if ($withIndex) {
            $table->index(['site_id', 'status']);
        }
    }

    /**
     * Add site scope with custom status column name
     */
    public function addSiteScopeWithStatus(
        Blueprint $table,
        string $statusColumn = 'is_active',
        bool $nullable = true,
    ): void {
        $column = $table->foreignId('site_id');

        if ($nullable) {
            $column->nullable();
        }

        $column->constrained('sites')->cascadeOnDelete();

        $table->index(['site_id', $statusColumn]);
    }
}
