<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasPermissions;

class UserGroup extends Model
{
    use HasFactory, HasPermissions;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_default',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'metadata' => 'array',
    ];

    protected $guard_name = 'api';

    /**
     * Relationship with users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_user', 'user_group_id', 'user_id')->withTimestamps();
    }

    /**
     * Scope for active groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default group
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] ?? ucfirst(str_replace('-', ' ', $this->name));
    }
}
