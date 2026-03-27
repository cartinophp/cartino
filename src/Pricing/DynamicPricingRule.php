<?php

declare(strict_types=1);

namespace Cartino\Pricing;

use Cartino\Models\Customer;
use Cartino\Models\Product;
use Cartino\Pricing\Rules\PricingRuleInterface;

class DynamicPricingRule implements PricingRuleInterface
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getName(): string
    {
        return $this->model->name;
    }

    public function getPriority(): int
    {
        return $this->model->priority ?? 0;
    }

    public function isExclusive(): bool
    {
        return $this->model->is_exclusive ?? false;
    }

    public function appliesTo(Product $product, ?Customer $customer, int $quantity, array $context): bool
    {
        $conditions = $this->model->conditions ?? [];

        foreach ($conditions as $condition) {
            if (! $this->evaluateCondition($condition, $product, $customer, $quantity, $context)) {
                return false;
            }
        }

        return true;
    }

    public function calculateAdjustment(
        float $currentPrice,
        Product $product,
        int $quantity,
        array $context,
    ): ?array {
        $adjustment = $this->model->adjustment ?? [];

        if (empty($adjustment)) {
            return null;
        }

        return [
            'type' => $adjustment['type'] ?? 'percentage',
            'value' => $adjustment['value'] ?? 0,
        ];
    }

    protected function evaluateCondition(
        array $condition,
        Product $product,
        ?Customer $customer,
        int $quantity,
        array $context,
    ): bool {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        // Get field value based on context
        $fieldValue = match ($field) {
            'product.id' => $product->id,
            'product.category_id' => $product->category_id,
            'product.brand_id' => $product->brand_id,
            'product.price' => $product->price,
            'customer.id' => $customer?->id,
            'customer.group_id' => $customer?->group_id,
            'quantity' => $quantity,
            default => data_get($context, $field),
        };

        return match ($operator) {
            '=' => $fieldValue == $value,
            '!=' => $fieldValue != $value,
            '>' => $fieldValue > $value,
            '>=' => $fieldValue >= $value,
            '<' => $fieldValue < $value,
            '<=' => $fieldValue <= $value,
            'in' => in_array($fieldValue, (array) $value),
            'not_in' => ! in_array($fieldValue, (array) $value),
            default => false,
        };
    }
}
