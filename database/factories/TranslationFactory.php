<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'translatable_type' => null, // Must be set when using factory
            'translatable_id' => null, // Must be set when using factory
            'locale' => $this->faker->randomElement(['en_US', 'it_IT', 'fr_FR', 'de_DE', 'es_ES']),
            'key' => $this->faker->randomElement(['name', 'description', 'slug', 'title', 'content']),
            'value' => $this->faker->sentence(),
            'is_verified' => $this->faker->boolean(70),
            'source' => $this->faker->randomElement(['manual', 'auto', 'import']),
            'translated_by' => null,
        ];
    }

    public function forModel(string $type, int $id): self
    {
        return $this->state([
            'translatable_type' => $type,
            'translatable_id' => $id,
        ]);
    }

    public function verified(): self
    {
        return $this->state(['is_verified' => true]);
    }

    public function manual(): self
    {
        return $this->state(['source' => 'manual']);
    }
}
