<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\HasAssets;
use Cartino\Traits\HasCustomFields;
use Cartino\Traits\HasOptimizedFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasAssets;
    use HasCustomFields;
    use HasFactory;
    use HasOptimizedFilters;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'is_enabled',
        'seo',
        'meta',
        'data',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'seo' => 'array',
        'meta' => 'array',
    ];

    /**
     * Fields that should always be eager loaded (N+1 protection)
     */
    protected static array $defaultEagerLoad = [];

    /**
     * Fields that can be filtered
     */
    protected static array $filterable = [
        'id',
        'name',
        'slug',
        'is_enabled',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that can be sorted
     */
    protected static array $sortable = [
        'id',
        'name',
        'slug',
        'is_enabled',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that can be searched
     */
    protected static array $searchable = [
        'name',
        'description',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        /**
         * Asset collections configuration
         */
        $this->assetCollections = [
            'logo' => [
                'multiple' => false,
                'max_files' => 1,
                'mime_types' => ['image/svg+xml', 'image/png', 'image/jpeg', 'image/webp'],
            ],
            'banner' => [
                'multiple' => false,
                'max_files' => 1,
                'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            ],
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
