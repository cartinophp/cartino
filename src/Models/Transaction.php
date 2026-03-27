<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'order_id',
        'customer_id',
        'type',
        'status',
        'gateway',
        'gateway_reference',
        'amount',
        'currency_code',
        'gateway_data',
        'metadata',
        'failure_reason',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_data' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = 'TXN-'.strtoupper(Str::random(10));
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }

    public function isPayment(): bool
    {
        return $this->type === 'payment';
    }

    public function markAsCompleted(array $gatewayData = []): bool
    {
        return $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'gateway_data' => array_merge($this->gateway_data ?? [], $gatewayData),
        ]);
    }

    public function markAsFailed(string $reason, array $gatewayData = []): bool
    {
        return $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'processed_at' => now(),
            'gateway_data' => array_merge($this->gateway_data ?? [], $gatewayData),
        ]);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }
}
