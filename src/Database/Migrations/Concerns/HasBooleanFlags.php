<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasBooleanFlags
{
    /**
     * Add is_active field
     */
    public function addIsActive(Blueprint $table, bool $default = true, bool $withIndex = true): void
    {
        $table->boolean('is_active')->default($default);

        if ($withIndex) {
            $table->index('is_active');
        }
    }

    /**
     * Add is_default field
     */
    public function addIsDefault(Blueprint $table, bool $withIndex = true): void
    {
        $table->boolean('is_default')->default(false);

        if ($withIndex) {
            $table->index('is_default');
        }
    }

    /**
     * Add is_published field with optional timestamp
     */
    public function addIsPublished(Blueprint $table, bool $withTimestamp = true): void
    {
        $table->boolean('is_published')->default(false);

        if ($withTimestamp) {
            $table->timestamp('published_at')->nullable();
            $table->index(['is_published', 'published_at']);
        } else {
            $table->index('is_published');
        }
    }

    /**
     * Add visibility flags (is_active, is_visible, is_featured)
     */
    public function addVisibilityFlags(Blueprint $table): void
    {
        $table->boolean('is_active')->default(true);
        $table->boolean('is_visible')->default(true);
        $table->boolean('is_featured')->default(false);

        $table->index(['is_active', 'is_visible']);
        $table->index(['is_featured', 'is_active']);
    }
}
