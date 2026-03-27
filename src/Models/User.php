<?php

namespace Cartino\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The guard name for Spatie Permission.
     *
     * @var string
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'avatar',
        'phone',
        'date_of_birth',
        'gender',
        'locale',
        'timezone',
        'marketing_consent',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
        'provider_refresh_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'date_of_birth' => 'date',
        'marketing_consent' => 'boolean',
        'provider_token' => 'encrypted',
        'provider_refresh_token' => 'encrypted',
    ];

    /**
     * Get the user's social accounts.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get social account for specific provider.
     */
    public function getSocialAccount(string $provider): ?SocialAccount
    {
        return $this->socialAccounts()->where('provider', $provider)->first();
    }

    /**
     * Get the user's groups.
     */
    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_user', 'user_id', 'user_group_id')->withTimestamps();
    }

    /**
     * Check if user belongs to a specific group.
     */
    public function belongsToGroup(string $groupName): bool
    {
        return $this->userGroups()->where('name', $groupName)->exists();
    }

    /**
     * Get user's default group.
     */
    public function getDefaultGroup(): ?UserGroup
    {
        return $this->userGroups()->where('is_default', true)->first() ?? UserGroup::where('is_default', true)->first();
    }

    /**
     * Check if user has social account for specific provider.
     */
    public function hasSocialAccount(string $provider): bool
    {
        return $this->socialAccounts()->where('provider', $provider)->exists();
    }

    /**
     * Get all connected providers.
     */
    public function getConnectedProvidersAttribute(): array
    {
        return $this->socialAccounts()->pluck('provider')->toArray();
    }

    /**
     * Check if user registered via OAuth.
     */
    public function isOAuthUser(): bool
    {
        return ! is_null($this->provider) || $this->socialAccounts()->exists();
    }

    /**
     * Check if user can login with password.
     */
    public function hasPassword(): bool
    {
        return ! is_null($this->password);
    }

    /**
     * Get user's avatar URL with fallbacks.
     */
    public function getAvatarUrlAttribute(): string
    {
        // Check if user has custom avatar
        if ($this->avatar) {
            return $this->avatar;
        }

        // Check social accounts for avatar
        $socialAccount = $this->socialAccounts()
            ->whereNotNull('provider_data->avatar')
            ->orWhereNotNull('provider_data->avatar_url')
            ->orWhereNotNull('provider_data->picture')
            ->first();

        if ($socialAccount && $socialAccount->avatar_url) {
            return $socialAccount->avatar_url;
        }

        // Generate Gravatar
        $hash = md5(strtolower(trim($this->email)));

        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    /**
     * Get user's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->email;
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Scope for OAuth users.
     */
    public function scopeOAuth($query)
    {
        return $query->whereNotNull('provider')->orWhereHas('socialAccounts');
    }

    /**
     * Scope for regular users.
     */
    public function scopeRegular($query)
    {
        return $query->whereNull('provider')->whereDoesntHave('socialAccounts');
    }

    /**
     * Create or update user from OAuth provider data.
     */
    public static function createOrUpdateFromProvider(string $provider, $providerUser): self
    {
        // Try to find existing user by provider ID
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;

            // Update social account data
            $socialAccount->update([
                'provider_token' => $providerUser->token,
                'provider_refresh_token' => $providerUser->refreshToken,
                'provider_data' => [
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                    'avatar' => $providerUser->getAvatar(),
                    'raw' => $providerUser->getRaw(),
                    'updated_at' => now(),
                ],
            ]);

            $user->updateLastLogin();

            return $user;
        }

        // Try to find existing user by email
        $user = self::where('email', $providerUser->getEmail())->first();

        if ($user) {
            // Link new provider to existing user
            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $providerUser->getId(),
                'provider_token' => $providerUser->token,
                'provider_refresh_token' => $providerUser->refreshToken,
                'provider_data' => [
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                    'avatar' => $providerUser->getAvatar(),
                    'raw' => $providerUser->getRaw(),
                    'created_at' => now(),
                ],
            ]);

            $user->updateLastLogin();

            return $user;
        }

        // Create new user
        $user = self::create([
            'name' => $providerUser->getName() ?: 'User',
            'email' => $providerUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
            'provider_token' => $providerUser->token,
            'provider_refresh_token' => $providerUser->refreshToken,
            'avatar' => $providerUser->getAvatar(),
            'email_verified_at' => now(), // OAuth emails are considered verified
            'last_login_at' => now(),
        ]);

        // Create social account record
        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
            'provider_token' => $providerUser->token,
            'provider_refresh_token' => $providerUser->refreshToken,
            'provider_data' => [
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'avatar' => $providerUser->getAvatar(),
                'raw' => $providerUser->getRaw(),
                'created_at' => now(),
            ],
        ]);

        return $user;
    }
}
