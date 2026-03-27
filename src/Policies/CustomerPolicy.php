<?php

namespace Cartino\Policies;

use Cartino\Models\Customer;
use Cartino\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view-customers');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('view-customers');
    }

    public function create(User $user): bool
    {
        return $user->can('create-customers');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('edit-customers');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can('delete-customers');
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->can('restore-customers');
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->can('force-delete-customers');
    }

    public function viewOrders(User $user, Customer $customer): bool
    {
        return $user->can('view-orders');
    }

    public function export(User $user): bool
    {
        return $user->can('export-customers');
    }

    public function import(User $user): bool
    {
        return $user->can('import-customers');
    }
}
