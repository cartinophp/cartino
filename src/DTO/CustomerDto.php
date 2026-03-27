<?php

namespace Cartino\DTO;

use DateTime;

class CustomerDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public string $first_name = '',
        public string $last_name = '',
        public string $email = '',
        public ?string $phone = null,
        public ?DateTime $date_of_birth = null,
        public ?string $gender = null,
        public ?DateTime $email_verified_at = null,
        public ?string $password = null,
        public string $status = 'active',
        public ?DateTime $last_login_at = null,
        public ?string $last_login_ip = null,
        public ?string $avatar = null,
        public array $meta = [],
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
            first_name: $data['first_name'] ?? '',
            last_name: $data['last_name'] ?? '',
            email: $data['email'] ?? '',
            phone: $data['phone'] ?? null,
            date_of_birth: isset($data['date_of_birth']) ? new DateTime($data['date_of_birth']) : null,
            gender: $data['gender'] ?? null,
            email_verified_at: isset($data['email_verified_at']) ? new DateTime($data['email_verified_at']) : null,
            password: $data['password'] ?? null,
            status: $data['status'] ?? 'active',
            last_login_at: isset($data['last_login_at']) ? new DateTime($data['last_login_at']) : null,
            last_login_ip: $data['last_login_ip'] ?? null,
            avatar: $data['avatar'] ?? null,
            meta: $data['meta'] ?? [],
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
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
                'gender' => $this->gender,
                'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
                'password' => $this->password,
                'status' => $this->status,
                'last_login_at' => $this->last_login_at?->format('Y-m-d H:i:s'),
                'last_login_ip' => $this->last_login_ip,
                'avatar' => $this->avatar,
                'meta' => $this->meta,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            fn ($value) => $value !== null,
        );
    }

    /**
     * Validate customer data
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

        if (empty(trim($this->email))) {
            $errors['email'] = 'Email is required';
        }

        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid email address';
        }

        if (! empty($this->phone) && ! preg_match('/^[\+]?[0-9\s\-\(\)]{8,20}$/', $this->phone)) {
            $errors['phone'] = 'Phone number format is invalid';
        }

        if (! empty($this->gender) && ! in_array($this->gender, ['male', 'female', 'other', 'prefer_not_to_say'])) {
            $errors['gender'] = 'Gender must be one of: male, female, other, prefer_not_to_say';
        }

        if ($this->date_of_birth && $this->date_of_birth > new DateTime) {
            $errors['date_of_birth'] = 'Date of birth cannot be in the future';
        }

        if ($this->password && strlen($this->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long';
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
     * Get display name (full name or email if no name)
     */
    public function getDisplayName(): string
    {
        $fullName = $this->getFullName();

        return ! empty($fullName) ? $fullName : $this->email;
    }

    /**
     * Check if customer is active
     */
    public function isEnabled(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if email is verified
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Get age
     */
    public function getAge(): ?int
    {
        if (! $this->date_of_birth) {
            return null;
        }

        return $this->date_of_birth->diff(new DateTime)->y;
    }

    /**
     * Check if has avatar
     */
    public function hasAvatar(): bool
    {
        return ! empty($this->avatar);
    }

    /**
     * Get avatar URL or default
     */
    public function getAvatarUrl(string $default = '/images/default-avatar.png'): string
    {
        return $this->avatar ?? $default;
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
