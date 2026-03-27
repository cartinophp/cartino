<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add rating fields if they don't exist
            if (! Schema::hasColumn('products', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->nullable()->after('status');
            }

            if (! Schema::hasColumn('products', 'review_count')) {
                $table->integer('review_count')->default(0)->after('average_rating');
            }

            // Add indexes for better performance
            $table->index(['average_rating']);
            $table->index(['review_count']);
            $table->index(['average_rating', 'review_count']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['products_average_rating_index']);
            $table->dropIndex(['products_review_count_index']);
            $table->dropIndex(['products_average_rating_review_count_index']);
            $table->dropColumn(['average_rating', 'review_count']);
        });
    }
};
