<?php

declare(strict_types=1);

namespace Cartino\DTO;

class SupplierDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $code = '',
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $contact_person = null,
        public ?string $contact_email = null,
        public ?string $contact_phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postal_code = null,
        public ?string $country_code = null,
        public ?string $website = null,
        public ?string $tax_number = null,
        public string $status = 'active',
        public string $priority = 'normal',
        public float $rating = 0.0,
        public bool $is_preferred = false,
        public bool $is_verified = false,
        public ?string $notes = null,
        public array $payment_terms = [],
        public array $shipping_terms = [],
        public ?int $lead_time_days = null,
        public ?float $minimum_order_value = null,
        public ?string $currency_code = null,
        public ?int $site_id = null,
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
            name: $data['name'] ?? '',
            code: $data['code'] ?? '',
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            contact_person: $data['contact_person'] ?? null,
            contact_email: $data['contact_email'] ?? null,
            contact_phone: $data['contact_phone'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postal_code: $data['postal_code'] ?? null,
            country_code: $data['country_code'] ?? null,
            website: $data['website'] ?? null,
            tax_number: $data['tax_number'] ?? null,
            status: $data['status'] ?? 'active',
            priority: $data['priority'] ?? 'normal',
            rating: (float) ($data['rating'] ?? 0.0),
            is_preferred: (bool) ($data['is_preferred'] ?? false),
            is_verified: (bool) ($data['is_verified'] ?? false),
            notes: $data['notes'] ?? null,
            payment_terms: $data['payment_terms'] ?? [],
            shipping_terms: $data['shipping_terms'] ?? [],
            lead_time_days: $data['lead_time_days'] ?? null,
            minimum_order_value: $data['minimum_order_value'] ? ((float) $data['minimum_order_value']) : null,
            currency_code: $data['currency_code'] ?? null,
            site_id: $data['site_id'] ?? null,
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
                'name' => $this->name,
                'code' => $this->code ?: $this->generateCode(),
                'email' => $this->email,
                'phone' => $this->phone,
                'contact_person' => $this->contact_person,
                'contact_email' => $this->contact_email,
                'contact_phone' => $this->contact_phone,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country_code' => $this->country_code,
                'website' => $this->website,
                'tax_number' => $this->tax_number,
                'status' => $this->status,
                'priority' => $this->priority,
                'rating' => $this->rating,
                'is_preferred' => $this->is_preferred,
                'is_verified' => $this->is_verified,
                'notes' => $this->notes,
                'payment_terms' => $this->payment_terms,
                'shipping_terms' => $this->shipping_terms,
                'lead_time_days' => $this->lead_time_days,
                'minimum_order_value' => $this->minimum_order_value,
                'currency_code' => $this->currency_code,
                'site_id' => $this->site_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            fn ($value) => $value !== null,
        );
    }

    /**
     * Get validation rules
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:shopper_suppliers,code,'.$this->id],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'priority' => ['required', 'in:low,normal,high,critical'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'is_preferred' => ['boolean'],
            'is_verified' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'payment_terms' => ['array'],
            'shipping_terms' => ['array'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'minimum_order_value' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'site_id' => ['nullable', 'integer', 'exists:shopper_sites,id'],
        ];
    }

    /**
     * Get validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del fornitore è obbligatorio.',
            'name.max' => 'Il nome del fornitore non può superare i 255 caratteri.',
            'code.unique' => 'Questo codice fornitore è già in uso.',
            'code.max' => 'Il codice fornitore non può superare i 50 caratteri.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'contact_email.email' => 'Inserisci un indirizzo email di contatto valido.',
            'website.url' => 'Inserisci un URL valido per il sito web.',
            'country_code.size' => 'Il codice paese deve essere di 2 caratteri.',
            'status.in' => 'Lo stato deve essere: attivo, inattivo o sospeso.',
            'priority.in' => 'La priorità deve essere: bassa, normale, alta o critica.',
            'rating.numeric' => 'La valutazione deve essere un numero.',
            'rating.min' => 'La valutazione minima è 0.',
            'rating.max' => 'La valutazione massima è 5.',
            'lead_time_days.integer' => 'I giorni di consegna devono essere un numero intero.',
            'lead_time_days.min' => 'I giorni di consegna non possono essere negativi.',
            'minimum_order_value.numeric' => 'Il valore minimo dell\'ordine deve essere un numero.',
            'minimum_order_value.min' => 'Il valore minimo dell\'ordine non può essere negativo.',
            'currency_code.size' => 'Il codice valuta deve essere di 3 caratteri.',
            'site_id.exists' => 'Il sito selezionato non esiste.',
        ];
    }

    /**
     * Generate supplier code
     */
    private function generateCode(): string
    {
        if (! $this->name) {
            return '';
        }

        $words = explode(' ', strtoupper(trim($this->name)));
        $code = '';

        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $code .= substr($word, 0, 3);
            }

            if (strlen($code) >= 6) {
                break;
            }
        }

        return substr($code.random_int(100, 999), 0, 9);
    }

    /**
     * Check if supplier is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if supplier is preferred
     */
    public function isPreferred(): bool
    {
        return $this->is_preferred;
    }

    /**
     * Check if supplier is verified
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Get display priority
     */
    public function getDisplayPriority(): string
    {
        return match ($this->priority) {
            'low' => 'Bassa',
            'normal' => 'Normale',
            'high' => 'Alta',
            'critical' => 'Critica',
            default => 'Normale',
        };
    }

    /**
     * Get display status
     */
    public function getDisplayStatus(): string
    {
        return match ($this->status) {
            'active' => 'Attivo',
            'inactive' => 'Inattivo',
            'suspended' => 'Sospeso',
            default => 'Inattivo',
        };
    }
}
