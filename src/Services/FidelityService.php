<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\Customer;
use Cartino\Models\FidelityCard;
use Cartino\Models\FidelityTransaction;
use Cartino\Models\Order;
use Illuminate\Support\Collection;

class FidelityService
{
    public function __construct()
    {
        //
    }

    /**
     * Verifica se il sistema di fedeltà è abilitato
     */
    public function isEnabled(): bool
    {
        return config('cartino.fidelity.enabled', false);
    }

    /**
     * Verifica se i punti fedeltà sono abilitati
     */
    public function arePointsEnabled(): bool
    {
        return $this->isEnabled() && config('cartino.fidelity.points.enabled', false);
    }

    /**
     * Crea una nuova fidelity card per un customer
     */
    public function createFidelityCard(Customer $customer): FidelityCard
    {
        if ($customer->fidelityCard) {
            return $customer->fidelityCard;
        }

        return $customer
            ->fidelityCard()
            ->create([
                'is_active' => true,
            ]);
    }

    /**
     * Trova una fidelity card per numero
     */
    public function findCardByNumber(string $cardNumber): ?FidelityCard
    {
        return FidelityCard::where('card_number', $cardNumber)->where('is_active', true)->first();
    }

    /**
     * Processa un ordine per i punti fedeltà
     */
    public function processOrderForPoints(Order $order): ?FidelityTransaction
    {
        if (! $this->arePointsEnabled() || ! $order->customer) {
            return null;
        }

        $customer = $order->customer;
        $card = $customer->getOrCreateFidelityCard();

        $points = $this->calculatePointsForOrder($order, $card);

        if ($points > 0) {
            // Aggiorna l'importo totale speso
            $card->increment('total_spent_amount', $order->total);

            return $card->addPoints($points, "Points earned from order #{$order->number}", $order->id);
        }

        return null;
    }

    /**
     * Calcola i punti per un ordine
     */
    public function calculatePointsForOrder(Order $order, ?FidelityCard $card = null): int
    {
        if (! $this->arePointsEnabled()) {
            return 0;
        }

        $card = $card ?: $order->customer?->fidelityCard;

        if (! $card) {
            // Se non c'è una card, calcola i punti basandosi sui tier di base
            return $this->calculatePointsForAmount($order->total, $order->currency);
        }

        return $card->calculatePointsForAmount($order->total, $order->currency);
    }

    /**
     * Calcola i punti per un importo senza considerare una card specifica
     */
    public function calculatePointsForAmount(float $amount, ?string $currency = null): int
    {
        $config = config('cartino.fidelity.points');

        if (! $config['enabled']) {
            return 0;
        }

        $baseCurrency = $config['currency_base'] ?? 'EUR';
        $convertedAmount = $this->convertCurrency($amount, $currency ?? $baseCurrency, $baseCurrency);

        // Usa il tier più basso per il calcolo
        $tiers = $config['conversion_rules']['tiers'] ?? [0 => 1];
        $baseRate = $tiers[0] ?? 1;

        return (int) floor($convertedAmount * $baseRate);
    }

    /**
     * Riscatta punti
     */
    public function redeemPoints(
        FidelityCard $card,
        int $points,
        ?string $reason = null,
        ?int $orderId = null,
    ): FidelityTransaction {
        if (! $this->arePointsEnabled()) {
            throw new \InvalidArgumentException('Fidelity points system is disabled.');
        }

        return $card->redeemPoints($points, $reason, $orderId);
    }

    /**
     * Ottiene il valore monetario dei punti
     */
    public function getPointsValue(int $points): float
    {
        $rate = config('cartino.fidelity.points.redemption.points_to_currency_rate', 0.01);

        return $points * $rate;
    }

    /**
     * Ottiene i punti necessari per un valore monetario
     */
    public function getPointsForValue(float $value): int
    {
        $rate = config('cartino.fidelity.points.redemption.points_to_currency_rate', 0.01);

        return (int) ceil($value / $rate);
    }

    /**
     * Verifica se è possibile riscattare i punti
     */
    public function canRedeemPoints(FidelityCard $card, int $points): bool
    {
        if (! $this->arePointsEnabled() || ! $card->is_active) {
            return false;
        }

        $minPoints = config('cartino.fidelity.points.redemption.min_points', 100);

        return $card->available_points >= $points && $points >= $minPoints;
    }

    /**
     * Ottiene le statistiche della fidelity card
     */
    public function getCardStatistics(FidelityCard $card): array
    {
        $currentTier = $card->getCurrentTier();
        $nextTier = $card->getNextTier();

        return [
            'card_number' => $card->card_number,
            'total_points' => $card->total_points,
            'available_points' => $card->available_points,
            'total_earned' => $card->total_earned,
            'total_redeemed' => $card->total_redeemed,
            'total_spent' => $card->total_spent_amount,
            'points_value' => $this->getPointsValue($card->available_points),
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'is_active' => $card->is_active,
            'issued_at' => $card->issued_at,
            'last_activity_at' => $card->last_activity_at,
        ];
    }

    /**
     * Ottiene le transazioni recenti
     */
    public function getRecentTransactions(FidelityCard $card, int $limit = 10): Collection
    {
        return $card->transactions()->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * Scade i punti scaduti
     */
    public function expirePoints(?FidelityCard $card = null): int
    {
        if (! $this->arePointsEnabled()) {
            return 0;
        }

        $expiredCount = 0;

        if ($card) {
            $card->expirePoints();
            $expiredCount = 1;
        } else {
            // Scade i punti per tutte le carte attive
            FidelityCard::active()->chunk(100, function ($cards) use (&$expiredCount) {
                foreach ($cards as $card) {
                    $card->expirePoints();
                    $expiredCount++;
                }
            });
        }

        return $expiredCount;
    }

    /**
     * Ottiene le carte con punti in scadenza
     */
    public function getCardsWithExpiringPoints(int $days = 30): Collection
    {
        return FidelityCard::active()
            ->whereHas('transactions', function ($query) use ($days) {
                $query
                    ->where('type', 'earned')
                    ->where('expired', false)
                    ->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [now(), now()->addDays($days)]);
            })
            ->with([
                'customer',
                'transactions' => function ($query) use ($days) {
                    $query->expiring($days);
                },
            ])
            ->get();
    }

    /**
     * Ottiene la configurazione del sistema
     */
    public function getConfiguration(): array
    {
        return config('cartino.fidelity', []);
    }

    /**
     * Converte valuta (implementazione semplificata)
     */
    protected function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        // Implementazione semplificata - in un caso reale si userebbe un servizio di conversione
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Per ora assumiamo che sia tutto nella stessa valuta
        // In futuro si può implementare una logica di conversione più complessa
        return $amount;
    }
}
