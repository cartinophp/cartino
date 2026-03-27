<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Brand;
use Cartino\Models\Product;
use Cartino\Models\ProductType;
use Cartino\Models\ProductVariant;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Configure the model factory to always create a default variant (like Shopify)
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            // If no variants exist yet, create a default variant
            if ($product->variants()->count() === 0) {
                $variant = ProductVariant::factory()->create([
                    'product_id' => $product->id,
                    'site_id' => $product->site_id,
                    'title' => 'Default Title',
                    'sku' => $product->handle.'-default',
                    'position' => 1,
                ]);

                // Set as default variant
                $product->update([
                    'default_variant_id' => $variant->id,
                    'variants_count' => 1,
                    'price_min' => $variant->price,
                    'price_max' => $variant->price,
                ]);
            }
        });
    }

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);
        // Ensure slug uniqueness across massive batches by appending a short random suffix
        $slugBase = Str::slug($title);
        $slug = $slugBase.'-'.Str::lower(Str::random(6));

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'title' => $title,
            'slug' => $slug,
            // Make handle unique per site reliably
            'handle' => Str::slug($slugBase.'-'.Str::lower(Str::random(8))),
            'excerpt' => $this->faker->sentence(10),
            'description' => $this->faker->paragraphs(3, true),
            'product_type' => 'physical',
            'brand_id' => fn () => Brand::query()->inRandomOrder()->value('id'),
            'product_type_id' => fn () => ProductType::query()->inRandomOrder()->value('id'),
            'options' => [
                ['name' => 'Color', 'values' => ['Red', 'Blue', 'Green']],
                ['name' => 'Size', 'values' => ['S', 'M', 'L']],
            ],
            'tags' => ['featured', 'demo'],
            'template_suffix' => null,
            'requires_selling_plan' => false,
            'status' => 'published',
            'published_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'published_scope' => 'web',
            'default_variant_id' => null,
            'data' => null,
        ];
    }

    public function forSite(int $siteId): static
    {
        return $this->state(fn () => ['site_id' => $siteId]);
    }

    public function digital(): static
    {
        return $this->state(fn () => ['product_type' => 'digital']);
    }
}
