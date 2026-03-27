<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable();

            $table->unique(['email', 'site_id']);
            $table->index(['site_id', 'status']);
            $table->index(['last_name', 'first_name']);
            $table->index(['phone', 'site_id']);
            $table->index('created_at');

            // Composite indexes for common filter combinations
            $table->index(['status', 'created_at']);
            $table->index(['gender', 'status']);
            $table->index(['last_login_at', 'status']);

            // Full text search for names (MySQL 5.6+)
            if (config('database.default') === 'mysql') {
                $table->fullText(['first_name', 'last_name']);
            }

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
