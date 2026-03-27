<?php

namespace Cartino\Models;

use Cartino\Enums\CartStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'customer_id',
        'email',
        'status',
        'items',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'last_activity_at',
        'abandoned_at',
        'recovery_emails_sent',
        'last_recovery_email_sent_at',
        'recovered',
        'recovered_at',
        'converted_order_id',
        'shipping_address',
        'billing_address',
        'metadata',
    ];

    protected $casts = [
        'status' => CartStatus::class,
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'recovery_emails_sent' => 'integer',
        'recovered' => 'boolean',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'metadata' => 'array',
        'last_activity_at' => 'datetime',
        'abandoned_at' => 'datetime',
        'last_recovery_email_sent_at' => 'datetime',
        'recovered_at' => 'datetime',
    ];

    protected $dates = [
        'last_activity_at',
        'abandoned_at',
        'last_recovery_email_sent_at',
        'recovered_at',
    ];

    /**
     * Relationship with customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with converted order
     */
    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    /**
     * Check if cart is abandoned
     */
    public function isAbandoned(): bool
    {
        return $this->status === CartStatus::ABANDONED;
    }

    /**
     * Check if cart is active
     */
    public function isActive(): bool
    {
        return $this->status === CartStatus::ACTIVE;
    }

    /**
     * Check if cart is converted
     */
    public function isConverted(): bool
    {
        return $this->status === CartStatus::CONVERTED;
    }

    /**
     * Check if cart is expired
     */
    public function isExpired(): bool
    {
        return $this->status === CartStatus::EXPIRED;
    }

    /**
     * Mark cart as abandoned
     */
    public function markAsAbandoned(): void
    {
        $this->update([
            'status' => CartStatus::ABANDONED,
            'abandoned_at' => now(),
        ]);
    }

    /**
     * Mark cart as recovered
     */
    public function markAsRecovered(): void
    {
        $this->update([
            'recovered' => true,
            'recovered_at' => now(),
            'status' => CartStatus::ACTIVE,
        ]);
    }

    /**
     * Mark cart as converted
     */
    public function markAsConverted(int $orderId): void
    {
        $this->update([
            'status' => CartStatus::CONVERTED,
            'converted_order_id' => $orderId,
            'recovered' => true,
            'recovered_at' => now(),
        ]);
    }

    /**
     * Update last activity
     */
    public function updateActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Check if cart can be considered abandoned based on time
     */
    public function canBeAbandoned(int $hoursThreshold = 1): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $lastActivity = $this->last_activity_at ?? $this->updated_at;

        return $lastActivity->diffInHours(now()) >= $hoursThreshold;
    }

    /**
     * Get items count
     */
    protected function itemsCount(): Attribute
    {
        return Attribute::make(get: fn () => collect($this->items ?? [])->sum('quantity'));
    }

    /**
     * Get cart age in hours
     */
    protected function ageInHours(): Attribute
    {
        return Attribute::make(get: fn () => $this->created_at->diffInHours(now()));
    }

    /**
     * Check if eligible for recovery email
     */
    public function isEligibleForRecovery(): bool
    {
        return
            $this->isAbandoned() &&
            ! $this->recovered &&
            $this->recovery_emails_sent < 3 &&
            (
                $this->last_recovery_email_sent_at === null ||
                    $this->last_recovery_email_sent_at->diffInHours(now()) >= 24
            );
    }

    /**
     * Scope for abandoned carts
     */
    public function scopeAbandoned($query)
    {
        return $query->where('status', CartStatus::ABANDONED);
    }

    /**
     * Scope for active carts
     */
    public function scopeActive($query)
    {
        return $query->where('status', CartStatus::ACTIVE);
    }

    /**
     * Scope for carts that can be abandoned
     */
    public function scopeCanBeAbandoned($query, int $hoursThreshold = 1)
    {
        return $query
            ->where('status', CartStatus::ACTIVE)
            ->where(function ($q) use ($hoursThreshold) {
                $q->where('last_activity_at', '<=', now()->subHours($hoursThreshold))->orWhere(function ($subQ) use (
                    $hoursThreshold,
                ) {
                    $subQ->whereNull('last_activity_at')->where('updated_at', '<=', now()->subHours($hoursThreshold));
                });
            });
    }

    /**
     * Scope for eligible recovery carts
     */
    public function scopeEligibleForRecovery($query)
    {
        return $query
            ->abandoned()
            ->where('recovered', false)
            ->where('recovery_emails_sent', '<', 3)
            ->where(function ($q) {
                $q->whereNull('last_recovery_email_sent_at')->orWhere(
                    'last_recovery_email_sent_at',
                    '<=',
                    now()->subHours(24),
                );
            });
    }
}
