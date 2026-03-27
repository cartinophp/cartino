<?php

declare(strict_types=1);

namespace Cartino\DTO\Customer;

use Cartino\Models\Customer;

class CustomerFidelityData
{
    public function __construct(
        public int $id,
        public string $first_name,
        public string $last_name,
        public string $full_name,
        public string $email,
        public ?string $phone,
        public string $status,
        public ?string $created_at,
        public ?array $fidelity_card,
        public ?array $fidelity_tier,
        public int $fidelity_points,
        public ?string $fidelity_card_number,
        public ?string $fidelity_card_status,
        public int $orders_count = 0,
        public float $total_spent = 0,
    ) {}

    public static function fromModel(Customer $customer): self
    {
        $fidelityCard = null;
        $fidelityTier = null;
        $fidelityPoints = 0;
        $fidelityCardNumber = null;
        $fidelityCardStatus = null;

        if ($customer->fidelityCard) {
            $card = $customer->fidelityCard;
            $fidelityCard = [
                'id' => $card->id,
                'card_number' => $card->card_number,
                'total_points' => $card->total_points,
                'available_points' => $card->available_points,
                'total_earned' => $card->total_earned,
                'total_redeemed' => $card->total_redeemed,
                'total_spent_amount' => (float) $card->total_spent_amount,
                'is_active' => $card->is_active,
                'issued_at' => $card->issued_at?->toISOString(),
                'last_activity_at' => $card->last_activity_at?->toISOString(),
            ];

            $fidelityTier = $card->getCurrentTier();
            $fidelityPoints = $card->available_points;
            $fidelityCardNumber = $card->card_number;
            $fidelityCardStatus = $card->is_active ? 'active' : 'inactive';
        }

        return new self(
            id: $customer->id,
            first_name: $customer->first_name,
            last_name: $customer->last_name,
            full_name: $customer->full_name,
            email: $customer->email,
            phone: $customer->phone,
            status: $customer->status ?? 'active',
            created_at: $customer->created_at?->toISOString(),
            fidelity_card: $fidelityCard,
            fidelity_tier: $fidelityTier,
            fidelity_points: $fidelityPoints,
            fidelity_card_number: $fidelityCardNumber,
            fidelity_card_status: $fidelityCardStatus,
            orders_count: $customer->orders_count ?? 0,
            total_spent: (float) ($customer->orders_sum_total_amount ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'orders_count' => $this->orders_count,
            'total_spent' => $this->total_spent,
            'fidelity' => [
                'has_card' => ! is_null($this->fidelity_card),
                'card_number' => $this->fidelity_card_number,
                'status' => $this->fidelity_card_status,
                'points' => $this->fidelity_points,
                'tier' => $this->fidelity_tier,
                'card_details' => $this->fidelity_card,
            ],
        ];
    }
}
