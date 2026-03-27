<?php

declare(strict_types=1);

namespace Cartino\Policies\Concerns;

use Cartino\Models\User;
use Illuminate\Database\Eloquent\Model;

trait HasStandardAuthorization
{
    /**
     * Override in child policy to define permission prefix (e.g., 'products', 'brands')
     */
    abstract protected function getPermissionPrefix(): string;

    /**
     * Super admin bypass
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can($this->permission('view'));
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can($this->permission('view'));
    }

    public function create(User $user): bool
    {
        return $user->can($this->permission('create'));
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can($this->permission('edit'));
    }

    public function delete(User $user, Model $model): bool
    {
        if (! $user->can($this->permission('delete'))) {
            return false;
        }

        return $this->canDeleteModel($model);
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->can($this->permission('delete'));
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $user->can($this->permission('delete'));
    }

    /**
     * Override in child policy to add custom delete restrictions
     */
    protected function canDeleteModel(Model $model): bool
    {
        return true;
    }

    /**
     * Build permission name
     */
    protected function permission(string $action): string
    {
        return "{$action}-{$this->getPermissionPrefix()}";
    }
}
