<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssetTransformation extends Model
{
    protected $fillable = [
        'asset_id',
        'preset',
        'params',
        'params_hash',
        'path',
        'size',
        'width',
        'height',
        'last_accessed_at',
        'access_count',
    ];

    protected $casts = [
        'params' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'access_count' => 'integer',
        'last_accessed_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function url(): string
    {
        return Storage::disk(config('media.glide.cache_disk'))->url($this->path);
    }

    public function exists(): bool
    {
        return Storage::disk(config('media.glide.cache_disk'))->exists($this->path);
    }

    public function deleteFile(): void
    {
        if ($this->exists()) {
            Storage::disk(config('media.glide.cache_disk'))->delete($this->path);
        }
    }

    public function trackAccess(): void
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public static function hashParams(array $params): string
    {
        ksort($params);

        return hash('sha256', json_encode($params));
    }

    public function scopeOlderThan($query, int $days)
    {
        return $query->where('last_accessed_at', '<', now()->subDays($days));
    }
}
