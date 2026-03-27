<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AppApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'name',
        'token',
        'scopes',
        'last_used_at',
        'usage_count',
        'usage_limits',
        'expires_at',
        'is_active',
        'allowed_ips',
    ];

    protected $casts = [
        'scopes' => 'array',
        'usage_limits' => 'array',
        'allowed_ips' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'token',
    ];

    // Relationships
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($token) {
            if (empty($token->token)) {
                $token->token = Str::random(80);
            }
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeByScope($query, string $scope)
    {
        return $query->whereJsonContains('scopes', $scope);
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsValidAttribute(): bool
    {
        return $this->is_active && ! $this->is_expired;
    }

    public function getMaskedTokenAttribute(): string
    {
        if (strlen($this->token) <= 8) {
            return $this->token;
        }

        return substr($this->token, 0, 4).str_repeat('*', strlen($this->token) - 8).substr($this->token, -4);
    }

    // Methods
    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? []);
    }

    public function hasAnyScope(array $scopes): bool
    {
        return ! empty(array_intersect($scopes, $this->scopes ?? []));
    }

    public function recordUsage(?string $endpoint = null): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);

        // Record endpoint-specific usage if provided
        if ($endpoint && $this->usage_limits) {
            $limits = $this->usage_limits;
            $limits[$endpoint] = ($limits[$endpoint] ?? 0) + 1;
            $this->update(['usage_limits' => $limits]);
        }
    }

    public function isAllowedFromIp(string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true; // No IP restrictions
        }

        return in_array($ip, $this->allowed_ips);
    }

    public function getRateLimitForEndpoint(string $endpoint): ?int
    {
        return $this->usage_limits[$endpoint] ?? null;
    }

    public function revoke(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function renew(?int $days = null): bool
    {
        $expiresAt = $days ? now()->addDays($days) : null;

        return $this->update([
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);
    }

    public function regenerate(): string
    {
        $newToken = Str::random(80);
        $this->update(['token' => $newToken]);

        return $newToken;
    }

    // Static methods
    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->active()->first();
    }

    public static function createForApp(App $app, string $name, array $scopes = [], array $options = []): self
    {
        return static::create([
            'app_id' => $app->id,
            'name' => $name,
            'scopes' => $scopes,
            'expires_at' => $options['expires_at'] ?? null,
            'allowed_ips' => $options['allowed_ips'] ?? null,
            'usage_limits' => $options['usage_limits'] ?? null,
        ]);
    }
}
