<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasAuditFields
{
    /**
     * Add audit fields (created_by, updated_by, optionally deleted_by)
     */
    public function addAuditFields(
        Blueprint $table,
        bool $includeCreatedBy = true,
        bool $includeUpdatedBy = true,
        bool $includeDeletedBy = false,
        string $onDelete = 'nullOnDelete',
    ): void {
        if ($includeCreatedBy) {
            $column = $table->foreignId('created_by')->nullable()->constrained('users');
            if ($onDelete === 'nullOnDelete') {
                $column->nullOnDelete();
            } else {
                $column->onDelete($onDelete);
            }
        }

        if ($includeUpdatedBy) {
            $column = $table->foreignId('updated_by')->nullable()->constrained('users');
            if ($onDelete === 'nullOnDelete') {
                $column->nullOnDelete();
            } else {
                $column->onDelete($onDelete);
            }
        }

        if ($includeDeletedBy) {
            $column = $table->foreignId('deleted_by')->nullable()->constrained('users');
            if ($onDelete === 'nullOnDelete') {
                $column->nullOnDelete();
            } else {
                $column->onDelete($onDelete);
            }
        }

        // Index on created_by for common queries
        if ($includeCreatedBy) {
            $table->index('created_by');
        }
    }

    /**
     * Add author fields (for content management)
     */
    public function addAuthorFields(Blueprint $table): void
    {
        $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

        $table->index(['author_id', 'status']);
    }
}
