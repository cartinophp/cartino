<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
        'type',
        'permissions',
        'last_used_at',
        'expires_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'key',
    ];

    /**
     * Get the user who created this API key
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the API key is valid
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the API key has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->type === 'full_access') {
            return true;
        }

        if ($this->type === 'read_only') {
            return str_starts_with($permission, 'view');
        }

        if ($this->type === 'custom' && is_array($this->permissions)) {
            return in_array($permission, $this->permissions, true);
        }

        return false;
    }

    /**
     * Check if the API key can access a specific endpoint
     */
    public function canAccessEndpoint(string $method, string $path): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        if ($this->type === 'full_access') {
            return true;
        }

        if ($this->type === 'read_only') {
            return in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
        }

        // Custom: mappa il metodo HTTP a un permesso CRUD
        $method = strtoupper($method);

        $map = [
            'GET' => ['view', 'read', 'list'],
            'HEAD' => ['view', 'read', 'list'],
            'OPTIONS' => ['view', 'read', 'list'],
            'POST' => ['create', 'write', 'manage'],
            'PUT' => ['update', 'edit', 'write', 'manage'],
            'PATCH' => ['update', 'edit', 'write', 'manage'],
            'DELETE' => ['delete', 'destroy', 'manage'],
        ];

        $required = $map[$method] ?? null;

        if (! $required) {
            return false;
        }

        if (! is_array($this->permissions) || $this->permissions === []) {
            return false;
        }

        foreach ($required as $needle) {
            foreach ($this->permissions as $perm) {
                if (is_string($perm) && str_contains($perm, $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->last_used_at = now();
        $this->saveQuietly();
    }

    /**
     * Generate a new API key
     */
    public static function generate(): string
    {
        return 'ck_'.Str::random(32);
    }

    /**
     * Hash an API key for storage
     */
    public static function hash(string $key): string
    {
        return hash('sha256', $key);
    }

    /**
     * Scope to get only active keys
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to get expired keys
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
