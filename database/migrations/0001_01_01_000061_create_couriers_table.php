<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('logo')->nullable();
            $table->integer('delivery_time_min')->nullable()->comment('Tempo di consegna minimo in giorni');
            $table->integer('delivery_time_max')->nullable()->comment('Tempo di consegna massimo in giorni');
            $table->string('status')->default('active');
            $table->boolean('is_enabled')->default(true);
            $table->jsonb('seo')->nullable();
            $table->jsonb('meta')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['site_id', 'slug']);
            $table->index(['site_id', 'status']);
            $table->index(['status', 'name']);
            $table->index('created_at');
            $table->index('is_enabled');

            // Full text search (MySQL 5.6+)
            if (config('database.default') === 'mysql') {
                $table->fullText(['name', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
