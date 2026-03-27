<?php

declare(strict_types=1);

namespace Cartino\Http\Resources\OrderApproval;

use Cartino\Http\Resources\BaseResource;
use Cartino\Http\Resources\Company\CompanyResource;
use Cartino\Http\Resources\Order\OrderResource;
use Cartino\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

class OrderApprovalResource extends BaseResource
{
    /**
     * Transform the resource data.
     */
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,

            // Relationships
            'order_id' => $this->order_id,
            'order' => $this->whenIncluded('order', function () {
                return new OrderResource($this->whenLoaded('order'));
            }),

            'company_id' => $this->company_id,
            'company' => $this->whenIncluded('company', function () {
                return new CompanyResource($this->whenLoaded('company'));
            }),

            'requested_by_id' => $this->requested_by_id,
            'requested_by' => $this->whenIncluded('requestedBy', function () {
                return new UserResource($this->whenLoaded('requestedBy'));
            }),

            'approver_id' => $this->approver_id,
            'approver' => $this->whenIncluded('approver', function () {
                return new UserResource($this->whenLoaded('approver'));
            }),

            // Status
            'status' => $this->status,

            // Order Details (snapshot at approval time)
            'order_total' => $this->formatMoney($this->order_total),
            'approval_threshold' => $this->formatMoney($this->approval_threshold),
            'threshold_exceeded' => $this->threshold_exceeded,
            'amount_over_threshold' => $this->threshold_exceeded
                ? $this->formatMoney($this->order_total - $this->approval_threshold)
                : null,

            // Decision Details
            'approval_reason' => $this->approval_reason,
            'rejection_reason' => $this->rejection_reason,
            'notes' => $this->notes,

            // Timestamps
            'approved_at' => $this->formatTimestamp($this->approved_at),
            'rejected_at' => $this->formatTimestamp($this->rejected_at),
            'expires_at' => $this->formatTimestamp($this->expires_at),
            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),

            // Time Calculations
            'time_to_decision' => $this->getTimeToDecision(),
            'days_until_expiration' => $this->getDaysUntilExpiration(),
            'is_expired' => $this->isExpired(),

            // Additional Data
            'data' => $this->when($this->data, $this->data),
        ];
    }

    /**
     * Get additional meta for order approval.
     */
    protected function getMeta(Request $request): array
    {
        return array_merge(parent::getMeta($request), [
            'is_pending' => $this->status === 'pending',
            'is_approved' => $this->status === 'approved',
            'is_rejected' => $this->status === 'rejected',
            'is_expired' => $this->isExpired(),
            'can_be_approved' => $this->status === 'pending' && ! $this->isExpired(),
            'requires_urgent_action' => $this->status === 'pending' && $this->getDaysUntilExpiration() <= 1,
        ]);
    }

    /**
     * Get time from request to decision in hours
     */
    protected function getTimeToDecision(): ?float
    {
        if ($this->status === 'pending') {
            return null;
        }

        $decisionTime = $this->approved_at ?? $this->rejected_at;
        if (! $decisionTime) {
            return null;
        }

        return round($this->created_at->diffInHours($decisionTime), 2);
    }

    /**
     * Get days until expiration
     */
    protected function getDaysUntilExpiration(): ?int
    {
        if (! $this->expires_at || $this->status !== 'pending') {
            return null;
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }

    /**
     * Check if approval is expired
     */
    protected function isExpired(): bool
    {
        return $this->status === 'pending'
            && $this->expires_at
            && now()->isAfter($this->expires_at);
    }
}
