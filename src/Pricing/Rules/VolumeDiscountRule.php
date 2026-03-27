<?php

declare(strict_types=1);

namespace Cartino\Pricing\Rules;

use Cartino\Models\Customer;
use Cartino\Models\Product;

class VolumeDiscountRule implements PricingRuleInterface
{
    protected array $tiers = [
        ['min' => 10, 'max' => 49, 'discount' => 5],
        ['min' => 50, 'max' => 99, 'discount' => 10],
        ['min' => 100, 'max' => null, 'discount' => 15],
    ];

    public function getName(): string
    {
        return 'Volume Discount';
    }

    public function getPriority(): int
    {
        return 10;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function appliesTo(Product $product, ?Customer $customer, int $quantity, array $context): bool
    {
        return $quantity >= 10;
    }

    public function calculateAdjustment(
        float $currentPrice,
        Product $product,
        int $quantity,
        array $context,
    ): ?array {
        foreach ($this->tiers as $tier) {
            if ($quantity >= $tier['min'] && ($tier['max'] === null || $quantity <= $tier['max'])) {
                return [
                    'type' => 'percentage',
                    'value' => $tier['discount'],
                ];
            }
        }

        return null;
    }
}
