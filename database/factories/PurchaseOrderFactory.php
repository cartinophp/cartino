<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\PurchaseOrder;
use Cartino\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 10000);
        $tax = $subtotal * 0.22;

        return [
            'supplier_id' => Supplier::query()->inRandomOrder()->value('id') ?? Supplier::factory(),
            'po_number' => 'PO-'.strtoupper($this->faker->unique()->bothify('####-????')),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'approved', 'received', 'cancelled']),
            'order_date' => $this->faker->dateTimeBetween('-3 months'),
            'expected_delivery_date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'received_date' => $this->faker->optional()->dateTimeBetween('-1 month'),
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'shipping_amount' => $this->faker->randomFloat(2, 0, 100),
            'total_amount' => $subtotal + $tax,
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
