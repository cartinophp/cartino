<?php

declare(strict_types=1);

namespace Cartino\DTO;

class BrandDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $status = 'active',
        public readonly ?string $description = null,
        public readonly ?string $website = null,
        public readonly ?string $email = null,
        public readonly ?int $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            status: $data['status'] ?? 'active',
            description: $data['description'] ?? null,
            website: $data['website'] ?? null,
            email: $data['email'] ?? null,
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
                'status' => $this->status,
                'description' => $this->description,
                'website' => $this->website,
                'email' => $this->email,
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
