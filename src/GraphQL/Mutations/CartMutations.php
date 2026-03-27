<?php

declare(strict_types=1);

namespace Cartino\GraphQL\Mutations;

use Cartino\Services\CartService;

class CartMutations
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    /**
     * Add item to cart
     */
    public function add($root, array $args): array
    {
        $cart = $this->cartService->add(
            productId: $args['product_id'],
            variantId: $args['variant_id'] ?? null,
            quantity: $args['quantity'],
        );

        return $this->formatCart($cart);
    }

    /**
     * Update cart line quantity
     */
    public function updateLine($root, array $args): array
    {
        $cart = $this->cartService->updateQuantity(
            lineId: $args['line_id'],
            quantity: $args['quantity'],
        );

        return $this->formatCart($cart);
    }

    /**
     * Remove line from cart
     */
    public function removeLine($root, array $args): array
    {
        $cart = $this->cartService->removeLine($args['line_id']);

        return $this->formatCart($cart);
    }

    /**
     * Clear cart
     */
    public function clear($root, array $args): array
    {
        $cart = $this->cartService->clear();

        return $this->formatCart($cart);
    }

    /**
     * Apply coupon
     */
    public function applyCoupon($root, array $args): array
    {
        $cart = $this->cartService->applyCoupon($args['code']);

        return $this->formatCart($cart);
    }

    /**
     * Remove coupon
     */
    public function removeCoupon($root, array $args): array
    {
        $cart = $this->cartService->removeCoupon();

        return $this->formatCart($cart);
    }

    /**
     * Format cart for GraphQL response
     */
    protected function formatCart($cart): array
    {
        return [
            'lines' => $cart
                ->lines
                ->map(fn ($line) => [
                    'id' => $line->id,
                    'product' => $line->product,
                    'variant' => $line->variant,
                    'quantity' => $line->quantity,
                    'price' => $line->price,
                    'total' => $line->total,
                ])
                ->toArray(),
            'subtotal' => $cart->subtotal,
            'shipping' => $cart->shipping,
            'tax' => $cart->tax,
            'discount' => $cart->discount,
            'total' => $cart->total,
            'coupon_code' => $cart->coupon_code,
        ];
    }
}
