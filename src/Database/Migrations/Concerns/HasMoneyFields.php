<?php

namespace Cartino\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasMoneyFields
{
    /**
     * Add standard pricing fields (price, compare_at_price, cost)
     * Shopify-compatible pattern with high precision
     */
    public function addPricingFields(Blueprint $table, int $precision = 15, int $scale = 4): void // Alta precisione per conversioni currency
    {
        $table->decimal('price', $precision, $scale);
        $table->decimal('compare_at_price', $precision, $scale)->nullable();
        $table->decimal('cost', $precision, $scale)->nullable();

        $table->index(['price', 'compare_at_price']);
    }

    /**
     * Add single price field
     */
    public function addPrice(
        Blueprint $table,
        string $columnName = 'price',
        int $precision = 15,
        int $scale = 4,
        bool $nullable = false,
        $default = null,
    ): void {
        $column = $table->decimal($columnName, $precision, $scale);

        if ($nullable) {
            $column->nullable();
        }

        if ($default !== null) {
            $column->default($default);
        }

        $table->index($columnName);
    }

    /**
     * Add order totals (subtotal, tax, shipping, discount, total)
     */
    public function addOrderTotals(Blueprint $table): void
    {
        $table->decimal('subtotal', 15, 2);
        $table->decimal('tax_total', 15, 2)->default(0);
        $table->decimal('shipping_total', 15, 2)->default(0);
        $table->decimal('discount_total', 15, 2)->default(0);
        $table->decimal('total', 15, 2);

        $table->index('total');
    }

    /**
     * Add currency code (ISO 4217)
     */
    public function addCurrency(Blueprint $table, string $default = 'EUR'): void
    {
        $table->char('currency', 3)->default($default);
        $table->index(['currency', 'price']);
    }
}
