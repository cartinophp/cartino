<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('app_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained()->onDelete('cascade');

            // Token details
            $table->string('name'); // Token name/description
            $table->string('token', 80)->unique();
            $table->json('scopes')->nullable(); // API scopes/permissions

            // Usage tracking
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->json('usage_limits')->nullable(); // Rate limits per endpoint

            // Security
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('allowed_ips')->nullable(); // IP whitelist

            $table->timestamps();

            // Indexes
            $table->index(['app_id', 'is_active']);
            $table->index(['token']);
            $table->index(['expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_api_tokens');
    }
};
