<?php

namespace Cartino\Database\Factories;

use Cartino\Models\GlobalSet;
use Illuminate\Database\Eloquent\Factories\Factory;

class GlobalFactory extends Factory
{
    protected $model = GlobalSet::class;

    public function definition(): array
    {
        return [
            'handle' => $this->faker->unique()->slug(2),
            'title' => $this->faker->words(3, true),
            'data' => [
                'key1' => $this->faker->word,
                'key2' => $this->faker->sentence,
                'nested' => [
                    'field1' => $this->faker->word,
                    'field2' => $this->faker->numberBetween(1, 100),
                ],
            ],
        ];
    }

    /**
     * Site settings global
     */
    public function siteSettings(): static
    {
        return $this->state(fn (array $attributes) => [
            'handle' => 'site_settings',
            'title' => 'Site Settings',
            'data' => [
                'site_name' => $this->faker->company,
                'contact_email' => $this->faker->companyEmail,
                'contact_phone' => $this->faker->phoneNumber,
            ],
        ]);
    }

    /**
     * Social media global
     */
    public function socialMedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'handle' => 'social_media',
            'title' => 'Social Media',
            'data' => [
                'facebook' => 'https://facebook.com/'.$this->faker->userName,
                'instagram' => 'https://instagram.com/'.$this->faker->userName,
                'twitter' => 'https://twitter.com/'.$this->faker->userName,
            ],
        ]);
    }
}
