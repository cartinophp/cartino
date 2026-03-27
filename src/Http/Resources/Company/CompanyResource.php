<?php

declare(strict_types=1);

namespace Cartino\Http\Resources\Company;

use Cartino\Http\Resources\BaseResource;
use Cartino\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

class CompanyResource extends BaseResource
{
    /**
     * Transform the resource data.
     */
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_number' => $this->company_number,
            'name' => $this->name,
            'handle' => $this->handle,
            'legal_name' => $this->legal_name,

            // Tax Information
            'vat_number' => $this->vat_number,
            'tax_id' => $this->tax_id,
            'tax_exempt' => $this->tax_exempt,
            'tax_exemptions' => $this->when($this->tax_exempt, $this->tax_exemptions),

            // Contact Information
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,

            // Company Classification
            'type' => $this->type,
            'status' => $this->status,
            'risk_level' => $this->risk_level,

            // Financial Information
            'credit_limit' => $this->formatMoney($this->credit_limit),
            'outstanding_balance' => $this->formatMoney($this->outstanding_balance),
            'available_credit' => $this->formatMoney($this->credit_limit - $this->outstanding_balance),
            'lifetime_value' => $this->formatMoney($this->lifetime_value),

            // Payment Terms
            'payment_terms_days' => $this->payment_terms_days,
            'payment_method' => $this->payment_method,

            // Approval Settings
            'approval_threshold' => $this->formatMoney($this->approval_threshold),
            'requires_approval' => $this->requires_approval,

            // Addresses
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,

            // Hierarchy
            'parent_company_id' => $this->parent_company_id,
            'parent_company' => $this->whenIncluded('parentCompany', function () {
                return new self($this->whenLoaded('parentCompany'));
            }),
            'subsidiaries' => $this->whenIncluded('subsidiaries', function () {
                return self::collection($this->whenLoaded('subsidiaries'));
            }),

            // Relationships
            'users' => $this->whenIncluded('users', function () {
                return UserResource::collection($this->whenLoaded('users'));
            }),
            'managers' => $this->whenIncluded('managers', function () {
                return UserResource::collection($this->getManagers());
            }),

            // Statistics
            'order_count' => $this->order_count,
            'last_order_at' => $this->formatTimestamp($this->last_order_at),

            // Additional Data
            'notes' => $this->notes,
            'settings' => $this->when($this->settings, $this->settings),
            'data' => $this->when($this->data, $this->data),

            // Timestamps
            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),
        ];
    }

    /**
     * Get additional meta for company.
     */
    protected function getMeta(Request $request): array
    {
        return array_merge(parent::getMeta($request), [
            'is_active' => $this->status === 'active',
            'is_suspended' => $this->status === 'suspended',
            'is_high_risk' => $this->risk_level === 'high',
            'credit_limit_exceeded' => $this->outstanding_balance > $this->credit_limit,
            'credit_utilization' => $this->credit_limit > 0
                ? round(($this->outstanding_balance / $this->credit_limit) * 100, 2)
                : 0,
            'has_parent' => ! is_null($this->parent_company_id),
            'has_subsidiaries' => $this->whenLoaded('subsidiaries', fn () => $this->subsidiaries->count() > 0),
            'users_count' => $this->whenLoaded('users', fn () => $this->users->count()),
            'orders_count' => $this->order_count,
        ]);
    }
}
