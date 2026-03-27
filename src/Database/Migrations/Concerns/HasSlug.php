<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasSlug
{
    /**
     * Add slug field with optional scope
     */
    public function addSlug(
        Blueprint $table,
        bool $nullable = false,
        ?string $scopeColumn = null,
        bool $withIndex = true,
    ): void {
        $column = $table->string('slug');

        if ($nullable) {
            $column->nullable();
        }

        if ($withIndex && ! $scopeColumn) {
            $table->index('slug');
        }
    }

    /**
     * Add slug with site scope (unique per site)
     */
    public function addSiteSlug(Blueprint $table): void
    {
        $table->string('slug');
        // Unique constraint should be added separately:
        // $table->unique(['site_id', 'slug']);
        $table->index(['site_id', 'slug']);
    }

    /**
     * Add slug with parent scope
     */
    public function addScopedSlug(Blueprint $table, string $scopeColumn): void
    {
        $table->string('slug');
        // Unique constraint should be added separately:
        // $table->unique([$scopeColumn, 'slug']);
        $table->index([$scopeColumn, 'slug']);
    }
}
