<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Http\Controllers\Controller;
use Cartino\Models\AbandonedCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AbandonedCartController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AbandonedCart::with(['customer', 'recoveredOrder'])->latest('abandoned_at');

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'recovered') {
                $query->recovered();
            } elseif ($request->status === 'not_recovered') {
                $query->notRecovered();
            }
        }

        if ($request->filled('date_from')) {
            $query->where('abandoned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('abandoned_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', "%{$request->search}%")->orWhereHas(
                    'customer',
                    function ($customerQuery) use ($request) {
                        $customerQuery->where('name', 'like', "%{$request->search}%");
                    },
                );
            });
        }

        $abandonedCarts = $query->paginate(15)->withQueryString();

        // Calculate stats
        $stats = [
            'total' => AbandonedCart::count(),
            'recovered' => AbandonedCart::recovered()->count(),
            'not_recovered' => AbandonedCart::notRecovered()->count(),
            'total_value' => AbandonedCart::notRecovered()->sum('total_amount'),
            'recovery_rate' => AbandonedCart::count() > 0
                ? round((AbandonedCart::recovered()->count() / AbandonedCart::count()) * 100, 2)
                : 0,
        ];

        return Inertia::render('CP/AbandonedCarts/index', [
            'abandonedCarts' => $abandonedCarts,
            'stats' => $stats,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function show(AbandonedCart $abandonedCart): Response
    {
        $abandonedCart->load(['customer', 'recoveredOrder']);

        return Inertia::render('CP/AbandonedCarts/Show', [
            'abandonedCart' => $abandonedCart,
        ]);
    }

    public function sendRecoveryEmail(AbandonedCart $abandonedCart): JsonResponse
    {
        if (! $abandonedCart->canSendRecoveryEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send recovery email for this cart.',
            ], 422);
        }

        try {
            // Send recovery email
            // Mail::to($abandonedCart->email)->send(new CartRecoveryMail($abandonedCart));

            $abandonedCart->incrementRecoveryEmails();

            return response()->json([
                'success' => true,
                'message' => 'Recovery email sent successfully.',
                'abandonedCart' => $abandonedCart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send recovery email.',
            ], 500);
        }
    }

    public function bulkSendRecoveryEmails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_ids' => 'required|array',
            'cart_ids.*' => 'exists:abandoned_carts,id',
        ]);

        $carts = AbandonedCart::whereIn('id', $validated['cart_ids'])
            ->get()
            ->filter(fn ($cart) => $cart->canSendRecoveryEmail());

        $successCount = 0;
        $errors = [];

        foreach ($carts as $cart) {
            try {
                // Send recovery email
                // Mail::to($cart->email)->send(new CartRecoveryMail($cart));

                $cart->incrementRecoveryEmails();
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to send email for cart {$cart->id}";
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Recovery emails sent to {$successCount} customers.",
            'errors' => $errors,
        ]);
    }

    public function markAsRecovered(Request $request, AbandonedCart $abandonedCart): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $abandonedCart->markAsRecovered($validated['order_id']);

        return response()->json([
            'success' => true,
            'message' => 'Cart marked as recovered.',
            'abandonedCart' => $abandonedCart,
        ]);
    }

    public function destroy(AbandonedCart $abandonedCart): JsonResponse
    {
        $abandonedCart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Abandoned cart deleted successfully.',
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_ids' => 'required|array',
            'cart_ids.*' => 'exists:abandoned_carts,id',
        ]);

        $deletedCount = AbandonedCart::whereIn('id', $validated['cart_ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} abandoned carts deleted successfully.",
        ]);
    }
}
