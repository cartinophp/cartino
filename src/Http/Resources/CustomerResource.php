<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'preferences' => $this->preferences,
            'meta' => $this->meta,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            // Relationships
            'customer_group' => $this->whenLoaded('customerGroup', function () {
                return [
                    'id' => $this->customerGroup->id,
                    'name' => $this->customerGroup->name,
                    'slug' => $this->customerGroup->slug,
                    'description' => $this->customerGroup->description,
                ];
            }),
            'addresses' => $this->whenLoaded('addresses', function () {
                return $this->addresses->map(function ($address) {
                    return [
                        'id' => $address->id,
                        'type' => $address->type,
                        'first_name' => $address->first_name,
                        'last_name' => $address->last_name,
                        'company' => $address->company,
                        'address_line_1' => $address->address_line_1,
                        'address_line_2' => $address->address_line_2,
                        'city' => $address->city,
                        'state' => $address->state,
                        'postal_code' => $address->postal_code,
                        'country_id' => $address->country_id,
                        'country' => $address->country
                            ? [
                                'id' => $address->country->id,
                                'name' => $address->country->name,
                                'code' => $address->country->code,
                            ] : null,
                        'phone' => $address->phone,
                        'is_default' => $address->is_default,
                    ];
                });
            }),
            'fidelity_card' => $this->whenLoaded('fidelityCard', function () {
                return $this->fidelityCard
                    ? [
                        'id' => $this->fidelityCard->id,
                        'card_number' => $this->fidelityCard->card_number,
                        'points_balance' => $this->fidelityCard->points_balance,
                        'total_earned_points' => $this->fidelityCard->total_earned_points,
                        'total_redeemed_points' => $this->fidelityCard->total_redeemed_points,
                        'status' => $this->fidelityCard->status,
                        'issued_at' => $this->fidelityCard->issued_at?->toISOString(),
                        'expires_at' => $this->fidelityCard->expires_at?->toISOString(),
                    ] : null;
            }),
            // Computed values
            'orders_count' => $this->when(isset($this->orders_count), $this->orders_count),
            'orders_sum_total_amount' => $this->when(
                isset($this->orders_sum_total_amount),
                $this->orders_sum_total_amount,
            ),
            // Admin-only fields
            'internal_notes' => $this->when(
                $request->user()?->can('view-customer-internal-notes'),
                $this->internal_notes,
            ),
            // Actions
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'restore' => $request->user()?->can('restore', $this->resource) ?? false,
                'force_delete' => $request->user()?->can('forceDelete', $this->resource) ?? false,
            ],
        ];
    }
}
