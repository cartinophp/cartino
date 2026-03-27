<?php

namespace Cartino\DTO\Wishlist;

use Cartino\Models\Wishlist;

class WishlistData
{
    public function __construct(
        public int $id,
        public int $customer_id,
        public string $name,
        public ?string $description,
        public string $status,
        public bool $is_shared,
        public ?string $share_token,
        public int $items_count,
        public string $created_at,
        public string $updated_at,
        public ?array $customer = null,
        public ?array $items = null,
    ) {}

    public static function fromModel(Wishlist $wishlist): self
    {
        return new self(
            id: $wishlist->id,
            customer_id: $wishlist->customer_id,
            name: $wishlist->name,
            description: $wishlist->description,
            status: $wishlist->status,
            is_shared: $wishlist->is_shared,
            share_token: $wishlist->share_token,
            items_count: $wishlist->items_count ?? $wishlist->items()->count(),
            created_at: $wishlist->created_at->toISOString(),
            updated_at: $wishlist->updated_at->toISOString(),
            customer: $wishlist->relationLoaded('customer') && $wishlist->customer
                ? [
                    'id' => $wishlist->customer->id,
                    'first_name' => $wishlist->customer->first_name,
                    'last_name' => $wishlist->customer->last_name,
                    'email' => $wishlist->customer->email,
                ] : null,
            items: $wishlist->relationLoaded('items')
                ? $wishlist
                    ->items
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                        'created_at' => $item->created_at->toISOString(),
                        'product' => $item->relationLoaded('product') && $item->product
                            ? [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'price' => $item->product->price,
                                'image' => $item->product->image,
                            ] : null,
                    ])
                    ->toArray()
                : null,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            customer_id: (int) ($data['customer_id'] ?? 0),
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            status: $data['status'] ?? 'active',
            is_shared: (bool) ($data['is_shared'] ?? false),
            share_token: $data['share_token'] ?? null,
            items_count: (int) ($data['items_count'] ?? 0),
            created_at: $data['created_at'] ?? now()->toISOString(),
            updated_at: $data['updated_at'] ?? now()->toISOString(),
            customer: $data['customer'] ?? null,
            items: $data['items'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'is_shared' => $this->is_shared,
            'share_token' => $this->share_token,
            'items_count' => $this->items_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer' => $this->customer,
            'items' => $this->items,
        ];
    }
}
