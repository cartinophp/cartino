<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\Customer;
use Cartino\Models\Discount;
use Cartino\Models\DiscountApplication;
use Cartino\Models\Order;
use Illuminate\Support\Str;

class DiscountService
{
    public function createDiscount(array $data): Discount
    {
        // Generate unique code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->generateUniqueCode($data['name']);
        }

        $discount = Discount::create($data);

        return $discount;
    }

    public function updateDiscount(Discount $discount, array $data): Discount
    {
        $discount->update($data);

        return $discount->fresh();
    }

    public function deleteDiscount(Discount $discount): bool
    {
        // Soft delete or hard delete based on applications
        if ($discount->applications()->count() > 0) {
            // If discount has been used, just disable it
            $discount->update(['status' => 'inactive']);

            return true;
        }

        return $discount->delete();
    }

    public function validateDiscountCode(string $code, ?int $customerId = null): array
    {
        $discount = Discount::where('code', $code)->first();

        if (! $discount) {
            return [
                'valid' => false,
                'message' => __('discount.messages.code_not_found'),
                'discount' => null,
            ];
        }

        if (! $discount->isActive()) {
            return [
                'valid' => false,
                'message' => __('discount.messages.code_inactive'),
                'discount' => $discount,
            ];
        }

        if (! $this->canCustomerUseDiscount($discount, $customerId)) {
            return [
                'valid' => false,
                'message' => __('discount.messages.usage_limit_exceeded'),
                'discount' => $discount,
            ];
        }

        return [
            'valid' => true,
            'message' => __('discount.messages.code_valid'),
            'discount' => $discount,
        ];
    }

    public function applyDiscountToOrder(Discount $discount, Order $order): ?DiscountApplication
    {
        if (! $this->canApplyDiscountToOrder($discount, $order)) {
            return null;
        }

        // Remove any existing discount applications for this order
        $this->removeDiscountsFromOrder($order);

        $discountAmount = $this->calculateDiscountForOrder($discount, $order);

        if ($discountAmount <= 0) {
            return null;
        }

        // Create application record
        $application = DiscountApplication::create([
            'discount_id' => $discount->id,
            'applicable_type' => Order::class,
            'applicable_id' => $order->id,
            'discount_amount' => $discountAmount,
            'applied_at' => now(),
        ]);

        // Increment usage count
        $discount->increment('usage_count');

        // Update order total
        $this->updateOrderTotal($order);

        return $application;
    }

    public function removeDiscountsFromOrder(Order $order): void
    {
        $applications = DiscountApplication::where('applicable_type', Order::class)
            ->where('applicable_id', $order->id)
            ->get();

        foreach ($applications as $application) {
            $application->discount->decrement('usage_count');
            $application->delete();
        }

        $this->updateOrderTotal($order);
    }

    public function canApplyDiscountToOrder(Discount $discount, Order $order): bool
    {
        if (! $discount->isActive()) {
            return false;
        }

        // Check minimum order amount
        if ($discount->minimum_order_amount && $order->subtotal < $discount->minimum_order_amount) {
            return false;
        }

        // Check customer usage limit
        if (! $this->canCustomerUseDiscount($discount, $order->customer_id)) {
            return false;
        }

        // Check eligible customers
        if (! empty($discount->eligible_customers)) {
            if (! in_array($order->customer_id, $discount->eligible_customers)) {
                return false;
            }
        }

        // Check eligible products
        if (! empty($discount->eligible_products)) {
            $orderProductIds = $order->items->pluck('product_id')->toArray();
            $hasEligibleProducts = ! empty(array_intersect($discount->eligible_products, $orderProductIds));

            if (! $hasEligibleProducts) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscountForOrder(Discount $discount, Order $order): float
    {
        $baseAmount = $order->subtotal;

        // If specific products are eligible, calculate discount only on those
        if (! empty($discount->eligible_products)) {
            $eligibleItems = $order->items->whereIn('product_id', $discount->eligible_products);
            $baseAmount = $eligibleItems->sum(fn ($item) => $item->quantity * $item->price);
        }

        switch ($discount->type) {
            case 'percentage':
                $discountAmount = ($baseAmount * $discount->value) / 100;
                break;
            case 'fixed_amount':
                $discountAmount = min($discount->value, $baseAmount);
                break;
            case 'free_shipping':
                $discountAmount = $order->shipping_cost ?? 0;
                break;
            default:
                $discountAmount = 0;
        }

        // Apply maximum discount limit
        if ($discount->maximum_discount_amount) {
            $discountAmount = min($discountAmount, $discount->maximum_discount_amount);
        }

        return round($discountAmount, 2);
    }

    public function canCustomerUseDiscount(Discount $discount, ?int $customerId): bool
    {
        if (! $customerId || ! $discount->usage_limit_per_customer) {
            return true;
        }

        $customerUsage = DiscountApplication::where('discount_id', $discount->id)
            ->where('applicable_type', Order::class)
            ->whereHas('applicable', function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->count();

        return $customerUsage < $discount->usage_limit_per_customer;
    }

    public function getActiveDiscounts(): Category
    {
        return Discount::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getDiscountStatistics(Discount $discount): array
    {
        $applications = $discount->applications();

        return [
            'total_applications' => $applications->count(),
            'total_discount_amount' => $applications->sum('discount_amount'),
            'unique_customers' => $applications
                ->where('applicable_type', Order::class)
                ->join('orders', function ($join) {
                    $join->on('discount_applications.applicable_id', '=', 'orders.id')->where(
                        'discount_applications.applicable_type',
                        Order::class,
                    );
                })
                ->distinct('orders.customer_id')
                ->count('orders.customer_id'),
            'usage_percentage' => $discount->usage_limit
                ? (($discount->usage_count / $discount->usage_limit) * 100)
                : 0,
        ];
    }

    protected function generateUniqueCode(string $name): string
    {
        $baseCode = Str::upper(Str::slug($name, ''));
        $code = $baseCode;
        $counter = 1;

        while (Discount::where('code', $code)->exists()) {
            $code = $baseCode.$counter;
            $counter++;
        }

        return $code;
    }

    protected function updateOrderTotal(Order $order): void
    {
        $discountAmount = DiscountApplication::where('applicable_type', Order::class)
            ->where('applicable_id', $order->id)
            ->sum('discount_amount');

        $order->update([
            'discount_amount' => $discountAmount,
            'total' => ($order->subtotal + $order->tax_amount + $order->shipping_cost) - $discountAmount,
        ]);
    }
}
