<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\PurchaseOrderItem;
use Cartino\Models\User;

class PurchaseOrderItemPolicy
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
     * Determine if the user can view any purchase_order_items.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view purchase_order_items');
    }

    /**
     * Determine if the user can view the purchase_order_item.
     */
    public function view(User $user, PurchaseOrderItem $purchase_order_item): bool
    {
        return $user->can('view purchase_order_items');
    }

    /**
     * Determine if the user can create purchase_order_items.
     */
    public function create(User $user): bool
    {
        return $user->can('create purchase_order_items');
    }

    /**
     * Determine if the user can update the purchase_order_item.
     */
    public function update(User $user, PurchaseOrderItem $purchase_order_item): bool
    {
        return $user->can('edit purchase_order_items');
    }

    /**
     * Determine if the user can delete the purchase_order_item.
     */
    public function delete(User $user, PurchaseOrderItem $purchase_order_item): bool
    {
        return $user->can('delete purchase_order_items');
    }

    /**
     * Determine if the user can restore the purchase_order_item.
     */
    public function restore(User $user, PurchaseOrderItem $purchase_order_item): bool
    {
        return $user->can('delete purchase_order_items');
    }

    /**
     * Determine if the user can permanently delete the purchase_order_item.
     */
    public function forceDelete(User $user, PurchaseOrderItem $purchase_order_item): bool
    {
        return $user->can('delete purchase_order_items');
    }
}
