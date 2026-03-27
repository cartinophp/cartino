<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'site_id',
        'handle',
        'title',
        'description',
        'location',
        'settings',
        'data',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'settings' => 'array',
        'data' => 'array',
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function getTree(): array
    {
        return $this->items()
            ->with('children')
            ->get()
            ->map(function ($item) {
                return $item->toTreeArray();
            })
            ->toArray();
    }

    public function getMaxDepth(): int
    {
        return $this->settings['max_depth'] ?? 3;
    }

    public function getAllowedCollections(): array
    {
        return $this->settings['collections'] ?? [];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
