<?php

namespace Cartino\Services;

use Cartino\DTO\Cart\CartData;
use Cartino\Enums\CartStatus;
use Cartino\Jobs\SendCartRecoveryEmail;
use Cartino\Models\Cart;
use Cartino\Repositories\CartRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CartService
{
    public function __construct(
        private CartRepository $repository,
    ) {}

    /**
     * Create new cart
     */
    public function createCart(array $data): CartData
    {
        $cart = $this->repository->create($data);

        return CartData::fromModel($cart);
    }

    /**
     * Update cart
     */
    public function updateCart(Cart $cart, array $data): CartData
    {
        // Update activity timestamp
        $data['last_activity_at'] = now();

        $cart = $this->repository->update($cart->id, $data);

        return CartData::fromModel($cart);
    }

    /**
     * Add item to cart
     */
    public function addItem(Cart $cart, array $itemData): CartData
    {
        $items = $cart->items ?? [];

        // Check if product already exists in cart
        $existingIndex = collect($items)->search(function ($item) use ($itemData) {
            return $item['product_id'] == $itemData['product_id'];
        });

        if ($existingIndex !== false) {
            // Update existing item quantity
            $items[$existingIndex]['quantity'] += $itemData['quantity'];
        } else {
            // Add new item
            $items[] = [
                'product_id' => $itemData['product_id'],
                'product_name' => $itemData['product_name'] ?? null,
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'total' => $itemData['quantity'] * $itemData['price'],
            ];
        }

        // Recalculate cart totals
        $subtotal = collect($items)->sum('total');

        return $this->updateCart($cart, [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal, // Simplified for now
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Cart $cart, int $productId): CartData
    {
        $items = collect($cart->items ?? [])
            ->filter(function ($item) use ($productId) {
                return $item['product_id'] != $productId;
            })
            ->values()
            ->toArray();

        // Recalculate cart totals
        $subtotal = collect($items)->sum('total');

        return $this->updateCart($cart, [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
        ]);
    }

    /**
     * Clear cart
     */
    public function clearCart(Cart $cart): CartData
    {
        return $this->updateCart($cart, [
            'items' => [],
            'subtotal' => 0,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
        ]);
    }

    /**
     * Mark cart as abandoned
     */
    public function markAsAbandoned(Cart $cart): bool
    {
        return $this->repository->markAsAbandoned($cart->id);
    }

    /**
     * Mark cart as recovered
     */
    public function markAsRecovered(Cart $cart): bool
    {
        return $this->repository->markAsRecovered($cart->id);
    }

    /**
     * Mark cart as converted
     */
    public function markAsConverted(Cart $cart, int $orderId): bool
    {
        return $this->repository->markAsConverted($cart->id, $orderId);
    }

    /**
     * Process cart abandonment (cron job)
     */
    public function processAbandonment(int $hoursThreshold = 1): array
    {
        $marked = $this->repository->autoMarkAbandoned($hoursThreshold);

        Log::info("Marked {$marked} carts as abandoned");

        return [
            'marked_abandoned' => $marked,
        ];
    }

    /**
     * Schedule recovery email
     */
    public function scheduleRecoveryEmail(Cart $cart, int $delayHours = 1): void
    {
        SendCartRecoveryEmail::dispatch($cart)->delay(now()->addHours($delayHours));
    }

    /**
     * Send immediate recovery email
     */
    public function sendRecoveryEmail(Cart $cart): bool
    {
        try {
            // Log the recovery attempt
            $this->repository->update($cart->id, [
                'recovery_emails_sent' => ($cart->recovery_emails_sent ?? 0) + 1,
                'last_recovery_email_sent_at' => now(),
            ]);

            SendCartRecoveryEmail::dispatchNow($cart);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send cart recovery email', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get carts eligible for recovery
     */
    public function getCartsEligibleForRecovery(): array
    {
        $carts = $this->repository->getEligibleForRecovery();

        return $carts->map(fn ($cart) => CartData::fromModel($cart))->toArray();
    }

    /**
     * Auto-schedule recovery emails for eligible carts
     */
    public function autoScheduleRecoveryEmails(): int
    {
        $eligibleCarts = $this->getCartsEligibleForRecovery();
        $scheduled = 0;

        foreach ($eligibleCarts as $cartData) {
            $cart = Cart::find($cartData->id);
            if ($cart && $cart->isEligibleForRecovery()) {
                $this->scheduleRecoveryEmail($cart);
                $scheduled++;
            }
        }

        return $scheduled;
    }

    /**
     * Get recovery statistics
     */
    public function getRecoveryStatistics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        return $this->repository->getRecoveryStatistics($startDate, $endDate);
    }

    /**
     * Calculate recovery rate
     */
    public function getRecoveryRate(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $stats = $this->getRecoveryStatistics($startDate, $endDate);

        return $stats['recovery_rate'] ?? 0.0;
    }

    /**
     * Get top abandoned products
     */
    public function getTopAbandonedProducts(int $limit = 10): array
    {
        return $this->repository->getTopAbandonedProducts($limit);
    }

    /**
     * Clean old carts
     */
    public function cleanOldCarts(int $daysOld = 30): int
    {
        return $this->repository->cleanOldCarts($daysOld);
    }

    /**
     * Get revenue lost from abandoned carts
     */
    public function getLostRevenue(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        return $this->repository->getLostRevenue($startDate, $endDate);
    }

    /**
     * Generate recovery link for cart
     */
    public function generateRecoveryLink(Cart $cart): string
    {
        $token = base64_encode($cart->id.'|'.($cart->email ?? $cart->customer?->email).'|'.now()->timestamp);

        return route('cart.recover', ['token' => $token]);
    }

    /**
     * Validate recovery token
     */
    public function validateRecoveryToken(string $token): ?Cart
    {
        try {
            $decoded = base64_decode($token);
            [$cartId, $email, $timestamp] = explode('|', $decoded);

            // Token expires after 7 days
            if ((now()->timestamp - $timestamp) > 604800) {
                return null;
            }

            return Cart::where('id', $cartId)
                ->where(function ($query) use ($email) {
                    $query->where('email', $email)->orWhereHas('customer', function ($q) use ($email) {
                        $q->where('email', $email);
                    });
                })
                ->where('status', CartStatus::ABANDONED)
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get or create cart for session
     */
    public function getOrCreateForSession(string $sessionId, ?int $customerId = null): CartData
    {
        $cart = $this->repository->getBySession($sessionId);

        if (! $cart) {
            $cart = $this->repository->create([
                'session_id' => $sessionId,
                'customer_id' => $customerId,
                'status' => CartStatus::ACTIVE,
                'currency' => config('cartino.currency', 'EUR'),
                'last_activity_at' => now(),
            ]);
        } else {
            // Update activity
            $this->repository->updateActivity($cart->id);
        }

        return CartData::fromModel($cart);
    }

    /**
     * Get or create cart for customer
     */
    public function getOrCreateForCustomer(int $customerId): CartData
    {
        $cart = $this->repository->getByCustomer($customerId);

        if (! $cart) {
            $cart = $this->repository->create([
                'customer_id' => $customerId,
                'status' => CartStatus::ACTIVE,
                'currency' => config('cartino.currency', 'EUR'),
                'last_activity_at' => now(),
            ]);
        } else {
            // Update activity
            $this->repository->updateActivity($cart->id);
        }

        return CartData::fromModel($cart);
    }

    /**
     * Merge carts (when user logs in)
     */
    public function mergeCarts(Cart $sessionCart, Cart $customerCart): CartData
    {
        $sessionItems = $sessionCart->items ?? [];
        $customerItems = $customerCart->items ?? [];

        // Merge items
        $mergedItems = collect($customerItems);

        foreach ($sessionItems as $sessionItem) {
            $existingIndex = $mergedItems->search(function ($item) use ($sessionItem) {
                return $item['product_id'] == $sessionItem['product_id'];
            });

            if ($existingIndex !== false) {
                // Add quantities
                $mergedItems[$existingIndex]['quantity'] += $sessionItem['quantity'];
                $mergedItems[$existingIndex]['total'] =
                    $mergedItems[$existingIndex]['quantity'] * $mergedItems[$existingIndex]['price'];
            } else {
                // Add new item
                $mergedItems->push($sessionItem);
            }
        }

        // Update customer cart
        $subtotal = $mergedItems->sum('total');
        $updatedCart = $this->updateCart($customerCart, [
            'items' => $mergedItems->toArray(),
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
        ]);

        // Delete session cart
        $this->repository->delete($sessionCart->id);

        return $updatedCart;
    }
}
