<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasOrdering
{
    /**
     * Add sort_order field
     */
    public function addSortOrder(Blueprint $table, string $columnName = 'sort_order', int $default = 0): void
    {
        $table->integer($columnName)->default($default);
        $table->index($columnName);
    }

    /**
     * Add position field
     */
    public function addPosition(Blueprint $table, int $default = 1): void
    {
        $table->integer('position')->default($default);
        $table->index('position');
    }

    /**
     * Add priority field
     */
    public function addPriority(Blueprint $table, int $default = 0): void
    {
        $table->integer('priority')->default($default);
        $table->index('priority');
    }

    /**
     * Add nested set fields (for tree structures)
     */
    public function addNestedSet(Blueprint $table, bool $withPath = true): void
    {
        $table->integer('level')->default(0);
        $table->integer('left');
        $table->integer('right');

        if ($withPath) {
            $table->string('path', 500)->nullable();
        }

        $table->index(['left', 'right']);
        $table->index('level');
    }

    /**
     * Add hierarchy fields (parent_id, depth, order)
     */
    public function addHierarchy(Blueprint $table, ?string $parentTable = null): void
    {
        $parentTable = $parentTable ?? $table->getTable();

        $table->foreignId('parent_id')->nullable()->constrained($parentTable)->nullOnDelete();
        $table->integer('depth')->default(0);
        $table->integer('order')->default(0);

        $table->index(['parent_id', 'order']);
        $table->index(['depth', 'order']);
    }
}
