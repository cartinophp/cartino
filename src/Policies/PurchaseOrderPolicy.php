<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\PurchaseOrder;
use Cartino\Models\User;

class PurchaseOrderPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super admins can do anything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_super) {
            return true;
        }

        return null;
    }

    /**
     * Determine if the user can view any purchase_orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view purchase_orders');
    }

    /**
     * Determine if the user can view the purchase_order.
     */
    public function view(User $user, PurchaseOrder $purchase_order): bool
    {
        return $user->can('view purchase_orders');
    }

    /**
     * Determine if the user can create purchase_orders.
     */
    public function create(User $user): bool
    {
        return $user->can('create purchase_orders');
    }

    /**
     * Determine if the user can update the purchase_order.
     */
    public function update(User $user, PurchaseOrder $purchase_order): bool
    {
        return $user->can('edit purchase_orders');
    }

    /**
     * Determine if the user can delete the purchase_order.
     */
    public function delete(User $user, PurchaseOrder $purchase_order): bool
    {
        return $user->can('delete purchase_orders');
    }

    /**
     * Determine if the user can restore the purchase_order.
     */
    public function restore(User $user, PurchaseOrder $purchase_order): bool
    {
        return $user->can('delete purchase_orders');
    }

    /**
     * Determine if the user can permanently delete the purchase_order.
     */
    public function forceDelete(User $user, PurchaseOrder $purchase_order): bool
    {
        return $user->can('delete purchase_orders');
    }
}
