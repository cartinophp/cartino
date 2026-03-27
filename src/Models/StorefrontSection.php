<?php

namespace Cartino\Models;

use Cartino\Support\HasSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorefrontSection extends Model
{
    use HasSite;

    protected $table = 'shopper_sections';

    protected $fillable = [
        'site_id',
        'handle',
        'name',
        'description',
        'component_path',
        'schema',
        'preset_data',
        'blocks',
        'category',
        'is_global',
        'is_active',
        'max_blocks',
        'icon',
    ];

    protected $casts = [
        'schema' => 'array',
        'preset_data' => 'array',
        'blocks' => 'array',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'max_blocks' => 'integer',
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function templates()
    {
        return $this->belongsToMany(StorefrontTemplate::class, 'shopper_template_sections', 'section_id', 'template_id')
            ->withPivot(['settings', 'blocks_data', 'sort_order', 'is_visible', 'section_key'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Methods
    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    /**
     * Get section schema with default values merged
     */
    public function getCompiledSchema(): array
    {
        $schema = $this->schema ?? [];
        $presets = $this->preset_data ?? [];

        return array_merge($presets, $schema);
    }

    /**
     * Create a new section instance for a template
     */
    public function createInstance(array $settings = [], array $blocks = []): array
    {
        return [
            'section_id' => $this->id,
            'settings' => array_merge($this->preset_data ?? [], $settings),
            'blocks_data' => $blocks,
            'section_key' => uniqid($this->handle.'_'),
        ];
    }
}
