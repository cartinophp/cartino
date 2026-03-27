<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasScheduling
{
    /**
     * Add scheduling fields (starts_at, ends_at)
     */
    public function addScheduling(
        Blueprint $table,
        string $startColumn = 'starts_at',
        string $endColumn = 'ends_at',
    ): void {
        $table->timestamp($startColumn)->nullable();
        $table->timestamp($endColumn)->nullable();

        // Index for "active now" queries
        $table->index([$startColumn, $endColumn]);
    }

    /**
     * Add validity period (for pricing rules, tax rates)
     */
    public function addValidityPeriod(Blueprint $table): void
    {
        $this->addScheduling($table, 'valid_from', 'valid_until');

        // Composite index with status for performance
        $table->index(['valid_from', 'valid_until', 'is_active']);
    }

    /**
     * Add expiration field
     */
    public function addExpiration(Blueprint $table, string $columnName = 'expires_at'): void
    {
        $table->timestamp($columnName)->nullable();
        $table->index($columnName);
    }

    /**
     * Add workflow timestamps (for multi-stage processes)
     */
    public function addWorkflowTimestamps(Blueprint $table, array $stages): void
    {
        foreach ($stages as $stage) {
            $table->timestamp("{$stage}_at")->nullable();
        }

        // Index first and last stage
        if (count($stages) > 0) {
            $firstStage = reset($stages);
            $lastStage = end($stages);
            $table->index(["{$firstStage}_at", 'status']);
            $table->index("{$lastStage}_at");
        }
    }

    /**
     * Add fulfillment timestamps (shipped, delivered)
     */
    public function addFulfillmentTimestamps(Blueprint $table): void
    {
        $table->timestamp('shipped_at')->nullable();
        $table->timestamp('estimated_delivery')->nullable();
        $table->timestamp('delivered_at')->nullable();

        $table->index(['shipped_at', 'status']);
        $table->index(['delivered_at', 'status']);
    }
}
