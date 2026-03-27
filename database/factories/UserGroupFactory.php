<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGroupFactory extends Factory
{
    protected $model = UserGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'is_default' => false,
            'metadata' => null,
        ];
    }
}
