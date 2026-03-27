<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\HasCustomFields;
use Cartino\Traits\HasOptimizedFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Courier extends Model implements HasMedia
{
    use HasCustomFields;
    use HasFactory;
    use HasOptimizedFilters;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'website',
        'tracking_url',
        'logo',
        'delivery_time_min',
        'delivery_time_max',
        'status',
        'is_enabled',
        'seo',
        'meta',
        'data',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'delivery_time_min' => 'integer',
        'delivery_time_max' => 'integer',
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
        'code',
        'status',
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
        'code',
        'status',
        'is_enabled',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that can be searched
     */
    protected static array $searchable = [
        'name',
        'code',
        'description',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->width(800)
            ->height(600)
            ->sharpen(10);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Get delivery time range as string
     */
    public function getDeliveryTimeAttribute(): ?string
    {
        if ($this->delivery_time_min && $this->delivery_time_max) {
            return "{$this->delivery_time_min}-{$this->delivery_time_max} giorni";
        }

        if ($this->delivery_time_min) {
            return "{$this->delivery_time_min}+ giorni";
        }

        if ($this->delivery_time_max) {
            return "fino a {$this->delivery_time_max} giorni";
        }

        return null;
    }
}
