<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasStatus
{
    /**
     * Add status field with optional enum values
     */
    public function addStatus(
        Blueprint $table,
        array $values = [],
        string $default = 'active',
        bool $withIndex = true,
    ): void {
        if (! empty($values)) {
            $table->enum('status', $values)->default($default);
        } else {
            $table->string('status')->default($default);
        }

        if ($withIndex) {
            $table->index('status');
        }
    }

    /**
     * Add is_active boolean instead of status
     */
    public function addIsActive(Blueprint $table, bool $default = true, bool $withIndex = true): void
    {
        $table->boolean('is_active')->default($default);

        if ($withIndex) {
            $table->index('is_active');
        }
    }
}
