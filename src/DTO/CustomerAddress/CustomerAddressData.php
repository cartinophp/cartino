<?php

namespace Cartino\DTO\CustomerAddress;

use Cartino\Models\CustomerAddress;

class CustomerAddressData
{
    public function __construct(
        public ?int $id,
        public int $customer_id,
        public string $type,
        public string $first_name,
        public string $last_name,
        public ?string $company,
        public string $address_line_1,
        public ?string $address_line_2,
        public string $city,
        public ?string $state,
        public string $postal_code,
        public string $country_code,
        public ?string $phone,
        public bool $is_default,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(CustomerAddress $address): self
    {
        return new self(
            id: $address->id,
            customer_id: $address->customer_id,
            type: $address->type,
            first_name: $address->first_name,
            last_name: $address->last_name,
            company: $address->company,
            address_line_1: $address->address_line_1,
            address_line_2: $address->address_line_2,
            city: $address->city,
            state: $address->state,
            postal_code: $address->postal_code,
            country_code: $address->country_code,
            phone: $address->phone,
            is_default: $address->is_default,
            created_at: $address->created_at?->toISOString(),
            updated_at: $address->updated_at?->toISOString(),
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            customer_id: (int) ($data['customer_id'] ?? 0),
            type: $data['type'] ?? 'shipping',
            first_name: $data['first_name'] ?? '',
            last_name: $data['last_name'] ?? '',
            company: $data['company'] ?? null,
            address_line_1: $data['address_line_1'] ?? '',
            address_line_2: $data['address_line_2'] ?? null,
            city: $data['city'] ?? '',
            state: $data['state'] ?? null,
            postal_code: $data['postal_code'] ?? '',
            country_code: $data['country_code'] ?? '',
            phone: $data['phone'] ?? null,
            is_default: (bool) ($data['is_default'] ?? false),
            created_at: $data['created_at'] ?? now()->toISOString(),
            updated_at: $data['updated_at'] ?? now()->toISOString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
