<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Order;
use Cartino\Models\OrderLine;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderLineFactory extends Factory
{
    protected $model = OrderLine::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 3);
        $unitPrice = $this->faker->randomFloat(2, 5, 150);

        return [
            'order_id' => fn () => Order::factory(),
            'product_id' => fn () => Product::factory(),
            'product_variant_id' => null,
            'product_name' => $this->faker->word(4, true),
            'product_sku' => 'SKU-'.$this->faker->unique()->numerify('####'),
            'product_options' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * $quantity,
            'meta' => null,
        ];
    }

    public function forVariant(ProductVariant $variant, Order $order): static
    {
        return $this->state(fn () => [
            'order_id' => $order->id,
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->title,
            'product_sku' => $variant->sku,
            'unit_price' => $variant->price,
            'quantity' => 1,
            'line_total' => $variant->price,
        ]);
    }
}
