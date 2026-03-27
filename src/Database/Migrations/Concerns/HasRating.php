<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasRating
{
    /**
     * Add rating field with optional review count
     */
    public function addRating(
        Blueprint $table,
        string $columnName = 'rating',
        bool $withCount = true,
        bool $nullable = true,
    ): void {
        $column = $table->decimal($columnName, 3, 2);

        if ($nullable) {
            $column->nullable();
        } else {
            $column->default(0.00);
        }

        if ($withCount) {
            $table->integer('review_count')->default(0);
            $table->index([$columnName, 'review_count']);
        } else {
            $table->index($columnName);
        }
    }

    /**
     * Add average_rating field
     */
    public function addAverageRating(Blueprint $table, bool $withCount = true): void
    {
        $this->addRating($table, 'average_rating', $withCount, true);
    }

    /**
     * Add helpful votes (for reviews)
     */
    public function addHelpfulVotes(Blueprint $table): void
    {
        $table->integer('helpful_count')->default(0);
        $table->integer('not_helpful_count')->default(0);
    }

    /**
     * Add multiple rating types (quality, delivery, etc.)
     */
    public function addMultipleRatings(Blueprint $table, array $ratingTypes): void
    {
        foreach ($ratingTypes as $type) {
            $table->decimal("{$type}_rating", 3, 2)->nullable();
        }
    }
}
