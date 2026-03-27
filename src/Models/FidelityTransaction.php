<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FidelityTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fidelity_card_id',
        'order_id',
        'type',
        'points',
        'description',
        'expires_at',
        'expired',
        'reference_transaction_id',
        'meta',
    ];

    protected $casts = [
        'points' => 'integer',
        'expires_at' => 'datetime',
        'expired' => 'boolean',
        'meta' => 'array',
    ];

    // Relationships
    public function fidelityCard(): BelongsTo
    {
        return $this->belongsTo(FidelityCard::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function referenceTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reference_transaction_id');
    }

    // Scopes
    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    public function scopeRedeemed($query)
    {
        return $query->where('type', 'redeemed');
    }

    public function scopeExpired($query)
    {
        return $query->where('type', 'expired');
    }

    public function scopeActive($query)
    {
        return $query
            ->where('expired', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpiring($query, int $days = 30)
    {
        return $query
            ->where('type', 'earned')
            ->where('expired', false)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    // Methods
    public function isExpired(): bool
    {
        return $this->expired || $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    public function getDaysUntilExpiration(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return max(0, $this->expires_at->diffInDays(now()));
    }
}
