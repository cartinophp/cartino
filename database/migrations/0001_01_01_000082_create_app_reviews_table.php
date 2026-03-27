<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('app_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Review content
            $table->integer('rating'); // 1-5 stars
            $table->string('title')->nullable();
            $table->text('review')->nullable();

            // Review metadata
            $table->string('version_reviewed'); // App version when reviewed
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_featured')->default(false);

            // Moderation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // Helpful votes
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['app_id', 'status', 'rating']);
            $table->index(['user_id']);
            $table->unique(['app_id', 'user_id']); // One review per user per app
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_reviews');
    }
};
