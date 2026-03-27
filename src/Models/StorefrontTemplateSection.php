<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorefrontTemplateSection extends Model
{
    protected $table = 'shopper_template_sections';

    protected $fillable = [
        'template_id',
        'section_id',
        'settings',
        'blocks_data',
        'sort_order',
        'is_visible',
        'section_key',
    ];

    protected $casts = [
        'settings' => 'array',
        'blocks_data' => 'array',
        'sort_order' => 'integer',
        'is_visible' => 'boolean',
    ];

    // Relationships
    public function template(): BelongsTo
    {
        return $this->belongsTo(StorefrontTemplate::class, 'template_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(StorefrontSection::class, 'section_id');
    }

    // Scopes
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
