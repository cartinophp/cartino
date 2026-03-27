<?php

namespace Cartino\Models;

use Cartino\Events\AppWebhookEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class App extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'handle',
        'description',
        'version',
        'author',
        'author_url',
        'support_url',
        'documentation_url',
        'app_store_id',
        'price',
        'pricing_model',
        'pricing_plans',
        'metadata',
        'permissions',
        'webhooks',
        'api_scopes',
        'is_installed',
        'is_active',
        'is_system',
        'installed_at',
        'last_updated_at',
        'icon_url',
        'screenshots',
        'banner_url',
        'assets',
        'categories',
        'tags',
        'min_shopper_version',
        'max_shopper_version',
        'dependencies',
        'status',
        'rating',
        'review_count',
        'install_count',
    ];

    protected $casts = [
        'pricing_plans' => 'array',
        'metadata' => 'array',
        'permissions' => 'array',
        'webhooks' => 'array',
        'api_scopes' => 'array',
        'screenshots' => 'array',
        'assets' => 'array',
        'categories' => 'array',
        'tags' => 'array',
        'dependencies' => 'array',
        'is_installed' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'installed_at' => 'datetime',
        'last_updated_at' => 'datetime',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($app) {
            if (empty($app->slug)) {
                $app->slug = Str::slug($app->name);
            }
            if (empty($app->handle)) {
                $app->handle = Str::slug($app->name, '_');
            }
        });
    }

    // Relationships
    public function installation(): HasOne
    {
        return $this->hasOne(AppInstallation::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AppReview::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(AppWebhook::class);
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(AppApiToken::class);
    }

    // Scopes
    public function scopeInstalled($query)
    {
        return $query->where('is_installed', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->whereJsonContains('categories', $category);
    }

    public function scopeFree($query)
    {
        return $query->where('pricing_model', 'free');
    }

    public function scopePaid($query)
    {
        return $query->where('pricing_model', '!=', 'free');
    }

    // Accessors
    public function getIsInstalledAttribute($value): bool
    {
        return (bool) $value;
    }

    public function getIsFreeAttribute(): bool
    {
        return $this->pricing_model === 'free' || $this->price == 0;
    }

    public function getIsCompatibleAttribute(): bool
    {
        $shopperVersion = config('cartino.version', '1.0.0');

        if ($this->min_shopper_version && version_compare($shopperVersion, $this->min_shopper_version, '<')) {
            return false;
        }

        if ($this->max_shopper_version && version_compare($shopperVersion, $this->max_shopper_version, '>')) {
            return false;
        }

        return true;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_free) {
            return __('apps.free');
        }

        return match ($this->pricing_model) {
            'one_time' => formatCurrency($this->price),
            'recurring' => formatCurrency($this->price).'/'.__('apps.month'),
            default => formatCurrency($this->price),
        };
    }

    // Methods
    public function install(array $config = []): AppInstallation
    {
        if ($this->is_installed) {
            throw new \Exception(__('apps.already_installed'));
        }

        $installation = $this->installation()->create([
            'user_id' => auth()->id(),
            'version_installed' => $this->version,
            'configuration' => $config,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $this->update([
            'is_installed' => true,
            'is_active' => true,
            'installed_at' => now(),
            'install_count' => $this->install_count + 1,
        ]);

        // Fire installation webhook
        $this->fireWebhook('app.installed', [
            'app' => $this->toArray(),
            'installation' => $installation->toArray(),
        ]);

        return $installation;
    }

    public function uninstall(): bool
    {
        if (! $this->is_installed) {
            throw new \Exception(__('apps.not_installed'));
        }

        if ($this->is_system) {
            throw new \Exception(__('apps.cannot_uninstall_system'));
        }

        // Fire uninstallation webhook
        $this->fireWebhook('app.uninstalled', [
            'app' => $this->toArray(),
        ]);

        $this->installation()->delete();

        $this->update([
            'is_installed' => false,
            'is_active' => false,
        ]);

        return true;
    }

    public function activate(): bool
    {
        if (! $this->is_installed) {
            throw new \Exception(__('apps.not_installed'));
        }

        $this->update(['is_active' => true]);
        $this->installation()->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $this->fireWebhook('app.activated', [
            'app' => $this->toArray(),
        ]);

        return true;
    }

    public function deactivate(): bool
    {
        if ($this->is_system) {
            throw new \Exception(__('apps.cannot_deactivate_system'));
        }

        $this->update(['is_active' => false]);
        $this->installation()->update([
            'status' => 'inactive',
            'deactivated_at' => now(),
        ]);

        $this->fireWebhook('app.deactivated', [
            'app' => $this->toArray(),
        ]);

        return true;
    }

    public function updateRating(): void
    {
        $reviews = $this->reviews()->where('status', 'approved');

        $this->update([
            'rating' => $reviews->avg('rating') ?? 0,
            'review_count' => $reviews->count(),
        ]);
    }

    private function fireWebhook(string $event, array $data): void
    {
        // TODO: Implement webhook firing logic
        event(new AppWebhookEvent($this, $event, $data));
    }

    // Static methods
    public static function findByHandle(string $handle): ?self
    {
        return static::where('handle', $handle)->first();
    }

    public static function getInstalled()
    {
        return static::installed()->get();
    }

    public static function getActive()
    {
        return static::active()->get();
    }
}
