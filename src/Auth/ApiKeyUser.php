<?php

declare(strict_types=1);

namespace Cartino\Auth;

use Cartino\Models\ApiKey;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;

/**
 * Utente virtuale basato su API Key, utile per far funzionare le policy CRUD.
 */
class ApiKeyUser implements Authenticatable, AuthorizableContract
{
    use Authorizable;

    public function __construct(
        private ApiKey $apiKey,
    ) {}

    public function getAuthIdentifierName(): string
    {
        return 'api_key_id';
    }

    public function getAuthIdentifier(): int|string|null
    {
        return $this->apiKey->id;
    }

    public function getAuthPassword(): ?string
    {
        return null;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function can($ability, $arguments = []): bool
    {
        // Pieno accesso
        if ($this->apiKey->type === 'full_access') {
            return true;
        }

        // Read-only: abilita solo view
        if ($this->apiKey->type === 'read_only') {
            return is_string($ability) && str_starts_with($ability, 'view');
        }

        // Custom: verifica permessi granulari
        if (is_string($ability)) {
            return $this->apiKey->hasPermission($ability);
        }

        return false;
    }

    public function getApiKey(): ApiKey
    {
        return $this->apiKey;
    }
}
