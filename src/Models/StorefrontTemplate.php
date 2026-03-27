<?php

namespace Cartino\Models;

use Cartino\Support\HasSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorefrontTemplate extends Model
{
    use HasSite;

    protected $table = 'shopper_templates';

    protected $fillable = [
        'site_id',
        'handle',
        'name',
        'type',
        'sections',
        'settings',
        'layout',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'sections' => 'array',
        'settings' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function templateSections(): HasMany
    {
        return $this->hasMany(StorefrontTemplateSection::class, 'template_id')->orderBy('sort_order');
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(StorefrontSection::class, 'shopper_template_sections', 'template_id', 'section_id')
            ->withPivot(['settings', 'blocks_data', 'sort_order', 'is_visible', 'section_key'])
            ->withTimestamps()
            ->orderBy('pivot_sort_order');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    /**
     * Get compiled template data for frontend rendering
     */
    public function compile(): array
    {
        $compiledSections = [];

        foreach ($this->sections as $section) {
            $compiledSections[] = [
                'id' => $section->pivot->section_key ?? $section->id,
                'type' => $section->handle,
                'component' => $section->component_path,
                'settings' => $section->pivot->settings ?? [],
                'blocks' => $section->pivot->blocks_data ?? [],
                'schema' => $section->schema ?? [],
            ];
        }

        return [
            'template' => [
                'handle' => $this->handle,
                'name' => $this->name,
                'type' => $this->type,
                'layout' => $this->layout,
                'settings' => $this->settings,
            ],
            'sections' => $compiledSections,
        ];
    }

    /**
     * Check if template can be assigned to a resource type
     */
    public function canAssignTo(string $resourceType): bool
    {
        $allowedTypes = [
            'product' => ['product'],
            'collection' => ['collection', 'category'],
            'page' => ['page'],
            'blog' => ['blog'],
            'article' => ['article'],
            'index' => ['index', 'home'],
        ];

        return in_array($this->type, $allowedTypes[$resourceType] ?? []);
    }
}
