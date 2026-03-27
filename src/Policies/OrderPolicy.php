<?php

namespace Cartino\Policies;

use Cartino\Models\Order;
use Cartino\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view-orders');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('view-orders');
    }

    public function create(User $user): bool
    {
        return $user->can('create-orders');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('edit-orders');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('delete-orders') && ! $order->isPaid();
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->can('restore-orders');
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->can('force-delete-orders');
    }

    public function fulfill(User $user, Order $order): bool
    {
        return $user->can('fulfill-orders') && $order->isPaid();
    }

    public function ship(User $user, Order $order): bool
    {
        return $user->can('ship-orders') && $order->isFulfilled();
    }

    public function refund(User $user, Order $order): bool
    {
        return $user->can('refund-orders') && $order->isPaid();
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->can('cancel-orders') && ! $order->isShipped();
    }

    public function export(User $user): bool
    {
        return $user->can('export-orders');
    }
}
