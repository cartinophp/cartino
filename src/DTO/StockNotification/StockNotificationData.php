<?php

namespace Cartino\DTO\StockNotification;

use Cartino\Models\StockNotification;

class StockNotificationData
{
    public function __construct(
        public ?int $id,
        public int $customer_id,
        public string $product_type,
        public int $product_id,
        public ?string $product_handle,
        public array $product_data,
        public array $variant_data,
        public string $email,
        public ?string $phone,
        public string $preferred_method,
        public string $status,
        public ?string $notified_at,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(StockNotification $notification): self
    {
        return new self(
            id: $notification->id,
            customer_id: $notification->customer_id,
            product_type: $notification->product_type,
            product_id: $notification->product_id,
            product_handle: $notification->product_handle,
            product_data: $notification->product_data ?? [],
            variant_data: $notification->variant_data ?? [],
            email: $notification->email,
            phone: $notification->phone,
            preferred_method: $notification->preferred_method,
            status: $notification->status,
            notified_at: $notification->notified_at?->toISOString(),
            created_at: $notification->created_at?->toISOString(),
            updated_at: $notification->updated_at?->toISOString(),
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            customer_id: (int) ($data['customer_id'] ?? 0),
            product_type: $data['product_type'] ?? '',
            product_id: (int) ($data['product_id'] ?? 0),
            product_handle: $data['product_handle'] ?? null,
            product_data: $data['product_data'] ?? [],
            variant_data: $data['variant_data'] ?? [],
            email: $data['email'] ?? '',
            phone: $data['phone'] ?? null,
            preferred_method: $data['preferred_method'] ?? 'email',
            status: $data['status'] ?? 'pending',
            notified_at: $data['notified_at'] ?? null,
            created_at: $data['created_at'] ?? now()->toISOString(),
            updated_at: $data['updated_at'] ?? now()->toISOString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'product_type' => $this->product_type,
            'product_id' => $this->product_id,
            'product_handle' => $this->product_handle,
            'product_data' => $this->product_data,
            'variant_data' => $this->variant_data,
            'email' => $this->email,
            'phone' => $this->phone,
            'preferred_method' => $this->preferred_method,
            'status' => $this->status,
            'notified_at' => $this->notified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
