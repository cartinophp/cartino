<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('handle')->unique(); // Shopify-style handle
            $table->text('description')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->string('support_url')->nullable();
            $table->string('documentation_url')->nullable();

            // App Store info
            $table->string('app_store_id')->nullable()->unique();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('pricing_model')->default('free'); // free, one_time, recurring
            $table->json('pricing_plans')->nullable(); // For subscription tiers

            // App metadata
            $table->json('metadata')->nullable(); // Configuration, settings, etc.
            $table->json('permissions')->nullable(); // Required permissions
            $table->json('webhooks')->nullable(); // Webhook endpoints
            $table->json('api_scopes')->nullable(); // API access scopes

            // Installation info
            $table->boolean('is_installed')->default(false);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_system')->default(false); // System apps cannot be uninstalled
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();

            // App files and assets
            $table->string('icon_url')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('banner_url')->nullable();
            $table->json('assets')->nullable(); // CSS, JS files

            // Categories and tags
            $table->json('categories')->nullable();
            $table->json('tags')->nullable();

            // Compatibility
            $table->string('min_version')->nullable();
            $table->string('max_version')->nullable();
            $table->json('dependencies')->nullable(); // Other required apps

            // Status and ratings
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'deprecated'])->default('draft');
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('review_count')->default(0);
            $table->integer('install_count')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['status', 'is_active']);
            // Cannot index JSON column directly in MySQL
            $table->index(['pricing_model', 'price']);
            $table->index(['rating', 'review_count']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('apps');
    }
};
