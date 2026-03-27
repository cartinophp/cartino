<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();

            // Page Identity
            $table->string('title');
            $table->string('slug');
            $table->string('handle');
            $table->string('template')->nullable(); // blade template to use
            $table->string('layout')->nullable(); // layout wrapper

            // Content
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->jsonb('blocks_data')->nullable()->comment('Page builder blocks (Gutenberg-style)');

            // Display
            $table->boolean('show_title')->default(true);
            $table->string('hero_image')->nullable();

            // SEO
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->jsonb('seo')->nullable()->comment('Additional SEO metadata');

            // Publishing
            $table->enum('status', ['published', 'draft', 'private', 'scheduled'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            // Author & Editor
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Custom fields (Statamic-style)
            $table->jsonb('data')->nullable();

            // Hierarchy
            $table->integer('order')->default(0);
            $table->integer('depth')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['site_id', 'handle']);
            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index(['parent_id', 'order']);
            $table->index(['author_id', 'status']);
            $table->index(['site_id', 'parent_id', 'order']);
            $table->index(['scheduled_at', 'status']);

            // Full text search
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'content', 'excerpt']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
