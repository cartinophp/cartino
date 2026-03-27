<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'site_id',
        'channel_id',
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
        'data' => 'array',
        'disjunctive' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected array $translatable = [
        'title',
        'description',
        'meta_title',
        'meta_description',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_products')
            ->withPivot('position', 'featured')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
