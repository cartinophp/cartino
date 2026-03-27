<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetFolder extends Model
{
    protected $fillable = [
        'container',
        'path',
        'basename',
        'parent_id',
        'title',
        'meta',
        'data',
        'allow_uploads',
    ];

    protected $casts = [
        'meta' => 'array',
        'data' => 'array',
        'allow_uploads' => 'boolean',
    ];

    public function containerModel(): BelongsTo
    {
        return $this->belongsTo(AssetContainer::class, 'container', 'handle');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'folder', 'path')->where('container', $this->container);
    }

    public function getDepth(): int
    {
        return substr_count($this->path, '/');
    }

    public function getBreadcrumbs(): array
    {
        $parts = explode('/', $this->path);
        $breadcrumbs = [];
        $currentPath = '';

        foreach ($parts as $part) {
            $currentPath .= ($currentPath ? '/' : '').$part;
            $breadcrumbs[] = [
                'path' => $currentPath,
                'name' => $part,
            ];
        }

        return $breadcrumbs;
    }

    public function scopeInContainer($query, string $container)
    {
        return $query->where('container', $container);
    }

    public function scopeRootFolders($query)
    {
        return $query->whereNull('parent_id');
    }
}
