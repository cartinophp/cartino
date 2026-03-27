<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $first = $this->faker->firstName();
        $last = $this->faker->lastName();

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'first_name' => $first,
            'last_name' => $last,
            'email' => strtolower(Str::slug($first.$last)).'@example.com',
            'phone' => $this->faker->e164PhoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'status' => 'active',
            'last_login_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'data' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
