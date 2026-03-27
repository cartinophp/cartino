<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Services\FidelityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FidelityController extends ApiController
{
    public function __construct(
        protected FidelityService $fidelityService,
    ) {}

    /**
     * Ottiene le informazioni della fidelity card del customer autenticato
     */
    public function show(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer || ! $this->fidelityService->isEnabled()) {
            return response()->json(['message' => 'Fidelity system not available'], 404);
        }

        $card = $customer->fidelityCard;

        if (! $card) {
            return response()->json([
                'fidelity_card' => null,
                'can_create' => true,
                'system_enabled' => $this->fidelityService->isEnabled(),
                'points_enabled' => $this->fidelityService->arePointsEnabled(),
            ]);
        }

        $statistics = $this->fidelityService->getCardStatistics($card);
        $recentTransactions = $this->fidelityService->getRecentTransactions($card, 10);

        return response()->json([
            'fidelity_card' => $statistics,
            'recent_transactions' => $recentTransactions,
            'system_enabled' => $this->fidelityService->isEnabled(),
            'points_enabled' => $this->fidelityService->arePointsEnabled(),
        ]);
    }

    /**
     * Crea una nuova fidelity card per il customer autenticato
     */
    public function store(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer || ! $this->fidelityService->isEnabled()) {
            return response()->json(['message' => 'Fidelity system not available'], 404);
        }

        if ($customer->fidelityCard) {
            return response()->json(['message' => 'Fidelity card already exists'], 409);
        }

        $card = $this->fidelityService->createFidelityCard($customer);
        $statistics = $this->fidelityService->getCardStatistics($card);

        return response()->json([
            'message' => 'Fidelity card created successfully',
            'fidelity_card' => $statistics,
        ], 201);
    }

    /**
     * Ottiene le transazioni della fidelity card
     */
    public function transactions(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer || ! $customer->fidelityCard) {
            return response()->json(['message' => 'Fidelity card not found'], 404);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $type = $request->get('type'); // earned, redeemed, expired

        $query = $customer->fidelityCard->transactions()->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }

    /**
     * Trova una fidelity card per numero
     */
    public function findByCardNumber(Request $request): JsonResponse
    {
        $request->validate([
            'card_number' => 'required|string|max:50',
        ]);

        if (! $this->fidelityService->isEnabled()) {
            return response()->json(['message' => 'Fidelity system not available'], 404);
        }

        $card = $this->fidelityService->findCardByNumber($request->card_number);

        if (! $card) {
            return response()->json(['message' => 'Fidelity card not found'], 404);
        }

        $statistics = $this->fidelityService->getCardStatistics($card);

        return response()->json([
            'fidelity_card' => $statistics,
            'customer' => [
                'id' => $card->customer->id,
                'name' => $card->customer->full_name,
                'email' => $card->customer->email,
            ],
        ]);
    }

    /**
     * Calcola i punti per un importo
     */
    public function calculatePoints(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
        ]);

        if (! $this->fidelityService->arePointsEnabled()) {
            return response()->json(['message' => 'Fidelity points system not available'], 404);
        }

        $customer = $request->user();
        $amount = $request->amount;
        $currency = $request->currency;

        if ($customer && $customer->fidelityCard) {
            $points = $customer->fidelityCard->calculatePointsForAmount($amount, $currency);
            $currentTier = $customer->fidelityCard->getCurrentTier();
            $nextTier = $customer->fidelityCard->getNextTier();
        } else {
            $points = $this->fidelityService->calculatePointsForAmount($amount, $currency);
            $currentTier = ['threshold' => 0, 'rate' => 1];
            $nextTier = null;
        }

        return response()->json([
            'amount' => $amount,
            'currency' => $currency,
            'points_earned' => $points,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
        ]);
    }

    /**
     * Riscatta punti (solo per admin/staff)
     */
    public function redeemPoints(Request $request): JsonResponse
    {
        $request->validate([
            'card_number' => 'required|string|max:50',
            'points' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        if (! $this->fidelityService->arePointsEnabled()) {
            return response()->json(['message' => 'Fidelity points system not available'], 404);
        }

        $card = $this->fidelityService->findCardByNumber($request->card_number);

        if (! $card) {
            return response()->json(['message' => 'Fidelity card not found'], 404);
        }

        if (! $this->fidelityService->canRedeemPoints($card, $request->points)) {
            return response()->json(['message' => 'Insufficient points or below minimum redemption threshold'], 400);
        }

        try {
            $transaction = $this->fidelityService->redeemPoints(
                $card,
                $request->points,
                $request->reason ?? 'Manual redemption',
            );

            $updatedStatistics = $this->fidelityService->getCardStatistics($card);

            return response()->json([
                'message' => 'Points redeemed successfully',
                'transaction' => $transaction,
                'fidelity_card' => $updatedStatistics,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Ottiene la configurazione del sistema fedeltà (informazioni pubbliche)
     */
    public function configuration(): JsonResponse
    {
        if (! $this->fidelityService->isEnabled()) {
            return response()->json(['message' => 'Fidelity system not available'], 404);
        }

        $config = $this->fidelityService->getConfiguration();

        // Rimuovi informazioni sensibili e restituisci solo quelle pubbliche
        $publicConfig = [
            'enabled' => $config['enabled'] ?? false,
            'points' => [
                'enabled' => $config['points']['enabled'] ?? false,
                'currency_base' => $config['points']['currency_base'] ?? 'EUR',
                'conversion_rules' => [
                    'tiers' => $config['points']['conversion_rules']['tiers'] ?? [],
                ],
                'expiration' => [
                    'enabled' => $config['points']['expiration']['enabled'] ?? false,
                    'months' => $config['points']['expiration']['months'] ?? 12,
                ],
                'redemption' => [
                    'min_points' => $config['points']['redemption']['min_points'] ?? 100,
                    'points_to_currency_rate' => $config['points']['redemption']['points_to_currency_rate'] ?? 0.01,
                ],
            ],
        ];

        return response()->json($publicConfig);
    }
}
