<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string $currency
 * @property string|null $adjustment_type
 * @property string|null $adjustment_direction
 * @property float|null $adjustment_value
 * @property bool $auto_include_new_products
 * @property bool $is_default
 * @property string $status
 * @property Carbon|null $published_at
 * @property array|null $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Catalog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'currency',
        'adjustment_type',
        'adjustment_direction',
        'adjustment_value',
        'auto_include_new_products',
        'is_default',
        'status',
        'published_at',
        'data',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:4',
        'auto_include_new_products' => 'boolean',
        'is_default' => 'boolean',
        'published_at' => 'datetime',
        'data' => 'array',
    ];

    protected $attributes = [
        'currency' => 'USD',
        'status' => 'draft',
        'auto_include_new_products' => false,
        'is_default' => false,
    ];

    /**
     * Sites that use this catalog.
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_catalog')
            ->withPivot(['priority', 'is_default', 'is_active', 'starts_at', 'ends_at', 'settings'])
            ->withTimestamps();
    }

    /**
     * Products included in this catalog.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'catalog_product')
            ->withPivot(['priority', 'is_featured', 'position', 'settings'])
            ->withTimestamps();
    }

    /**
     * Product variants included in this catalog.
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'catalog_product_variant')
            ->withPivot(['is_active', 'position', 'settings'])
            ->withTimestamps();
    }

    /**
     * Customer groups that have access to this catalog.
     */
    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'catalog_customer_group')
            ->withPivot(['priority', 'is_default', 'settings'])
            ->withTimestamps();
    }

    /**
     * Scope: Active catalogs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Published catalogs.
     */
    public function scopePublished($query)
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope: Default catalog.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Filter by currency.
     */
    public function scopeForCurrency($query, string $currency)
    {
        return $query->where('currency', $currency);
    }

    /**
     * Check if catalog is published.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'active' && ($this->published_at === null || $this->published_at->isPast());
    }

    /**
     * Find catalog by slug.
     */
    public static function findBySlug(?string $slug): ?self
    {
        if (! $slug) {
            return static::default()->active()->first();
        }

        return static::where('slug', $slug)->active()->first();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
