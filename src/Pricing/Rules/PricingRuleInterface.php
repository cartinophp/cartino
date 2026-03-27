<?php

declare(strict_types=1);

namespace Cartino\Pricing\Rules;

use Cartino\Models\Customer;
use Cartino\Models\Product;

interface PricingRuleInterface
{
    /**
     * Get rule name
     */
    public function getName(): string;

    /**
     * Get rule priority
     */
    public function getPriority(): int;

    /**
     * Check if rule is exclusive (stops other rules from applying)
     */
    public function isExclusive(): bool;

    /**
     * Check if rule applies to the given product/customer/context
     */
    public function appliesTo(Product $product, ?Customer $customer, int $quantity, array $context): bool;

    /**
     * Calculate price adjustment
     *
     * @return array{type: string, value: float}|null
     */
    public function calculateAdjustment(
        float $currentPrice,
        Product $product,
        int $quantity,
        array $context,
    ): ?array;
}
