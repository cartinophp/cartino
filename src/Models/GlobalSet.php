<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSet extends Model
{
    use HasFactory;

    protected $table = 'global_sets';

    protected $fillable = [
        'handle',
        'title',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get a specific data value from the global set
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Set a specific data value in the global set
     */
    public function set(string $key, mixed $value): self
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);
        $this->data = $data;

        return $this;
    }

    /**
     * Scope to find by handle
     */
    public function scopeByHandle($query, string $handle)
    {
        return $query->where('handle', $handle);
    }
}
