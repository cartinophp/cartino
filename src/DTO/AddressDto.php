<?php

declare(strict_types=1);

namespace Cartino\DTO;

class AddressDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $addressable_type = null,
        public ?int $addressable_id = null,
        public string $type = 'shipping',
        public string $first_name = '',
        public string $last_name = '',
        public ?string $company = null,
        public string $address_line_1 = '',
        public ?string $address_line_2 = null,
        public string $city = '',
        public ?string $state = null,
        public string $postal_code = '',
        public int $country_id = 1,
        public ?string $phone = null,
        public bool $is_default = false,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    /**
     * Create from array
     */
    public static function from(array $data): static
    {
        return new static(
            id: $data['id'] ?? null,
            addressable_type: $data['addressable_type'] ?? null,
            addressable_id: isset($data['addressable_id']) ? ((int) $data['addressable_id']) : null,
            type: $data['type'] ?? 'shipping',
            first_name: $data['first_name'] ?? '',
            last_name: $data['last_name'] ?? '',
            company: $data['company'] ?? null,
            address_line_1: $data['address_line_1'] ?? '',
            address_line_2: $data['address_line_2'] ?? null,
            city: $data['city'] ?? '',
            state: $data['state'] ?? null,
            postal_code: $data['postal_code'] ?? '',
            country_id: (int) ($data['country_id'] ?? 1),
            phone: $data['phone'] ?? null,
            is_default: filter_var($data['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'id' => $this->id,
                'addressable_type' => $this->addressable_type,
                'addressable_id' => $this->addressable_id,
                'type' => $this->type,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'company' => $this->company,
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country_id' => $this->country_id,
                'phone' => $this->phone,
                'is_default' => $this->is_default,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            fn ($value) => $value !== null,
        );
    }

    /**
     * Validate address data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->first_name))) {
            $errors['first_name'] = 'First name is required';
        }

        if (strlen($this->first_name) > 255) {
            $errors['first_name'] = 'First name cannot exceed 255 characters';
        }

        if (empty(trim($this->last_name))) {
            $errors['last_name'] = 'Last name is required';
        }

        if (strlen($this->last_name) > 255) {
            $errors['last_name'] = 'Last name cannot exceed 255 characters';
        }

        if (empty(trim($this->address_line_1))) {
            $errors['address_line_1'] = 'Address line 1 is required';
        }

        if (strlen($this->address_line_1) > 255) {
            $errors['address_line_1'] = 'Address line 1 cannot exceed 255 characters';
        }

        if (! empty($this->address_line_2) && strlen($this->address_line_2) > 255) {
            $errors['address_line_2'] = 'Address line 2 cannot exceed 255 characters';
        }

        if (empty(trim($this->city))) {
            $errors['city'] = 'City is required';
        }

        if (strlen($this->city) > 255) {
            $errors['city'] = 'City cannot exceed 255 characters';
        }

        if (! empty($this->state) && strlen($this->state) > 255) {
            $errors['state'] = 'State cannot exceed 255 characters';
        }

        if (empty(trim($this->postal_code))) {
            $errors['postal_code'] = 'Postal code is required';
        }

        if (strlen($this->postal_code) > 20) {
            $errors['postal_code'] = 'Postal code cannot exceed 20 characters';
        }

        if ($this->country_id <= 0) {
            $errors['country_id'] = 'Country is required';
        }

        if (! in_array($this->type, ['shipping', 'billing', 'both'])) {
            $errors['type'] = 'Address type must be shipping, billing, or both';
        }

        if (! empty($this->phone) && ! preg_match('/^[\+]?[0-9\s\-\(\)]{8,20}$/', $this->phone)) {
            $errors['phone'] = 'Phone number format is invalid';
        }

        if (! empty($this->company) && strlen($this->company) > 255) {
            $errors['company'] = 'Company name cannot exceed 255 characters';
        }

        return $errors;
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get formatted address
     */
    public function getFormattedAddress(bool $includeCountry = true): string
    {
        $parts = [];

        $parts[] = $this->address_line_1;

        if ($this->address_line_2) {
            $parts[] = $this->address_line_2;
        }

        $cityStateZip = $this->city;
        if ($this->state) {
            $cityStateZip .= ', '.$this->state;
        }
        $cityStateZip .= ' '.$this->postal_code;

        $parts[] = $cityStateZip;

        if ($includeCountry && $this->country_id) {
            // This would need to be resolved with actual country name
            $parts[] = "Country ID: {$this->country_id}";
        }

        return implode("\n", $parts);
    }

    /**
     * Get single line address
     */
    public function getSingleLineAddress(): string
    {
        $parts = [$this->address_line_1];

        if ($this->address_line_2) {
            $parts[] = $this->address_line_2;
        }

        $parts[] = $this->city;

        if ($this->state) {
            $parts[] = $this->state;
        }

        $parts[] = $this->postal_code;

        return implode(', ', array_filter($parts));
    }

    /**
     * Check if is default address
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Check if is shipping address
     */
    public function isShipping(): bool
    {
        return in_array($this->type, ['shipping', 'both']);
    }

    /**
     * Check if is billing address
     */
    public function isBilling(): bool
    {
        return in_array($this->type, ['billing', 'both']);
    }

    /**
     * Check if has company
     */
    public function hasCompany(): bool
    {
        return ! empty($this->company);
    }

    /**
     * Get display name (name + company if exists)
     */
    public function getDisplayName(): string
    {
        $name = $this->getFullName();

        if ($this->hasCompany()) {
            return "{$name} ({$this->company})";
        }

        return $name;
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhone(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        // Basic phone formatting (can be enhanced based on locale)
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', preg_replace('/\D/', '', $this->phone));
    }
}
