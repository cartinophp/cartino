<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\HasCustomFields;
use Cartino\Traits\HasOptimizedFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasCustomFields;
    use HasFactory;
    use HasOptimizedFilters;
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'customer_id',
        'product_id',
        'product_variant_id',
        'subscription_number',
        'status',
        'billing_interval',
        'billing_interval_count',
        'price',
        'currency_id',
        'started_at',
        'trial_end_at',
        'current_period_start',
        'current_period_end',
        'next_billing_date',
        'paused_at',
        'cancelled_at',
        'ended_at',
        'payment_method',
        'payment_details',
        'billing_cycle_count',
        'total_billed',
        'cancel_reason',
        'cancel_comment',
        'cancel_at_period_end',
        'pause_reason',
        'pause_resumes_at',
        'metadata',
        'notes',
        'data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total_billed' => 'decimal:2',
        'billing_interval_count' => 'integer',
        'billing_cycle_count' => 'integer',
        'cancel_at_period_end' => 'boolean',
        'payment_details' => 'array',
        'metadata' => 'array',
        'data' => 'array',
        'started_at' => 'datetime',
        'trial_end_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'next_billing_date' => 'datetime',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'ended_at' => 'datetime',
        'pause_resumes_at' => 'datetime',
    ];

    protected $appends = [
        'is_active',
        'is_trial',
        'is_paused',
        'is_cancelled',
        'billing_frequency',
    ];

    // Relationships

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Accessors

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsTrialAttribute(): bool
    {
        return $this->trial_end_at && $this->trial_end_at->isFuture();
    }

    public function getIsPausedAttribute(): bool
    {
        return $this->status === 'paused';
    }

    public function getIsCancelledAttribute(): bool
    {
        return in_array($this->status, ['cancelled', 'expired']);
    }

    public function getBillingFrequencyAttribute(): string
    {
        $count = $this->billing_interval_count > 1 ? "every {$this->billing_interval_count} " : '';
        $interval = $this->billing_interval;

        if ($this->billing_interval_count > 1) {
            $interval .= 's';
        }

        return $count.$interval;
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCancelled($query)
    {
        return $query->whereIn('status', ['cancelled', 'expired']);
    }

    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')->whereNotNull('next_billing_date')->where(
            'next_billing_date',
            '<=',
            now(),
        );
    }

    public function scopeTrialEnding($query, int $days = 3)
    {
        return $query->where('status', 'active')->whereNotNull('trial_end_at')->whereBetween('trial_end_at', [
            now(),
            now()->addDays($days),
        ]);
    }

    // Methods

    public function pause(?string $reason = null, ?\DateTime $resumesAt = null): bool
    {
        $this->update([
            'status' => 'paused',
            'paused_at' => now(),
            'pause_reason' => $reason,
            'pause_resumes_at' => $resumesAt,
        ]);

        return true;
    }

    public function resume(): bool
    {
        $this->update([
            'status' => 'active',
            'paused_at' => null,
            'pause_reason' => null,
            'pause_resumes_at' => null,
        ]);

        return true;
    }

    public function cancel(?string $reason = null, ?string $comment = null, bool $immediately = false): bool
    {
        $data = [
            'cancel_reason' => $reason,
            'cancel_comment' => $comment,
            'cancelled_at' => now(),
        ];

        if ($immediately) {
            $data['status'] = 'cancelled';
            $data['ended_at'] = now();
        } else {
            $data['cancel_at_period_end'] = true;
        }

        $this->update($data);

        return true;
    }

    public function calculateNextBillingDate(): ?\DateTime
    {
        if (! $this->current_period_end) {
            return null;
        }

        return match ($this->billing_interval) {
            'day' => $this->current_period_end->copy()->addDays($this->billing_interval_count),
            'week' => $this->current_period_end->copy()->addWeeks($this->billing_interval_count),
            'month' => $this->current_period_end->copy()->addMonths($this->billing_interval_count),
            'year' => $this->current_period_end->copy()->addYears($this->billing_interval_count),
            default => null,
        };
    }
}
