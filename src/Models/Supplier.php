<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'site_id',
        'name',
        'code',
        'slug',
        'description',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'fax',
        'website',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'tax_number',
        'company_registration',
        'credit_limit',
        'payment_terms_days',
        'currency',
        'status',
        'is_preferred',
        'priority',
        'minimum_order_amount',
        'lead_time_days',
        'notes',
        'metadata',
        'certifications',
        'rating',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'credit_limit' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'rating' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'lead_time_days' => 'integer',
        'priority' => 'integer',
        'is_preferred' => 'boolean',
        'metadata' => 'array',
        'certifications' => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): SupplierFactory
    {
        return SupplierFactory::new();
    }

    /**
     * Get the site that owns the supplier.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the product-supplier relationships.
     */
    public function productSuppliers(): HasMany
    {
        return $this->hasMany(ProductSupplier::class);
    }

    /**
     * Get the purchase orders for this supplier.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get products supplied by this supplier.
     */
    public function products()
    {
        return $this->hasManyThrough(Product::class, ProductSupplier::class, 'supplier_id', 'id', 'id', 'product_id');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by preferred suppliers.
     */
    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    /**
     * Scope to filter by country.
     */
    public function scopeFromCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Scope to filter by priority level.
     */
    public function scopeWithPriority($query, int $minPriority = 0)
    {
        return $query->where('priority', '>=', $minPriority);
    }

    /**
     * Get the supplier's full address.
     */
    public function getFullAddressAttribute(): string
    {
        $address = collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
        ])
            ->filter()
            ->implode(', ');

        if ($this->country_code) {
            $address .= ', '.$this->country_code;
        }

        return $address;
    }

    /**
     * Get the supplier's contact information.
     */
    public function getContactInfoAttribute(): array
    {
        return [
            'person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'fax' => $this->fax,
            'website' => $this->website,
        ];
    }

    /**
     * Check if supplier is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if supplier is preferred.
     */
    public function isPreferred(): bool
    {
        return $this->is_preferred;
    }

    /**
     * Get the supplier's performance metrics.
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'total_orders' => $this->purchaseOrders()->count(),
            'completed_orders' => $this->purchaseOrders()->where('status', 'completed')->count(),
            'total_products' => $this->productSuppliers()->count(),
            'average_lead_time' => $this->productSuppliers()->avg('lead_time_days'),
            'rating' => $this->rating,
            'on_time_delivery_rate' => $this->calculateOnTimeDeliveryRate(),
        ];
    }

    /**
     * Calculate on-time delivery rate.
     */
    private function calculateOnTimeDeliveryRate(): float
    {
        $completedOrders = $this->purchaseOrders()
            ->where('status', 'completed')
            ->whereNotNull('expected_delivery_date')
            ->whereNotNull('received_at')
            ->get();

        if ($completedOrders->isEmpty()) {
            return 0.0;
        }

        $onTimeOrders = $completedOrders->filter(function ($order) {
            return $order->received_at <= $order->expected_delivery_date;
        });

        return round(($onTimeOrders->count() / $completedOrders->count()) * 100, 2);
    }

    /**
     * Update supplier rating based on performance.
     */
    public function updateRating(): void
    {
        $metrics = $this->getPerformanceMetrics();

        // Simple rating calculation based on various factors
        $rating = 0;

        // On-time delivery (40% weight)
        $rating += ($metrics['on_time_delivery_rate'] / 100) * 4 * 0.4;

        // Order completion rate (30% weight)
        if ($metrics['total_orders'] > 0) {
            $completionRate = $metrics['completed_orders'] / $metrics['total_orders'];
            $rating += $completionRate * 5 * 0.3;
        }

        // Base rating for active suppliers (30% weight)
        if ($this->isActive()) {
            $rating += 5 * 0.3;
        }

        $this->update(['rating' => round($rating, 2)]);
    }
}
