<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\Cart;
use Cartino\Models\Channel;
use Cartino\Models\Customer;
use Cartino\Models\PriceRule;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Illuminate\Support\Collection;

/**
 * Price Calculator Service
 *
 * Applies dynamic price rules (discounts, promotions) based on complex conditions.
 * This is complementary to PricingService which handles base/catalog/specific prices.
 *
 * Flow:
 * 1. PricingService resolves base price (catalog, specific prices)
 * 2. PriceCalculator applies dynamic rules (promotions, customer group discounts, etc.)
 *
 * Inspired by Shopware's Rule Engine and PrestaShop's Specific Prices.
 */
class PriceCalculator
{
    /**
     * Calculate final price for a product variant with all applicable rules.
     *
     * @return array{base_price: float, final_price: float, discount: float, applied_rules: array, currency: string}
     */
    public function calculatePrice(
        ProductVariant $variant,
        ?Customer $customer = null,
        ?Channel $channel = null,
        int $quantity = 1,
        ?Cart $cart = null,
    ): array {
        // Get base price (could be from PricingService in real implementation)
        $basePrice = (float) $variant->price;
        $currency = $channel?->currency ?? config('cartino.currency', 'EUR');

        // Get applicable rules
        $rules = $this->getApplicableRules($variant, $customer, $channel, $quantity, $cart);

        $finalPrice = $basePrice;
        $appliedRules = [];
        $totalDiscount = 0;

        foreach ($rules as $rule) {
            $discountedPrice = $this->applyRule($finalPrice, $rule);

            if ($discountedPrice < $finalPrice) {
                $discount = $finalPrice - $discountedPrice;
                $finalPrice = $discountedPrice;
                $totalDiscount += $discount;

                $appliedRules[] = [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'discount_type' => $rule->discount_type,
                    'discount_value' => $rule->discount_value,
                    'discount_amount' => $discount,
                ];

                // Stop if rule says so
                if ($rule->stop_further_rules) {
                    break;
                }
            }
        }

        return [
            'base_price' => $basePrice,
            'final_price' => max(0, $finalPrice), // Never negative
            'discount' => $totalDiscount,
            'applied_rules' => $appliedRules,
            'currency' => $currency,
        ];
    }

    /**
     * Calculate prices for a product (considering all variants).
     *
     * @return array{min_price: float, max_price: float, variants: array}
     */
    public function calculateProductPrice(
        Product $product,
        ?Customer $customer = null,
        ?Channel $channel = null,
        int $quantity = 1,
    ): array {
        $variants = $product->variants()->where('status', 'active')->where('available', true)->get();

        if ($variants->isEmpty()) {
            return [
                'min_price' => 0,
                'max_price' => 0,
                'variants' => [],
            ];
        }

        $variantPrices = [];
        $minPrice = PHP_FLOAT_MAX;
        $maxPrice = 0;

        foreach ($variants as $variant) {
            $price = $this->calculatePrice($variant, $customer, $channel, $quantity);
            $variantPrices[$variant->id] = $price;

            $minPrice = min($minPrice, $price['final_price']);
            $maxPrice = max($maxPrice, $price['final_price']);
        }

        return [
            'min_price' => $minPrice === PHP_FLOAT_MAX ? 0 : $minPrice,
            'max_price' => $maxPrice,
            'variants' => $variantPrices,
        ];
    }

