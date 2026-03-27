<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'collection_id',
        'slug',
        'parent_id',
        'order',
        'status',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'order' => 'integer',
    ];

    /**
     * Translatable fields stored in the polymorphic translations table.
     */
    protected array $translatable = [
        'title',
        'description',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'author_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Entry::class, 'parent_id')->orderBy('order');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')->where('published_at', '>', now());
    }

    public function isPublished(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    public function url(): string
    {
        return '/'.$this->collection->slug.'/'.$this->slug;
    }
}
