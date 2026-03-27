<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->boolean('is_enabled')->default(true);

            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('level')->default(0);
            $table->string('path', 500)->nullable();
            $table->integer('left')->nullable();
            $table->integer('right')->nullable();

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();

            $table->integer('sort_order')->default(0);
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_featured')->default(false);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->jsonb('seo')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->timestamp('published_at')->nullable();

            $table->boolean('include_in_menu')->default(true);
            $table->boolean('include_in_search')->default(true);
            // products_count rimossa — era un counter cache non sicuro (out-of-sync).
            // Usare withCount('products') via Eloquent o una query aggregata.

            $table->string('template')->nullable();
            $table->jsonb('layout_settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable();

            $table->unique(['slug', 'site_id']);
            $table->unique(['path', 'site_id']);
            $table->index(['parent_id', 'sort_order']);
            $table->index(['left', 'right']);
            $table->index(['is_active', 'is_visible']);
            $table->index(['is_featured', 'is_active']);

            // Composite indexes (cover multiple simpler indexes)
            $table->index(['site_id', 'parent_id', 'is_active']);
            $table->index(['level', 'is_active', 'sort_order']);
            $table->index(['is_active', 'include_in_menu', 'sort_order']);

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['category_id', 'product_id']);
            $table->index(['product_id', 'is_primary']);
            $table->index(['category_id', 'sort_order']);
            $table->index(['product_id', 'sort_order']);

            $table->index(['category_id', 'is_primary', 'sort_order']);
            $table->index(['product_id', 'category_id', 'is_primary']);
        });

        Schema::create('category_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('type');
            $table->string('field_name');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_collapsible')->default(true);
            $table->boolean('is_expanded_by_default')->default(true);

            $table->decimal('min_value', 15, 2)->nullable();
            $table->decimal('max_value', 15, 2)->nullable();
            $table->decimal('step', 15, 2)->nullable();

            $table->jsonb('options')->nullable();

            $table->timestamps();

            $table->index(['category_id', 'is_active', 'sort_order']);
            $table->index(['type', 'is_active']);

            $table->index(['category_id', 'type', 'is_active']);
        });

        Schema::create('category_breadcrumbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ancestor_id')->constrained('categories')->cascadeOnDelete();
            $table->integer('depth');
            $table->string('path_names');
            $table->string('path_slugs');

            $table->timestamps();

            $table->unique(['category_id', 'ancestor_id']);
            $table->index(['ancestor_id', 'depth']);
            $table->index(['category_id', 'depth']);

            $table->index(['ancestor_id', 'category_id', 'depth']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_breadcrumbs');
        Schema::dropIfExists('category_filters');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('categories');
    }
};