    /**
     * Get all price rules applicable to this context.
     *
     * @return Collection<PriceRule>
     */
    protected function getApplicableRules(
        ProductVariant $variant,
        ?Customer $customer,
        ?Channel $channel,
        int $quantity,
        ?Cart $cart,
    ): Collection {
        // Base query: active rules within time range, ordered by priority
        $rules = PriceRule::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                // Check usage limits
                $q->where(function ($q2) {
                    $q2->whereNull('usage_limit')->orWhereRaw('usage_count < usage_limit');
                });
            })
            ->orderBy('priority', 'desc')
            ->get();

        // Filter by conditions
        return $rules->filter(function ($rule) use ($variant, $customer, $channel, $quantity, $cart) {
            return $this->matchesConditions($rule, $variant, $customer, $channel, $quantity, $cart);
        });
    }

    /**
     * Check if a rule matches all conditions.
     */
    protected function matchesConditions(
        PriceRule $rule,
        ProductVariant $variant,
        ?Customer $customer,
        ?Channel $channel,
        int $quantity,
        ?Cart $cart,
    ): bool {
        // Check entity type and IDs
        if (! $this->matchesEntityType($rule, $variant)) {
            return false;
        }

        $conditions = $rule->conditions ?? [];

        // Customer group check
        if (! empty($conditions['customer_group_ids']) && $customer) {
            $customerGroupIds = $customer->customerGroups->pluck('id')->toArray();
            if (! array_intersect($conditions['customer_group_ids'], $customerGroupIds)) {
                return false;
            }
        }

        // Specific customer check
        if (! empty($conditions['customer_ids']) && $customer) {
            if (! in_array($customer->id, $conditions['customer_ids'])) {
                return false;
            }
        }

        // Channel check
        if (! empty($conditions['channel_ids']) && $channel) {
            if (! in_array($channel->id, $conditions['channel_ids'])) {
                return false;
            }
        }

        // Site check
        if (! empty($conditions['site_ids'])) {
            $siteId = $variant->product->site_id ?? $variant->site_id ?? null;
            if (! in_array($siteId, $conditions['site_ids'])) {
                return false;
            }
        }

        // Quantity checks
        if (isset($conditions['min_quantity']) && $quantity < $conditions['min_quantity']) {
            return false;
        }

        if (isset($conditions['max_quantity']) && $quantity > $conditions['max_quantity']) {
            return false;
        }

        // Cart value checks
        if ($cart) {
            if (isset($conditions['min_cart_value']) && $cart->subtotal < $conditions['min_cart_value']) {
                return false;
            }

            if (isset($conditions['max_cart_value']) && $cart->subtotal > $conditions['max_cart_value']) {
                return false;
            }
        }

        // Product attributes check
        if (! empty($conditions['product_attributes'])) {
            $product = $variant->product;
            foreach ($conditions['product_attributes'] as $attribute => $value) {
                if ($product->{$attribute} != $value) {
                    return false;
                }
            }
        }

        // Weekday check (for time-based promotions)
        if (! empty($conditions['weekdays'])) {
            $currentWeekday = (int) now()->dayOfWeek; // 0 = Sunday, 6 = Saturday
            if (! in_array($currentWeekday, $conditions['weekdays'])) {
                return false;
            }
        }

        // Country check
        if (! empty($conditions['country_ids']) && $customer) {
            $defaultAddress = $customer->addresses()->where('is_default', true)->first();
            if (! $defaultAddress || ! in_array($defaultAddress->country_id, $conditions['country_ids'])) {
                return false;
            }
        }

        // Usage limit per customer
        if ($customer && $rule->usage_limit_per_customer) {
            $usageCount = $rule->usages()->where('customer_id', $customer->id)->count();

            if ($usageCount >= $rule->usage_limit_per_customer) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if rule applies to this entity (product/variant/category/cart).
     */
    protected function matchesEntityType(PriceRule $rule, ProductVariant $variant): bool
    {
        // If entity_ids is null, rule applies to all
        if (empty($rule->entity_ids)) {
            return true;
        }

        $entityIds = is_array($rule->entity_ids) ? $rule->entity_ids : json_decode($rule->entity_ids, true);

        return match ($rule->entity_type) {
            'variant' => in_array($variant->id, $entityIds),
            'product' => in_array($variant->product_id, $entityIds),
            'category' => $this->productInCategories($variant->product, $entityIds),
            'cart' => true, // Cart-level rules always apply
            default => false,
        };
    }

    /**
     * Check if product belongs to any of the specified categories.
     */
    protected function productInCategories(Product $product, array $categoryIds): bool
    {
        return $product->categories()->whereIn('categories.id', $categoryIds)->exists();
    }

    /**
     * Apply a single rule to a price.
     */
    protected function applyRule(float $price, PriceRule $rule): float
    {
        return match ($rule->discount_type) {
            'percent' => $price * (1 - ($rule->discount_value / 100)),
            'fixed' => max(0, $price - $rule->discount_value),
            'override' => $rule->discount_value,
            default => $price,
        };
    }

    /**
     * Record rule usage for tracking and limits.
     */
    public function recordRuleUsage(
        PriceRule $rule,
        int $orderId,
        float $discountAmount,
        ?int $customerId = null,
    ): void {
        $rule->usages()->create([
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'discount_amount' => $discountAmount,
        ]);

        $rule->increment('usage_count');
    }
}
