<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;

    protected $table = 'shopper_pages';

    protected $fillable = [
        'site_id',
        'title',
        'handle',
        'content',
        'status',
        'template_id',
        'author_id',
        'show_title',
        'seo_title',
        'seo_description',
        'published_at',
    ];

    protected $casts = [
        'show_title' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(StorefrontTemplate::class, 'template_id');
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

    // Accessors
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' && ($this->published_at === null || $this->published_at <= now());
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getIsPrivateAttribute(): bool
    {
        return $this->status === 'private';
    }

    public function getIsScheduledAttribute(): bool
    {
        return $this->status === 'published' && $this->published_at && $this->published_at > now();
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->attributes['seo_title'] ?: $this->title;
    }

    public function getSeoDescriptionAttribute(): string
    {
        if ($this->attributes['seo_description']) {
            return $this->attributes['seo_description'];
        }

        // Generate description from content (strip HTML and limit to 160 chars)
        $content = strip_tags($this->content);
        $content = preg_replace('/\s+/', ' ', $content);

        if (strlen($content) <= 160) {
            return $content;
        }

        return substr($content, 0, 157).'...';
    }

    public function getUrlAttribute(): string
    {
        return "/pages/{$this->handle}";
    }

    public function getPreviewUrlAttribute(): string
    {
        return $this->url.'?preview=true';
    }

    public function getWordCountAttribute(): int
    {
        $text = strip_tags($this->content);

        return str_word_count($text);
    }

    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil($this->word_count / 200));
    }

    public function getExcerptAttribute(): string
    {
        $text = strip_tags($this->content);
        $text = preg_replace('/\s+/', ' ', trim($text));

        if (strlen($text) <= 200) {
            return $text;
        }

        return substr($text, 0, 197).'...';
    }
}
