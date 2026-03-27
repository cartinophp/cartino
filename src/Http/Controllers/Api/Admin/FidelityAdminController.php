<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api\Admin;

use Cartino\Http\Controllers\Api\ApiController;
use Cartino\Models\FidelityCard;
use Cartino\Models\FidelityTransaction;
use Cartino\Services\FidelityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FidelityAdminController extends ApiController
{
    public function __construct(
        protected FidelityService $fidelityService,
    ) {}

    /**
     * Lista tutte le fidelity cards con paginazione e filtri
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 25), 100);
        $search = $request->get('search');
        $isActive = $request->get('is_active');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = FidelityCard::with(['customer'])->when($search, function ($q) use ($search) {
            $q->where('card_number', 'like', "%{$search}%")->orWhereHas('customer', function ($customerQuery) use (
                $search,
            ) {
                $customerQuery->where('first_name', 'like', "%{$search}%")->orWhere(
                    'last_name',
                    'like',
                    "%{$search}%",
                )->orWhere('email', 'like', "%{$search}%");
            });
        })->when($isActive !== null, function ($q) use ($isActive) {
            $q->where('is_active', (bool) $isActive);
        })->orderBy($sortBy, $sortOrder);

        $cards = $query->paginate($perPage);

        // Aggiungi statistiche per ogni card
        $cards
            ->getCollection()
            ->transform(function ($card) {
                return array_merge($card->toArray(), ['statistics' => $this->fidelityService->getCardStatistics($card)]);
            });

        return response()->json($cards);
    }

    /**
     * Mostra una fidelity card specifica
     */
    public function show(FidelityCard $card): JsonResponse
    {
        $card->load([
            'customer',
            'transactions' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(20);
            },
        ]);

        $statistics = $this->fidelityService->getCardStatistics($card);

        return response()->json([
            'card' => $card,
            'statistics' => $statistics,
            'recent_transactions' => $card->transactions,
        ]);
    }

    /**
     * Aggiorna una fidelity card
     */
    public function update(Request $request, FidelityCard $card): JsonResponse
    {
        $request->validate([
            'is_active' => 'sometimes|boolean',
            'meta' => 'sometimes|array',
        ]);

        $card->update($request->only(['is_active', 'meta']));

        $statistics = $this->fidelityService->getCardStatistics($card);

        return response()->json([
            'message' => 'Fidelity card updated successfully',
            'card' => $card,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Aggiungi punti manualmente a una fidelity card
     */
    public function addPoints(Request $request, FidelityCard $card): JsonResponse
    {
        $request->validate([
            'points' => 'required|integer',
            'reason' => 'required|string|max:255',
            'type' => 'sometimes|in:earned,adjusted',
        ]);

        $points = $request->points;
        $reason = $request->reason;
        $type = $request->get('type', 'adjusted');

        // Se i punti sono negativi, li considera come redemption
        if ($points < 0) {
            if ($card->available_points < abs($points)) {
                return response()->json(['message' => 'Insufficient points'], 400);
            }

            $transaction = $card->transactions()->create([
                'type' => 'adjusted',
                'points' => $points,
                'description' => $reason,
            ]);

            $card->decrement('available_points', abs($points));
        } else {
            // Punti positivi
            $transaction = $card->transactions()->create([
                'type' => $type,
                'points' => $points,
                'description' => $reason,
                'expires_at' => $type === 'earned' ? $card->calculatePointsExpiration() : null,
            ]);

            $card->increment('total_points', $points);
            $card->increment('available_points', $points);

            if ($type === 'earned') {
                $card->increment('total_earned', $points);
            }
        }

        $card->update(['last_activity_at' => now()]);
        $statistics = $this->fidelityService->getCardStatistics($card);

        return response()->json([
            'message' => 'Points updated successfully',
            'transaction' => $transaction,
            'card' => $card,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Ottieni statistiche generali del sistema di fedeltà
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_cards' => FidelityCard::count(),
            'active_cards' => FidelityCard::where('is_active', true)->count(),
            'total_points_issued' => FidelityCard::sum('total_earned'),
            'total_points_redeemed' => FidelityCard::sum('total_redeemed'),
            'total_points_available' => FidelityCard::sum('available_points'),
            'total_spent_amount' => FidelityCard::sum('total_spent_amount'),
        ];

        // Statistiche per periodo (ultimo mese)
        $lastMonth = now()->subMonth();
        $monthlyStats = [
            'new_cards_last_month' => FidelityCard::where('created_at', '>=', $lastMonth)->count(),
            'points_earned_last_month' => FidelityTransaction::where('type', 'earned')
                ->where('created_at', '>=', $lastMonth)
                ->sum('points'),
            'points_redeemed_last_month' => FidelityTransaction::where('type', 'redeemed')
                ->where('created_at', '>=', $lastMonth)
                ->sum(DB::raw('ABS(points)')),
        ];

        // Top tier dei clienti
        $topTiers = FidelityCard::selectRaw('
                CASE 
                    WHEN total_spent_amount >= 1000 THEN "Platinum"
                    WHEN total_spent_amount >= 500 THEN "Gold"
                    WHEN total_spent_amount >= 100 THEN "Silver"
                    ELSE "Bronze"
                END as tier,
                COUNT(*) as count
            ')
            ->where('is_active', true)
            ->groupBy('tier')
            ->get();

        // Punti in scadenza
        $expiringPoints = FidelityTransaction::where('type', 'earned')
            ->where('expired', false)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->sum('points');

        return response()->json([
            'general' => $stats,
            'monthly' => $monthlyStats,
            'tiers' => $topTiers,
            'expiring_points_30_days' => $expiringPoints,
            'system_configuration' => $this->fidelityService->getConfiguration(),
        ]);
    }

    /**
     * Forza la scadenza dei punti
     */
    public function expirePoints(Request $request): JsonResponse
    {
        $dryRun = $request->boolean('dry_run', false);

        if ($dryRun) {
            // Conta quanti punti scadrebbero
            $expiredTransactions = FidelityTransaction::where('type', 'earned')
                ->where('expired', false)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->get();

            $totalExpiredPoints = $expiredTransactions->sum('points');
            $affectedCards = $expiredTransactions->unique('fidelity_card_id')->count();

            return response()->json([
                'dry_run' => true,
                'total_expired_points' => $totalExpiredPoints,
                'affected_cards' => $affectedCards,
                'expired_transactions' => $expiredTransactions->count(),
            ]);
        }

        $expiredCount = $this->fidelityService->expirePoints();

        return response()->json([
            'message' => 'Points expiration completed',
            'processed_cards' => $expiredCount,
        ]);
    }
}
