<?php

namespace Cartino\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppInstallation extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'user_id',
        'version_installed',
        'configuration',
        'settings',
        'permissions_granted',
        'subscription_id',
        'plan_name',
        'monthly_cost',
        'trial_ends_at',
        'subscription_ends_at',
        'auto_renew',
        'status',
        'activated_at',
        'deactivated_at',
        'last_used_at',
        'usage_count',
        'usage_metrics',
        'error_count',
        'last_error_at',
        'last_error_message',
    ];

    protected $casts = [
        'configuration' => 'array',
        'settings' => 'array',
        'permissions_granted' => 'array',
        'usage_metrics' => 'array',
        'monthly_cost' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'last_used_at' => 'datetime',
        'last_error_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    // Relationships
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeOnTrial($query)
    {
        return $query->where('trial_ends_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('subscription_ends_at', '<', now());
    }

    // Accessors
    public function getIsOnTrialAttribute(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isPast();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && ! $this->is_expired;
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (! $this->subscription_ends_at) {
            return null;
        }

        return now()->diffInDays($this->subscription_ends_at, false);
    }

    // Methods
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function recordError(string $message): void
    {
        $this->increment('error_count');
        $this->update([
            'last_error_at' => now(),
            'last_error_message' => $message,
        ]);
    }

    public function updateSettings(array $settings): bool
    {
        return $this->update([
            'settings' => array_merge($this->settings ?? [], $settings),
        ]);
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function renew(int $months = 1): bool
    {
        if (! $this->subscription_ends_at) {
            return false;
        }

        $newEndDate = $this->subscription_ends_at->addMonths($months);

        return $this->update([
            'subscription_ends_at' => $newEndDate,
            'status' => 'active',
        ]);
    }

    public function cancel(): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);
    }

    public function suspend(?string $reason = null): bool
    {
        return $this->update([
            'status' => 'suspended',
            'last_error_message' => $reason,
        ]);
    }

    public function resume(): bool
    {
        return $this->update([
            'status' => 'active',
            'last_error_message' => null,
        ]);
    }
}
