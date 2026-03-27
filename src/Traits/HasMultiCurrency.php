<?php

declare(strict_types=1);

namespace Cartino\Traits;

use Cartino\Models\Currency;

/**
 * Trait HasMultiCurrency
 *
 * Provides multi-currency functionality for models that handle monetary values.
 * Supports currency conversion, formatting, and price calculations.
 */
trait HasMultiCurrency
{
    /**
     * Get the default currency for this model
     */
    public function getDefaultCurrency(): string
    {
        return $this->currency ?? $this->default_currency ?? config('cartino.currency.default', 'USD');
    }

    /**
     * Get currency model
     */
    public function getCurrencyModel(): ?Currency
    {
        $currencyCode = $this->getDefaultCurrency();

        return Currency::where('code', $currencyCode)->first();
    }

    /**
     * Format price with currency
     */
    public function formatPrice(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? $this->getDefaultCurrency();
        $currencyModel = Currency::where('code', $currency)->first();

        if (! $currencyModel) {
            return number_format($amount, 2).' '.$currency;
        }

        $formatted = number_format(
            $amount,
            $currencyModel->precision ?? 2,
            $currencyModel->decimal_separator ?? '.',
            $currencyModel->thousands_separator ?? ','
        );

        // Symbol position: before or after
        $symbolPosition = $currencyModel->symbol_position ?? 'before';

        if ($symbolPosition === 'before') {
            return $currencyModel->symbol.$formatted;
        }

        return $formatted.' '.$currencyModel->symbol;
    }

    /**
     * Convert amount from one currency to another
     */
    public function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $fromRate = Currency::where('code', $fromCurrency)->value('rate') ?? 1.0;
        $toRate = Currency::where('code', $toCurrency)->value('rate') ?? 1.0;

        // Convert to base currency first, then to target currency
        $baseAmount = $amount / $fromRate;

        return $baseAmount * $toRate;
    }

    /**
     * Get price in specific currency
     */
    public function getPriceInCurrency(float $basePrice, string $currency): float
    {
        $defaultCurrency = $this->getDefaultCurrency();

        return $this->convertCurrency($basePrice, $defaultCurrency, $currency);
    }

    /**
     * Check if currency is supported
     */
    public function isCurrencySupported(string $currency): bool
    {
        return Currency::where('code', $currency)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Get all supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return Currency::where('is_enabled', true)
            ->pluck('code')
            ->toArray();
    }

    /**
     * Scope: Filter by currency
     */
    public function scopeForCurrency($query, string $currency)
    {
        if ($this->getTable() === 'catalogs') {
            return $query->where('currency', $currency);
        }

        if (method_exists($this, 'currency')) {
            return $query->whereHas('currency', function ($q) use ($currency) {
                $q->where('code', $currency);
            });
        }

        return $query->where('currency', $currency);
    }

    /**
     * Attribute: Get formatted price
     */
    public function getFormattedPriceAttribute(): ?string
    {
        if (! isset($this->price)) {
            return null;
        }

        return $this->formatPrice($this->price);
    }

    /**
     * Attribute: Get formatted total
     */
    public function getFormattedTotalAttribute(): ?string
    {
        if (! isset($this->total)) {
            return null;
        }

        return $this->formatPrice($this->total);
    }
}
