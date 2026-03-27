<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_variant_id',
        'sku',
        'name',
        'description',
        'quantity_ordered',
        'quantity_received',
        'quantity_cancelled',
        'unit_cost',
        'total_cost',
        'currency',
        'supplier_sku',
        'supplier_name',
        'status',
        'received_batches',
        'first_received_at',
        'fully_received_at',
        'notes',
        'quality_checks',
        'requires_inspection',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
        'quantity_cancelled' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'received_batches' => 'array',
        'quality_checks' => 'array',
        'requires_inspection' => 'boolean',
        'first_received_at' => 'datetime',
        'fully_received_at' => 'datetime',
    ];

    /**
     * Get the purchase order.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variant_id');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter items requiring inspection.
     */
    public function scopeRequiringInspection($query)
    {
        return $query->where('requires_inspection', true);
    }

    /**
     * Check if item is fully received.
     */
    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    /**
     * Check if item is partially received.
     */
    public function isPartiallyReceived(): bool
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_ordered;
    }

    /**
     * Get remaining quantity to receive.
     */
    public function getRemainingQuantity(): int
    {
        return max(0, ($this->quantity_ordered - $this->quantity_received) - $this->quantity_cancelled);
    }

    /**
     * Receive quantity.
     */
    public function receiveQuantity(int $quantity, array $qualityChecks = []): void
    {
        $remainingQuantity = $this->getRemainingQuantity();
        $actualQuantity = min($quantity, $remainingQuantity);

        if ($actualQuantity <= 0) {
            return;
        }

        $newReceivedQuantity = $this->quantity_received + $actualQuantity;

        $updateData = [
            'quantity_received' => $newReceivedQuantity,
        ];

        // Set first received timestamp
        if ($this->quantity_received === 0) {
            $updateData['first_received_at'] = now();
        }

        // Set fully received timestamp
        if ($newReceivedQuantity >= $this->quantity_ordered) {
            $updateData['fully_received_at'] = now();
            $updateData['status'] = 'received';
        } else {
            $updateData['status'] = 'partial';
        }

        // Add quality checks
        if (! empty($qualityChecks)) {
            $existingChecks = $this->quality_checks ?? [];
            $updateData['quality_checks'] = array_merge($existingChecks, [$qualityChecks]);
        }

        // Track received batches
        $existingBatches = $this->received_batches ?? [];
        $existingBatches[] = [
            'quantity' => $actualQuantity,
            'received_at' => now()->toDateTimeString(),
            'quality_checks' => $qualityChecks,
        ];
        $updateData['received_batches'] = $existingBatches;

        $this->update($updateData);

        // Update purchase order status if needed
        $this->updatePurchaseOrderStatus();
    }

    /**
     * Cancel quantity.
     */
    public function cancelQuantity(int $quantity): void
    {
        $availableQuantity = ($this->quantity_ordered - $this->quantity_received) - $this->quantity_cancelled;
        $actualQuantity = min($quantity, $availableQuantity);

        if ($actualQuantity <= 0) {
            return;
        }

        $newCancelledQuantity = $this->quantity_cancelled + $actualQuantity;

        $updateData = [
            'quantity_cancelled' => $newCancelledQuantity,
        ];

        // Update status
        if ($newCancelledQuantity >= $this->quantity_ordered) {
            $updateData['status'] = 'cancelled';
        } elseif ($this->quantity_received > 0) {
            $updateData['status'] = 'partial';
        }

        $this->update($updateData);

        // Update purchase order status if needed
        $this->updatePurchaseOrderStatus();
    }

    /**
     * Update purchase order status based on items.
     */
    private function updatePurchaseOrderStatus(): void
    {
        $purchaseOrder = $this->purchaseOrder;
        $items = $purchaseOrder->items;

        $totalItems = $items->count();
        $receivedItems = $items->where('status', 'received')->count();
        $partialItems = $items->where('status', 'partial')->count();
        $cancelledItems = $items->where('status', 'cancelled')->count();

        if ($receivedItems === $totalItems) {
            $purchaseOrder->markAsReceived();
        } elseif (
            ($receivedItems + $partialItems + $cancelledItems) === $totalItems &&
                ($receivedItems + $partialItems) > 0
        ) {
            $purchaseOrder->update(['status' => 'partial']);
        }
    }
}
