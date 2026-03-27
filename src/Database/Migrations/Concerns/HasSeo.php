<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasSeo
{
    /**
     * Add SEO fields (meta_title, meta_description, seo JSON)
     */
    public function addSeoFields(Blueprint $table): void
    {
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        $table->jsonb('seo')->nullable()->comment('Additional SEO metadata');
    }

    /**
     * Add only basic SEO fields
     */
    public function addBasicSeo(Blueprint $table): void
    {
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
    }
}
