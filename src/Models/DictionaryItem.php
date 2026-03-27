<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DictionaryItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'dictionary',
        'value',
        'label',
        'extra',
        'order',
        'is_enabled',
        'is_system',
    ];

    protected $casts = [
        'extra' => 'array',
        'order' => 'integer',
        'is_enabled' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Scope to filter by dictionary handle
     */
    public function scopeForDictionary($query, string $handle)
    {
        return $query->where('dictionary', $handle);
    }

    /**
     * Scope to get only enabled items
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope to get custom (non-system) items
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Get extra field value
     */
    public function getExtra(string $key, mixed $default = null): mixed
    {
        return data_get($this->extra, $key, $default);
    }

    /**
     * Set extra field value
     */
    public function setExtra(string $key, mixed $value): self
    {
        $extra = $this->extra ?? [];
        data_set($extra, $key, $value);
        $this->extra = $extra;

        return $this;
    }
}
