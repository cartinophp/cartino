<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'reference_type',
        'reference_id',
        'data',
        'is_enabled',
        'opens_in_new_window',
        'css_class',
        'sort_order',
        'depth',
    ];

    protected $casts = [
        'data' => 'array',
        'is_enabled' => 'boolean',
        'opens_in_new_window' => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    public function getComputedUrl(): ?string
    {
        // If manual URL is set, use it
        if ($this->url) {
            return $this->url;
        }

        // Generate URL based on reference
        if ($this->reference) {
            return $this->reference->url ?? null;
        }

        return null;
    }

    public function toTreeArray(): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->getComputedUrl(),
            'type' => $this->type,
            'is_enabled' => $this->is_enabled,
            'opens_in_new_window' => $this->opens_in_new_window,
            'css_class' => $this->css_class,
            'sort_order' => $this->sort_order,
            'depth' => $this->depth,
            'data' => $this->data,
            'children' => [],
        ];

        if ($this->children->isNotEmpty()) {
            $data['children'] = $this->children->map(function ($child) {
                return $child->toTreeArray();
            })->toArray();
        }

        return $data;
    }

    public function updateDepth(): void
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        $this->update(['depth' => $depth]);

        // Update children depths recursively
        $this->children->each(function ($child) {
            $child->updateDepth();
        });
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeRootItems($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
