<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => $this->faker->boolean(80) ? now() : null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function verified(): self
    {
        return $this->state(['email_verified_at' => now()]);
    }

    public function unverified(): self
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function admin(): self
    {
        return $this->state([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
        ]);
    }
}
