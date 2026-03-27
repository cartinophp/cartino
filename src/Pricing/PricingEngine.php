<?php

declare(strict_types=1);

namespace Cartino\Pricing;

use Cartino\Models\Customer;
use Cartino\Models\PricingRule;
use Cartino\Models\Product;
use Cartino\Pricing\Rules\PricingRuleInterface;
use Illuminate\Support\Collection;

class PricingEngine
{
    protected Collection $rules;

    protected ?Customer $customer = null;

    protected array $context = [];

    public function __construct()
    {
        $this->rules = collect();
        $this->loadRules();
    }

    /**
     * Load all active pricing rules
     */
    protected function loadRules(): void
    {
        $rules = PricingRule::where('is_active', true)->orderBy('priority', 'desc')->get();

        foreach ($rules as $ruleModel) {
            $rule = $this->instantiateRule($ruleModel);

            if ($rule) {
                $this->rules->push($rule);
            }
        }
    }

    /**
     * Instantiate pricing rule from model
     */
    protected function instantiateRule($model): ?PricingRuleInterface
    {
        if ($model->type === 'custom' && class_exists($model->class)) {
            return new $model->class($model);
        }

        return new DynamicPricingRule($model);
    }

    /**
     * Set customer context
     */
    public function forCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Set additional context
     */
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    /**
     * Calculate price for a product
     */
    public function calculatePrice(Product $product, int $quantity = 1): PriceResult
    {
        $basePrice = $product->price;
        $originalPrice = $basePrice;
        $appliedRules = collect();

        foreach ($this->rules as $rule) {
            if ($rule->appliesTo($product, $this->customer, $quantity, $this->context)) {
                $adjustment = $rule->calculateAdjustment($basePrice, $product, $quantity, $this->context);

                if ($adjustment !== null) {
                    $basePrice = $this->applyAdjustment($basePrice, $adjustment);
                    $appliedRules->push([
                        'rule' => $rule->getName(),
                        'adjustment' => $adjustment,
                        'price_after' => $basePrice,
                    ]);

                    // Stop if rule is exclusive
                    if ($rule->isExclusive()) {
                        break;
                    }
                }
            }
        }

        return new PriceResult(
            originalPrice: $originalPrice,
            finalPrice: max(0, $basePrice),
            appliedRules: $appliedRules,
            quantity: $quantity,
        );
    }

    /**
     * Calculate prices for multiple products (bulk pricing)
     */
    public function calculateBulkPrices(array $items): Collection
    {
        return collect($items)->map(function ($item) {
            return $this->calculatePrice($item['product'], $item['quantity'] ?? 1);
        });
    }

    /**
     * Get tiered pricing for a product
     */
    public function getTieredPricing(Product $product): Collection
    {
        $tiers = collect([
            ['min_quantity' => 1, 'max_quantity' => 9],
            ['min_quantity' => 10, 'max_quantity' => 49],
            ['min_quantity' => 50, 'max_quantity' => 99],
            ['min_quantity' => 100, 'max_quantity' => null],
        ]);

        return $tiers->map(function ($tier) use ($product) {
            $quantity = $tier['min_quantity'];
            $result = $this->calculatePrice($product, $quantity);

            return [
                'min_quantity' => $tier['min_quantity'],
                'max_quantity' => $tier['max_quantity'],
                'price' => $result->finalPrice,
                'discount_percentage' => $result->getDiscountPercentage(),
            ];
        });
    }

    /**
     * Apply price adjustment
     */
    protected function applyAdjustment(float $price, array $adjustment): float
    {
        $type = $adjustment['type'];
        $value = $adjustment['value'];

        return match ($type) {
            'fixed' => $price - $value,
            'percentage' => $price * (1 - ($value / 100)),
            'fixed_price' => $value,
            'multiply' => $price * $value,
            default => $price,
        };
    }

    /**
     * Get best price among multiple contexts
     */
    public function getBestPrice(Product $product, array $contexts): PriceResult
    {
        $prices = collect($contexts)->map(function ($context) use ($product) {
            return $this->withContext($context)->calculatePrice($product);
        });

        return $prices->sortBy('finalPrice')->first();
    }
}
