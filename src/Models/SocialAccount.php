<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'provider_data' => 'array',
        'provider_token' => 'encrypted',
        'provider_refresh_token' => 'encrypted',
    ];

    /**
     * Get the user that owns the social account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return
            $this->provider_data['avatar'] ??
            $this->provider_data['avatar_url'] ?? $this->provider_data['picture'] ?? null;
    }

    /**
     * Get the provider display name.
     */
    public function getDisplayNameAttribute(): ?string
    {
        return
            $this->provider_data['name'] ??
            $this->provider_data['display_name'] ?? $this->provider_data['login'] ?? null;
    }

    /**
     * Check if the token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (! isset($this->provider_data['expires_at'])) {
            return false;
        }

        return now()->isAfter($this->provider_data['expires_at']);
    }

    /**
     * Get all supported providers.
     */
    public static function getSupportedProviders(): array
    {
        return [
            'google' => [
                'name' => 'Google',
                'icon' => 'fab fa-google',
                'color' => '#db4437',
                'scopes' => ['openid', 'profile', 'email'],
            ],
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'fab fa-facebook-f',
                'color' => '#3b5998',
                'scopes' => ['email', 'public_profile'],
            ],
            'twitter' => [
                'name' => 'Twitter',
                'icon' => 'fab fa-twitter',
                'color' => '#1da1f2',
                'scopes' => ['tweet.read', 'users.read'],
            ],
            'github' => [
                'name' => 'GitHub',
                'icon' => 'fab fa-github',
                'color' => '#333333',
                'scopes' => ['user:email'],
            ],
            'linkedin' => [
                'name' => 'LinkedIn',
                'icon' => 'fab fa-linkedin-in',
                'color' => '#0077b5',
                'scopes' => ['r_liteprofile', 'r_emailaddress'],
            ],
            'apple' => [
                'name' => 'Apple',
                'icon' => 'fab fa-apple',
                'color' => '#000000',
                'scopes' => ['name', 'email'],
            ],
            'discord' => [
                'name' => 'Discord',
                'icon' => 'fab fa-discord',
                'color' => '#7289da',
                'scopes' => ['identify', 'email'],
            ],
            'microsoft' => [
                'name' => 'Microsoft',
                'icon' => 'fab fa-microsoft',
                'color' => '#00a4ef',
                'scopes' => ['openid', 'profile', 'email'],
            ],
        ];
    }

    /**
     * Check if provider is supported.
     */
    public static function isProviderSupported(string $provider): bool
    {
        return array_key_exists($provider, self::getSupportedProviders());
    }
}
