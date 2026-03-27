<?php

declare(strict_types=1);

namespace Cartino\DTO;

class CourierDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $code,
        public readonly string $status = 'active',
        public readonly bool $is_enabled = true,
        public readonly ?string $description = null,
        public readonly ?string $website = null,
        public readonly ?string $tracking_url = null,
        public readonly ?int $delivery_time_min = null,
        public readonly ?int $delivery_time_max = null,
        public readonly ?int $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            code: $data['code'],
            status: $data['status'] ?? 'active',
            is_enabled: $data['is_enabled'] ?? true,
            description: $data['description'] ?? null,
            website: $data['website'] ?? null,
            tracking_url: $data['tracking_url'] ?? null,
            delivery_time_min: $data['delivery_time_min'] ?? null,
            delivery_time_max: $data['delivery_time_max'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
                'code' => $this->code,
                'status' => $this->status,
                'is_enabled' => $this->is_enabled,
                'description' => $this->description,
                'website' => $this->website,
                'tracking_url' => $this->tracking_url,
                'delivery_time_min' => $this->delivery_time_min,
                'delivery_time_max' => $this->delivery_time_max,
            ],
            fn ($value) => $value !== null,
        );
    }

    public function toCreateArray(): array
    {
        $data = $this->toArray();
        unset($data['id']); // Remove ID for creation

        return $data;
    }
}
