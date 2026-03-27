<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasActivityTracking
{
    /**
     * Add last_used_at with optional usage count
     */
    public function addLastUsedTracking(Blueprint $table, bool $withCount = true): void
    {
        $table->timestamp('last_used_at')->nullable();

        if ($withCount) {
            $table->integer('usage_count')->default(0);
        }

        $table->index('last_used_at');
    }

    /**
     * Add last_activity_at field
     */
    public function addLastActivity(Blueprint $table): void
    {
        $table->timestamp('last_activity_at')->nullable();
        $table->index('last_activity_at');
    }

    /**
     * Add login tracking fields
     */
    public function addLoginTracking(Blueprint $table): void
    {
        $table->timestamp('last_login_at')->nullable();
        $table->string('last_login_ip', 45)->nullable();

        $table->index('last_login_at');
    }

    /**
     * Add success/failure tracking (for webhooks, APIs)
     */
    public function addSuccessFailureTracking(Blueprint $table, bool $withErrorMessage = true): void
    {
        $table->integer('success_count')->default(0);
        $table->integer('failure_count')->default(0);
        $table->timestamp('last_success_at')->nullable();
        $table->timestamp('last_failure_at')->nullable();

        if ($withErrorMessage) {
            $table->text('last_error')->nullable();
        }

        $table->index('last_success_at');
        $table->index('last_failure_at');
    }

    /**
     * Add error tracking
     */
    public function addErrorTracking(Blueprint $table): void
    {
        $table->integer('error_count')->default(0);
        $table->timestamp('last_error_at')->nullable();
        $table->text('last_error_message')->nullable();
    }
}
