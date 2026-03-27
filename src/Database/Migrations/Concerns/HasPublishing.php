<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasPublishing
{
    /**
     * Add publishing fields with optional scheduling and scope
     */
    public function addPublishingFields(
        Blueprint $table,
        bool $withScheduling = false,
        bool $withScope = false,
        bool $withUnpublish = false,
    ): void {
        $table->timestamp('published_at')->nullable();

        if ($withScheduling) {
            $table->timestamp('scheduled_at')->nullable();
            $table->index(['scheduled_at', 'status']);
        }

        if ($withScope) {
            $table->string('published_scope')->default('web')->comment('web, global');
            $table->index(['published_scope', 'status']);
        }

        if ($withUnpublish) {
            $table->timestamp('unpublished_at')->nullable();
        }

        // Standard index
        $table->index(['published_at', 'status']);
        $table->index(['status', 'published_at']);
    }

    /**
     * Add only published_at field
     */
    public function addPublishedAt(Blueprint $table): void
    {
        $table->timestamp('published_at')->nullable();
        $table->index('published_at');
    }
}
