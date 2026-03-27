<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Product;
use Cartino\Models\ProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

    public function definition(): array
    {
        $optionType = $this->faker->randomElement(['Size', 'Color', 'Material', 'Style']);

        $values = match ($optionType) {
            'Size' => $this->faker->randomElements(
                ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                $this->faker->numberBetween(3, 5),
            ),
            'Color' => $this->faker->randomElements(
                ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Pink'],
                $this->faker->numberBetween(3, 6),
            ),
            'Material' => $this->faker->randomElements(
                ['Cotton', 'Polyester', 'Leather', 'Wool', 'Silk'],
                $this->faker->numberBetween(2, 4),
            ),
            'Style' => $this->faker->randomElements(
                ['Classic', 'Modern', 'Vintage', 'Casual', 'Formal'],
                $this->faker->numberBetween(2, 4),
            ),
        };

        return [
            'product_id' => fn () => Product::factory(),
            'name' => $optionType,
            'position' => $this->faker->numberBetween(1, 3),
            'values' => array_values($values),
        ];
    }
}
