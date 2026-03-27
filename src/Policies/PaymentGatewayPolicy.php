<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\PaymentGateway;
use Cartino\Models\User;

class PaymentGatewayPolicy
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
     * Determine if the user can view any payment_gateways.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view payment_gateways');
    }

    /**
     * Determine if the user can view the payment_gateway.
     */
    public function view(User $user, PaymentGateway $payment_gateway): bool
    {
        return $user->can('view payment_gateways');
    }

    /**
     * Determine if the user can create payment_gateways.
     */
    public function create(User $user): bool
    {
        return $user->can('create payment_gateways');
    }

    /**
     * Determine if the user can update the payment_gateway.
     */
    public function update(User $user, PaymentGateway $payment_gateway): bool
    {
        return $user->can('edit payment_gateways');
    }

    /**
     * Determine if the user can delete the payment_gateway.
     */
    public function delete(User $user, PaymentGateway $payment_gateway): bool
    {
        return $user->can('delete payment_gateways');
    }

    /**
     * Determine if the user can restore the payment_gateway.
     */
    public function restore(User $user, PaymentGateway $payment_gateway): bool
    {
        return $user->can('delete payment_gateways');
    }

    /**
     * Determine if the user can permanently delete the payment_gateway.
     */
    public function forceDelete(User $user, PaymentGateway $payment_gateway): bool
    {
        return $user->can('delete payment_gateways');
    }
}
