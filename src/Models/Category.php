<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Support\HasHandle;
use Cartino\Support\HasSite;
use Cartino\Traits\HasAssets;
use Cartino\Traits\HasCustomFields;
use Cartino\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasAssets;
    use HasCustomFields;
    use HasFactory;
    use HasHandle;
    use HasSite;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'site_id',
        'title',
        'slug',
        'handle',
        'description',
        'body_html',
        'collection_type',
        'rules',
        'sort_order',
        'disjunctive',
        'meta_title',
        'meta_description',
        'seo',
        'status',
        'published_at',
        'published_scope',
        'template_suffix',
        'data',
    ];

    protected $casts = [
        'rules' => 'array',
        'seo' => 'array',
        'disjunctive' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected array $translatable = [
        'title',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected $appends = [
        'url',
        'image_url',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        /**
         * Asset collections configuration
         */
        $this->assetCollections = [
            'featured_image' => [
                'multiple' => false,
                'max_files' => 1,
                'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            ],
            'banner' => [
                'multiple' => false,
                'max_files' => 1,
                'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            ],
        ];
    }

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withPivot(['sort_order', 'is_primary'])
            ->withTimestamps()
            ->orderBy('category_product.sort_order');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeManual($query)
    {
        return $query->where('collection_type', 'manual');
    }

    public function scopeSmart($query)
    {
        return $query->where('collection_type', 'smart');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        return "/categories/{$this->handle}";
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image('square');
    }

    // Methods
    public function isPublished(): bool
    {
        return $this->status === 'published' && ($this->published_at === null || $this->published_at <= now());
    }

    public function isManual(): bool
    {
        return $this->collection_type === 'manual';
    }

    public function isSmart(): bool
    {
        return $this->collection_type === 'smart';
    }

    public function addProduct(Product $product, array $attributes = []): void
    {
        $this->products()->syncWithoutDetaching([
            $product->id => array_merge($attributes, [
                'position' => $this->products()->count() + 1,
            ]),
        ]);
    }

    public function removeProduct(Product $product): void
    {
        $this->products()->detach($product->id);
    }

    public function updateProductPosition(Product $product, int $position): void
    {
        $this->products()->updateExistingPivot($product->id, [
            'position' => $position,
        ]);
    }

    public function featuredProducts()
    {
        return $this->products()->wherePivot('featured', true);
    }

    /**
     * Apply smart collection rules to get products.
     */
    public function getSmartProducts()
    {
        if (! $this->isSmart() || empty($this->rules)) {
            return collect();
        }

        $query = Product::query()->published();

        foreach ($this->rules as $rule) {
            $field = $rule['field'] ?? null;
            $operator = $rule['operator'] ?? 'equals';
            $value = $rule['value'] ?? null;

            if (! $field || ! $value) {
                continue;
            }

            match ($operator) {
                'equals' => $query->where($field, $value),
                'not_equals' => $query->where($field, '!=', $value),
                'contains' => $query->where($field, 'like', "%{$value}%"),
                'not_contains' => $query->where($field, 'not like', "%{$value}%"),
                'starts_with' => $query->where($field, 'like', "{$value}%"),
                'ends_with' => $query->where($field, 'like', "%{$value}"),
                'greater_than' => $query->where($field, '>', $value),
                'less_than' => $query->where($field, '<', $value),
                'is_set' => $query->whereNotNull($field),
                'is_not_set' => $query->whereNull($field),
                default => null,
            };
        }

        return $query->get();
    }

    /**
     * Get all products (manual + smart).
     */
    public function getAllProducts()
    {
        if ($this->isManual()) {
            return $this->products;
        }

        return $this->getSmartProducts();
    }
}
