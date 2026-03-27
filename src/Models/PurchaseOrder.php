<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'site_id',
        'supplier_id',
        'reference',
        'supplier_reference',
        'status',
        'order_date',
        'expected_delivery_date',
        'requested_delivery_date',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'discount_amount',
        'total_amount',
        'currency',
        'shipping_method',
        'tracking_number',
        'shipping_address',
        'notes',
        'terms_and_conditions',
        'metadata',
        'sent_at',
        'confirmed_at',
        'shipped_at',
        'received_at',
        'cancelled_at',
        'created_by',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'requested_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the site.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who created the order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the order.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the purchase order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('order_date', [$from, $to]);
    }

    /**
     * Check if order is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if order is sent.
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if order is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Mark order as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark order as confirmed.
     */
    public function markAsConfirmed(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Mark order as shipped.
     */
    public function markAsShipped(?string $trackingNumber = null): void
    {
        $data = [
            'status' => 'shipped',
            'shipped_at' => now(),
        ];

        if ($trackingNumber) {
            $data['tracking_number'] = $trackingNumber;
        }

        $this->update($data);
    }

    /**
     * Mark order as received.
     */
    public function markAsReceived(): void
    {
        $this->update([
            'status' => 'completed',
            'received_at' => now(),
        ]);
    }

    /**
     * Mark order as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Calculate total amount from items.
     */
    public function calculateTotal(): void
    {
        $itemsTotal = $this->items()->sum('total_cost');

        $this->update([
            'subtotal' => $itemsTotal,
            'total_amount' => ($itemsTotal + $this->tax_amount + $this->shipping_cost) - $this->discount_amount,
        ]);
    }

    /**
     * Get order progress percentage.
     */
    public function getProgressPercentage(): int
    {
        return match ($this->status) {
            'draft' => 10,
            'sent' => 25,
            'confirmed' => 50,
            'partial' => 75,
            'completed' => 100,
            'cancelled' => 0,
            default => 0,
        };
    }

    /**
     * Generate next reference number.
     */
    public static function generateReference(): string
    {
        $year = date('Y');
        $lastOrder = static::where('reference', 'like', "PO-{$year}-%")->orderBy('reference', 'desc')->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->reference, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('PO-%s-%03d', $year, $nextNumber);
    }
}
