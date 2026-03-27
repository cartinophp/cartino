<?php

declare(strict_types=1);

namespace Cartino\Pricing\Rules;

use Cartino\Models\Customer;
use Cartino\Models\Product;

class CustomerGroupDiscountRule implements PricingRuleInterface
{
    protected array $discounts = [
        'wholesale' => 20,
        'vip' => 15,
        'retail' => 5,
    ];

    public function getName(): string
    {
        return 'Customer Group Discount';
    }

    public function getPriority(): int
    {
        return 20;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function appliesTo(Product $product, ?Customer $customer, int $quantity, array $context): bool
    {
        if (! $customer || ! $customer->group) {
            return false;
        }

        return array_key_exists($customer->group->code, $this->discounts);
    }

    public function calculateAdjustment(
        float $currentPrice,
        Product $product,
        int $quantity,
        array $context,
    ): ?array {
        $customer = $context['customer'] ?? null;

        if (! $customer || ! $customer->group) {
            return null;
        }

        $discount = $this->discounts[$customer->group->code] ?? null;

        if ($discount === null) {
            return null;
        }

        return [
            'type' => 'percentage',
            'value' => $discount,
        ];
    }
}
