<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasReference
{
    /**
     * Add handle field (unique identifier)
     */
    public function addHandle(Blueprint $table, bool $unique = true, ?string $scopeColumn = null): void
    {
        $table->string('handle');

        if ($unique && ! $scopeColumn) {
            $table->unique('handle');
        }

        $table->index('handle');
    }

    /**
     * Add code field
     */
    public function addCode(
        Blueprint $table,
        int $length = 50,
        bool $unique = true,
        ?string $scopeColumn = null,
    ): void {
        $column = $table->string('code', $length);

        if ($unique && ! $scopeColumn) {
            $column->unique();
        }

        $table->index('code');
    }

    /**
     * Add reference field (for invoices, orders, etc.)
     */
    public function addReference(
        Blueprint $table,
        string $columnName = 'reference',
        int $length = 100,
        ?string $scopeColumn = null,
        string $comment = '',
    ): void {
        $column = $table->string($columnName, $length);

        if ($comment) {
            $column->comment($comment);
        }

        $table->index($columnName);
    }

    /**
     * Add order_number field with site scope
     */
    public function addOrderNumber(Blueprint $table, ?string $scopeColumn = 'site_id'): void
    {
        $table->string('order_number');
        $table->index('order_number');

        // Unique constraint should be added separately:
        // $table->unique(['order_number', 'site_id']);
    }
}
