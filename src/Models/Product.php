<?php

declare(strict_types=1);

namespace Cartino\Models;

use Carbon\Carbon;
use Cartino\Support\HasHandle;
use Cartino\Support\HasSite;
use Cartino\Traits\HasAssets;
use Cartino\Traits\HasCustomFields;
use Cartino\Traits\HasOptimizedFilters;
use Cartino\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasAssets;
    use HasCustomFields;
    use HasFactory;
    use HasHandle;
    use HasOptimizedFilters;
    use HasSite;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'track_quantity',
        'allow_out_of_stock_purchases',
        'stock_status',
        'weight',
        'dimensions',
        'is_physical',
        'is_digital',
        'requires_shipping',
        'is_featured',
        'status',
        'brand_id',
        'product_type_id',
        'seo',
        'meta',
        'published_at',
        'average_rating',
        'review_count',
        'data',
        // Product enhancements
        'min_order_quantity',
        'order_increment',
        'is_closeout',
        'restock_days',
        'condition',
        'hs_code',
        'country_of_origin',
        'visibility',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_quantity' => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'is_physical' => 'boolean',
        'is_digital' => 'boolean',
        'requires_shipping' => 'boolean',
        'is_featured' => 'boolean',
        'seo' => 'array',
        'meta' => 'array',
        'published_at' => 'datetime',
        'average_rating' => 'decimal:2',
        'review_count' => 'integer',
        // Product enhancements
        'min_order_quantity' => 'integer',
        'order_increment' => 'integer',
        'is_closeout' => 'boolean',
        'restock_days' => 'integer',
    ];

    /**
     * Translatable fields for multi-language support
     */
    protected array $translatable = [
        'name',
        'slug',
        'description',
        'short_description',
    ];

    /**
     * Fields that should always be eager loaded (N+1 protection)
     */
    protected static array $defaultEagerLoad = [
        'brand:id,name,slug',
        'productType:id,name',
    ];

    /**
     * Fields that can be filtered
     */
    protected static array $filterable = [
        'id',
        'name',
        'slug',
        'sku',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'track_quantity',
        'stock_status',
        'weight',
        'is_physical',
        'is_digital',
        'requires_shipping',
        'is_featured',
        'status',
        'brand_id',
        'product_type_id',
        'published_at',
        'created_at',
        'updated_at',
        'average_rating',
        'review_count',
    ];

    /**
     * Fields that can be sorted
     */
    protected static array $sortable = [
        'id',
        'name',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'weight',
        'is_featured',
        'status',
        'published_at',
        'created_at',
        'updated_at',
        'average_rating',
        'review_count',
    ];

    /**
     * Fields that can be searched
     */
    protected static array $searchable = [
        'name',
        'description',
        'short_description',
        'sku',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        /**
         * Asset collections configuration
         */
        $this->assetCollections = [
            'images' => [
                'multiple' => true,
                'max_files' => 10,
                'mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            ],
            'gallery' => [
                'multiple' => true,
                'max_files' => 50,
                'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            ],
            'documents' => [
                'multiple' => true,
                'max_files' => 10,
                'mime_types' => ['application/pdf', 'application/msword'],
            ],
            'videos' => [
                'multiple' => true,
                'max_files' => 5,
                'mime_types' => ['video/mp4', 'video/webm'],
            ],
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(ProductOption::class, 'product_product_option')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function cartLines(): HasMany
    {
        return $this->hasMany(CartLine::class);
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    public function isFavoritedBy(Customer $customer): bool
    {
        return $this->favorites()->where('customer_id', $customer->id)->exists();
    }

    /**
     * Alias for assets relationship for backward compatibility
     */
    public function media(): MorphToMany
    {
        return $this->assets();
    }

    // ========================================
    // Product Bundles
    // ========================================

    /**
     * Products that are bundled within this product.
     * Example: "Gaming PC Bundle" contains "Gaming Mouse", "Keyboard", etc.
     */
    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_bundles', 'product_id', 'bundled_product_id')
            ->withPivot(['quantity', 'discount_percent', 'is_optional', 'sort_order'])
            ->withTimestamps()
            ->orderBy('product_bundles.sort_order');
    }

    /**
     * Products where this product is included as a bundled item.
     * Example: If "Gaming Mouse" is part of "Gaming PC Bundle", this returns the bundle.
     */
    public function bundledIn(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_bundles', 'bundled_product_id', 'product_id')
            ->withPivot(['quantity', 'discount_percent', 'is_optional', 'sort_order'])
            ->withTimestamps();
    }

    // ========================================
    // Product Relations
    // ========================================

    /**
     * All product relations (generic method).
     */
    protected function relations(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'related_product_id')
            ->withPivot(['type', 'sort_order'])
            ->withTimestamps()
            ->orderBy('product_relations.sort_order');
    }

    /**
     * Upsell products (higher-priced alternatives).
     * Example: "Gaming Laptop Pro" for "Gaming Laptop"
     */
    public function upsells(): BelongsToMany
    {
        return $this->relations()->wherePivot('type', 'upsell');
    }

    /**
     * Cross-sell products (complementary items).
     * Example: "Laptop Bag", "Gaming Mouse" for "Gaming Laptop"
     */
    public function crossSells(): BelongsToMany
    {
        return $this->relations()->wherePivot('type', 'cross_sell');
    }

    /**
     * Related products (similar items).
     * Example: Other gaming laptops for "Gaming Laptop"
     */
    public function relatedProducts(): BelongsToMany
    {
        return $this->relations()->wherePivot('type', 'related');
    }

    /**
     * Frequently bought together products.
     * Example: "Laptop Stand", "USB Hub" for "Gaming Laptop"
     */
    public function frequentlyBoughtTogether(): BelongsToMany
    {
        return $this->relations()->wherePivot('type', 'frequently_bought_together');
    }

    // ========================================
    // Inventory & Stock Methods
    // ========================================

    /**
     * Check if product can be sold when out of stock.
     * Based on inventory_policy field (on variants).
     */
    public function canSellWhenOutOfStock(): bool
    {
        // For products without variants, check allow_out_of_stock_purchases
        if ($this->variants()->count() === 0) {
            return (bool) $this->allow_out_of_stock_purchases;
        }

        // For products with variants, check if any variant allows out of stock purchases
        return $this->variants()->where('inventory_policy', 'continue')->exists();
    }

    /**
     * Check if product is currently in stock.
     */
    public function isInStock(): bool
    {
        // For products without variants
        if ($this->variants()->count() === 0) {
            if (! $this->track_quantity) {
                return true;
            }

            return $this->stock_quantity > 0 || $this->canSellWhenOutOfStock();
        }

        // For products with variants, check if any variant is in stock
        return $this->variants()
            ->where(function ($query) {
                $query->where('track_quantity', false)->orWhere('inventory_quantity', '>', 0)->orWhere(
                    'inventory_policy',
                    'continue',
                );
            })
            ->exists();
    }

    /**
     * Check if product needs restocking.
     */
    public function needsRestock(): bool
    {
        // Don't restock closeout items
        if ($this->is_closeout) {
            return false;
        }

        // For products without variants
        if ($this->variants()->count() === 0) {
            if (! $this->track_quantity) {
                return false;
            }

            $lowStockThreshold = $this->low_stock_threshold ?? 5;

            return $this->stock_quantity <= $lowStockThreshold;
        }

        // For products with variants, check if any variant needs restock
        return $this->variants()
            ->where('track_quantity', true)
            ->whereRaw('inventory_quantity <= ?', [5])
            ->exists();
    }

    /**
     * Get estimated restock date based on restock_days.
     */
    public function estimatedRestockDate(): ?Carbon
    {
        if (! $this->needsRestock() || ! $this->restock_days) {
            return null;
        }

        return now()->addDays($this->restock_days);
    }

    /**
     * Check if quantity is valid for ordering (respects min_order_quantity and order_increment).
     */
    public function isValidOrderQuantity(int $quantity): bool
    {
        $minQty = $this->min_order_quantity ?? 1;
        $increment = $this->order_increment ?? 1;

        // Check minimum quantity
        if ($quantity < $minQty) {
            return false;
        }

        // Check increment (quantity must be a multiple of increment)
        if ((($quantity - $minQty) % $increment) !== 0) {
            return false;
        }

        return true;
    }
}
