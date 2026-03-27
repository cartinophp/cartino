<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->string('password');
            $table->string('status')->default('active');
            $table->boolean('is_super_admin')->default(false);
            $table->string('locale', 10)->default('en');
            $table->string('timezone')->default('UTC');
            $table->jsonb('preferences')->nullable()->comment('UI and notification preferences');
            $table->unsignedBigInteger('default_site_id')->nullable();
            $table->jsonb('data')->nullable();
            $table->string('api_key')->nullable()->unique();
            $table->jsonb('oauth_providers')->nullable()->comment('Connected OAuth providers');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'is_super_admin']);
            $table->index(['locale', 'status']);
            $table->index(['first_name', 'last_name']);

            // Full text search
            if (config('database.default') === 'mysql') {
                $table->fullText(['first_name', 'last_name', 'email']);
            }
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
