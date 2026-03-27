<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Cart;
use Cartino\Models\CartLine;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartLineFactory extends Factory
{
    protected $model = CartLine::class;

    public function definition(): array
    {
        return [
            'cart_id' => fn () => Cart::factory(),
            'product_id' => fn () => Product::factory(),
            'product_variant_id' => null,
            'quantity' => $this->faker->numberBetween(1, 3),
            'unit_price' => $this->faker->randomFloat(2, 5, 150),
            'line_total' => fn (array $attributes) => ($attributes['unit_price'] ?? 0) * ($attributes['quantity'] ?? 1),
            'product_options' => null,
            'meta' => null,
        ];
    }

    public function withVariant(ProductVariant $variant): static
    {
        return $this->state(fn () => [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'unit_price' => $variant->price,
            'line_total' => $variant->price,
        ]);
    }
}
