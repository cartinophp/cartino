<?php

namespace Cartino\Models;

use Cartino\Enums\StockNotificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StockNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'phone',
        'customer_id',
        'product_type',
        'product_id',
        'product_handle',
        'variant_data',
        'requested_quantity',
        'is_notified',
        'notified_at',
        'is_active',
        'notification_token',
        'metadata',
    ];

    protected $casts = [
        'status' => StockNotificationStatus::class,
        'variant_data' => 'array',
        'requested_quantity' => 'integer',
        'is_notified' => 'boolean',
        'is_active' => 'boolean',
        'notified_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Boot method to generate notification token
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            if (empty($notification->notification_token)) {
                $notification->notification_token = Str::random(32);
            }
        });
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotNotified($query)
    {
        return $query->where('is_notified', false);
    }

    public function scopePending($query)
    {
        return $query->active()->notNotified();
    }

    public function scopeForProduct($query, string $productType, int $productId)
    {
        return $query->where('product_type', $productType)->where('product_id', $productId);
    }

    // Methods
    public function markAsNotified(): bool
    {
        return $this->update([
            'is_notified' => true,
            'notified_at' => now(),
        ]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function getUnsubscribeUrl(): string
    {
        return route('stock-notifications.unsubscribe', $this->notification_token);
    }

    // Static methods
    public static function createForProduct(
        string $email,
        string $productType,
        int $productId,
        ?string $productHandle = null,
        ?int $customerId = null,
        ?string $phone = null,
        array $variantData = [],
        int $quantity = 1,
    ): self {
        return static::create([
            'email' => $email,
            'phone' => $phone,
            'customer_id' => $customerId,
            'product_type' => $productType,
            'product_id' => $productId,
            'product_handle' => $productHandle,
            'variant_data' => $variantData,
            'requested_quantity' => $quantity,
        ]);
    }

    public static function notifyForProduct(
        string $productType,
        int $productId,
        ?int $availableQuantity = null,
    ): int {
        $notifications = static::pending()
            ->forProduct($productType, $productId)
            ->when($availableQuantity, function ($query) use ($availableQuantity) {
                return $query->where('requested_quantity', '<=', $availableQuantity);
            })
            ->get();

        $notifiedCount = 0;

        foreach ($notifications as $notification) {
            // Send notification email/SMS
            try {
                // Here you would send the actual notification
                // Mail::to($notification->email)->send(new StockAvailableNotification($notification));

                $notification->markAsNotified();
                $notifiedCount++;
            } catch (\Exception $e) {
                // Log error but continue with other notifications
                Log::error('Failed to send stock notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notifiedCount;
    }
}
