<?php

namespace Cartino\Services;

use Cartino\DTO\AbandonedCart\AbandonedCartData;
use Cartino\Jobs\SendAbandonedCartEmail;
use Cartino\Models\AbandonedCart;
use Cartino\Repositories\AbandonedCartRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AbandonedCartService
{
    public function __construct(
        private AbandonedCartRepository $repository,
    ) {}

    /**
     * Create abandoned cart record
     */
    public function createAbandonedCart(array $data): AbandonedCartData
    {
        $cart = $this->repository->create($data);

        return AbandonedCartData::fromModel($cart);
    }

    /**
     * Update abandoned cart
     */
    public function updateAbandonedCart(AbandonedCart $cart, array $data): AbandonedCartData
    {
        $cart = $this->repository->update($cart->id, $data);

        return AbandonedCartData::fromModel($cart);
    }

    /**
     * Mark cart as recovered
     */
    public function markAsRecovered(AbandonedCart $cart): bool
    {
        return $this->repository->markAsRecovered($cart->id);
    }

    /**
     * Schedule recovery email
     */
    public function scheduleRecoveryEmail(AbandonedCart $cart, int $delayHours = 1): void
    {
        SendAbandonedCartEmail::dispatch($cart)->delay(now()->addHours($delayHours));
    }

    /**
     * Send immediate recovery email
     */
    public function sendRecoveryEmail(AbandonedCart $cart): bool
    {
        try {
            // Log the recovery attempt
            $this->repository->update($cart->id, [
                'recovery_emails_sent' => ($cart->recovery_emails_sent ?? 0) + 1,
                'last_recovery_email_sent_at' => now(),
            ]);

            SendAbandonedCartEmail::dispatchNow($cart);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send abandoned cart recovery email', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get carts eligible for recovery
     */
    public function getCartsEligibleForRecovery(int $hoursThreshold = 1): array
    {
        $carts = $this->repository->getEligibleForRecovery($hoursThreshold);

        return $carts->map(fn ($cart) => AbandonedCartData::fromModel($cart))->toArray();
    }

    /**
     * Auto-schedule recovery emails for eligible carts
     */
    public function autoScheduleRecoveryEmails(): int
    {
        $eligibleCarts = $this->getCartsEligibleForRecovery();
        $scheduled = 0;

        foreach ($eligibleCarts as $cartData) {
            $cart = AbandonedCart::find($cartData->id);
            if ($cart && (! $cart->recovery_emails_sent || $cart->recovery_emails_sent < 3)) {
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

        if ($stats['total_abandoned'] === 0) {
            return 0.0;
        }

        return round(($stats['recovered'] / $stats['total_abandoned']) * 100, 2);
    }

    /**
     * Get top abandoned products
     */
    public function getTopAbandonedProducts(int $limit = 10): array
    {
        return $this->repository->getTopAbandonedProducts($limit);
    }

    /**
     * Clean old abandoned carts
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
     * Get potential revenue from recovery
     */
    public function getPotentialRecoveryRevenue(): float
    {
        $eligibleCarts = AbandonedCart::query()
            ->where('recovered', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        return $eligibleCarts->sum('total_amount');
    }

    /**
     * Generate recovery link for cart
     */
    public function generateRecoveryLink(AbandonedCart $cart): string
    {
        $token = base64_encode($cart->id.'|'.$cart->email.'|'.now()->timestamp);

        return route('cart.recover', ['token' => $token]);
    }

    /**
     * Validate recovery token
     */
    public function validateRecoveryToken(string $token): ?AbandonedCart
    {
        try {
            $decoded = base64_decode($token);
            [$cartId, $email, $timestamp] = explode('|', $decoded);

            // Token expires after 7 days
            if ((now()->timestamp - $timestamp) > 604800) {
                return null;
            }

            return AbandonedCart::where('id', $cartId)
                ->where('email', $email)
                ->where('recovered', false)
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}
