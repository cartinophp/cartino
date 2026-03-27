<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasJsonFields
{
    /**
     * Add standard 'data' field (EAV pattern like Statamic)
     * Used by 84% of tables for custom fields
     */
    public function addDataField(Blueprint $table, string $comment = 'Custom fields data'): void
    {
        $column = $table->jsonb('data')->nullable();

        if ($comment) {
            $column->comment($comment);
        }
    }

    /**
     * Add settings field for configuration
     */
    public function addSettingsField(Blueprint $table): void
    {
        $table->jsonb('settings')->nullable()->comment('Configuration settings');
    }

    /**
     * Add SEO-specific JSON field
     */
    public function addSeoField(Blueprint $table): void
    {
        $table->jsonb('seo')->nullable()->comment('SEO metadata (og:image, canonical, etc.)');
    }

    /**
     * Add metadata field
     */
    public function addMetadataField(Blueprint $table): void
    {
        $table->jsonb('metadata')->nullable()->comment('Additional metadata');
    }
}
