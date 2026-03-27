<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->nullable()->constrained('markets')->nullOnDelete();
            $table->string('handle')->unique();
            $table->boolean('is_default')->default(true);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('domain')->nullable()->unique();
            $table->jsonb('domains')->nullable();
            $table->string('locale', 10)->index();
            $table->string('lang', 5);
            $table->jsonb('countries')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('default_currency', 3)->default('EUR')->index();
            $table->boolean('tax_included_in_prices')->default(false);
            $table->string('tax_region')->nullable()->index();
            $table->integer('priority')->default(0)->index();
            $table->string('status')->default('active');
            $table->integer('order')->default(0)->index();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('unpublished_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->jsonb('attributes')->nullable();

            // Indexes
            $table->index(['handle', 'status']);
            $table->index(['locale', 'status']);
            $table->index(['default_currency', 'status']);
            $table->index(['is_default', 'status', 'priority']);
            $table->index(['market_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
