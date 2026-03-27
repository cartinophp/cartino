<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewMedia extends Model
{
    use HasFactory;

    protected $table = 'review_media';

    protected $fillable = [
        'review_id',
        'media_type',
        'url',
        'thumbnail_url',
        'alt_text',
        'file_size',
        'mime_type',
        'sort_order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the review that owns the media
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class, 'review_id');
    }

    /**
     * Scope to get images only
     */
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    /**
     * Scope to get videos only
     */
    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }

    /**
     * Get the media type icon
     */
    public function getIconAttribute(): string
    {
        return $this->media_type === 'video' ? 'video' : 'photo';
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (! $this->file_size) {
            return 'Unknown size';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < (count($units) - 1); $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Check if the media is an image
     */
    public function getIsImageAttribute(): bool
    {
        return $this->media_type === 'image';
    }

    /**
     * Check if the media is a video
     */
    public function getIsVideoAttribute(): bool
    {
        return $this->media_type === 'video';
    }
}
