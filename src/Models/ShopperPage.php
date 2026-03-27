<?php

namespace Cartino\Models;

use App\Models\User;
use Cartino\Support\HasHandle;
use Cartino\Support\HasSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopperPage extends Model
{
    use HasHandle, HasSite;

    protected $table = 'shopper_pages';

    protected $fillable = [
        'site_id',
        'title',
        'handle',
        'content',
        'status',
        'template_id',
        'show_title',
        'seo_title',
        'seo_description',
        'published_at',
        'author_id',
        'blocks_data',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'show_title' => 'boolean',
        'blocks_data' => 'array',
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(StorefrontTemplate::class, 'template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
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

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePrivate($query)
    {
        return $query->where('status', 'private');
    }

    // Methods
    public function isPublished(): bool
    {
        return $this->status === 'published' && ($this->published_at === null || $this->published_at <= now());
    }

    public function getTemplateHandle(): ?string
    {
        return $this->template?->handle;
    }

    public function getUrl(): string
    {
        return "/pages/{$this->handle}";
    }
}
