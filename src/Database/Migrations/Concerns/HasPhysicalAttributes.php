<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasPhysicalAttributes
{
    /**
     * Add weight field with unit
     */
    public function addWeight(
        Blueprint $table,
        string $columnName = 'weight',
        string $defaultUnit = 'kg',
        bool $nullable = true,
    ): void {
        $column = $table->decimal($columnName, 8, 2);

        if ($nullable) {
            $column->nullable();
        }

        $table->string('weight_unit', 10)->default($defaultUnit);

        $table->index([$columnName, 'weight_unit']);
    }

    /**
     * Add weight range (for shipping rules)
     */
    public function addWeightRange(Blueprint $table, string $defaultUnit = 'kg'): void
    {
        $table->decimal('min_weight', 10, 2)->nullable();
        $table->decimal('max_weight', 10, 2)->nullable();
        $table->string('weight_unit', 10)->default($defaultUnit);

        $table->index(['min_weight', 'max_weight']);
    }

    /**
     * Add dimensions (length, width, height)
     */
    public function addDimensions(Blueprint $table, string $format = 'json'): void
    {
        if ($format === 'json') {
            $table->jsonb('dimensions')->nullable()->comment('length, width, height');
        } else {
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('dimension_unit', 10)->default('cm');
        }
    }

    /**
     * Add complete product physical attributes
     */
    public function addProductPhysicalAttributes(Blueprint $table): void
    {
        // Weight
        $table->decimal('weight', 8, 2)->nullable();
        $table->string('weight_unit', 10)->default('kg');

        // Dimensions
        $table->jsonb('dimensions')->nullable()->comment('length, width, height, unit');

        // Additional
        $table->string('hs_code', 20)->nullable()->comment('Harmonized System code for customs');

        $table->index(['weight', 'weight_unit']);
    }
}
