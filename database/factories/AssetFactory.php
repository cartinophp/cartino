<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Asset;
use Cartino\Models\AssetContainer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $container = AssetContainer::inRandomOrder()->value('handle') ?? 'assets';
        $folder = $this->faker->randomElement(['brands', 'categories', 'products', 'gallery', 'docs', 'videos']);

        $mimeChoices = [
            [
                'ext' => 'jpg',
                'mime' => 'image/jpeg',
                'width' => $this->faker->numberBetween(800, 1920),
                'height' => $this->faker->numberBetween(800, 1920),
                'size' => $this->faker->numberBetween(80_000, 2_000_000),
            ],
            [
                'ext' => 'png',
                'mime' => 'image/png',
                'width' => $this->faker->numberBetween(800, 1920),
                'height' => $this->faker->numberBetween(800, 1920),
                'size' => $this->faker->numberBetween(120_000, 3_000_000),
            ],
            [
                'ext' => 'webp',
                'mime' => 'image/webp',
                'width' => $this->faker->numberBetween(800, 1920),
                'height' => $this->faker->numberBetween(800, 1920),
                'size' => $this->faker->numberBetween(80_000, 1_500_000),
            ],
            [
                'ext' => 'pdf',
                'mime' => 'application/pdf',
                'width' => null,
                'height' => null,
                'size' => $this->faker->numberBetween(200_000, 5_000_000),
            ],
            [
                'ext' => 'mp4',
                'mime' => 'video/mp4',
                'width' => 1920,
                'height' => 1080,
                'size' => $this->faker->numberBetween(5_000_000, 50_000_000),
                'duration' => $this->faker->numberBetween(15, 180),
            ],
        ];

        $file = $this->faker->randomElement($mimeChoices);
        $filename = Str::uuid()->toString();
        $basename = $filename.'.'.$file['ext'];
        $path = $folder.'/'.$basename;

        return [
            'container' => $container,
            'folder' => $folder,
            'basename' => $basename,
            'filename' => $filename,
            'extension' => $file['ext'],
            'path' => $path,
            'mime_type' => $file['mime'],
            'size' => $file['size'],
            'width' => $file['width'],
            'height' => $file['height'],
            'duration' => $file['duration'] ?? null,
            'aspect_ratio' => isset($file['width'], $file['height']) && $file['height'] > 0
                ? round($file['width'] / $file['height'], 4)
                : null,
            'meta' => [
                'alt' => $this->faker->sentence(3),
                'title' => $this->faker->sentence(6),
            ],
            'data' => [
                'caption' => $this->faker->sentence(8),
            ],
            'focus_css' => null,
            'uploaded_by' => null,
            'hash' => Str::random(40),
        ];
    }
}
