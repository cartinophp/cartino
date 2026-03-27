<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\ProductVariant;
use Cartino\Models\PurchaseOrder;
use Cartino\Models\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 100);
        $unitCost = $this->faker->randomFloat(2, 5, 500);

        return [
            'purchase_order_id' => PurchaseOrder::query()->inRandomOrder()->value('id') ?? PurchaseOrder::factory(),
            'product_variant_id' => ProductVariant::query()->inRandomOrder()->value('id') ?? ProductVariant::factory(),
            'quantity' => $quantity,
            'received_quantity' => $this->faker->numberBetween(0, $quantity),
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
