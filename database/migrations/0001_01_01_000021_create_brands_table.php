<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('status')->default('active');
            $table->jsonb('seo')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'status']);
            $table->index(['status', 'name']);
            $table->index('created_at');

            // Full text search (MySQL 5.6+)
            if (config('database.default') === 'mysql') {
                $table->fullText(['name', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
