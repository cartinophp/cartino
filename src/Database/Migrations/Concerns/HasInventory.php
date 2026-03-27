<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasInventory
{
    /**
     * Add simple inventory quantity
     */
    public function addSimpleInventory(Blueprint $table, string $columnName = 'inventory_quantity'): void
    {
        $table->integer($columnName)->default(0);
        $table->index($columnName);
    }

    /**
     * Add advanced inventory levels with computed available column
     * LunarPHP-inspired pattern
     */
    public function addInventoryLevels(Blueprint $table, bool $withComputed = true): void
    {
        $table->integer('quantity')->default(0);
        $table->integer('reserved')->default(0);

        if ($withComputed) {
            // PostgreSQL & MySQL 5.7+ generated column
            $table->integer('available')->storedAs('quantity - reserved');
        } else {
            $table->integer('available')->default(0);
        }

        $table->integer('incoming')->default(0);
        $table->integer('damaged')->default(0);

        // Index for low stock alerts
        $table->index(['available', 'reserved']);
    }

    /**
     * Add reorder points for automatic purchasing
     */
    public function addReorderPoints(Blueprint $table): void
    {
        $table->integer('reorder_point')->nullable();
        $table->integer('reorder_quantity')->nullable();
        $table->integer('safety_stock')->nullable();

        $table->index('reorder_point');
    }

    /**
     * Add inventory tracking timestamps
     */
    public function addInventoryTracking(Blueprint $table): void
    {
        $table->timestamp('last_counted_at')->nullable();
        $table->integer('last_counted_quantity')->nullable();
        $table->timestamp('last_received_at')->nullable();
        $table->timestamp('last_sold_at')->nullable();

        $table->index('last_counted_at');
    }

    /**
     * Add quantity tracking (before/after for auditing)
     */
    public function addQuantityTracking(Blueprint $table): void
    {
        $table->integer('quantity'); // Can be negative for movements
        $table->integer('quantity_before')->default(0);
        $table->integer('quantity_after')->default(0);
    }
}
