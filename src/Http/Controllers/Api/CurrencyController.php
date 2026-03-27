<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Models\Currency;
use Cartino\Repositories\CurrencyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends ApiController
{
    public function __construct(
        private readonly CurrencyRepository $currencyRepository,
    ) {}

    /**
     * Display a listing of currencies
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_enabled']);
        $perPage = $request->get('per_page', 25);

        $currencies = $this->currencyRepository->findAll($filters, $perPage);

        return $this->paginatedResponse($currencies);
    }

    /**
     * Store a newly created currency
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3|unique:currencies,code',
            'symbol' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0',
            'precision' => 'required|integer|min:0|max:8',
            'is_default' => 'boolean',
        ]);

        // Ensure only one default currency
        if ($validated['is_default'] ?? false) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        try {
            $currency = Currency::create($validated);

            return response()->json([
                'message' => 'Valuta creata con successo',
                'data' => $currency,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la creazione della valuta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified currency
     */
    public function show(string $id): JsonResponse
    {
        try {
            $currency = Currency::findOrFail($id);

            return response()->json([
                'data' => $currency,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Valuta non trovata',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified currency
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3|unique:currencies,code,'.$id,
            'symbol' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0',
            'precision' => 'required|integer|min:0|max:8',
            'is_default' => 'boolean',
        ]);

        try {
            $currency = Currency::findOrFail($id);

            // Ensure only one default currency
            if ($validated['is_default'] ?? false) {
                Currency::where('id', '!=', $id)->where('is_default', true)->update(['is_default' => false]);
            }

            $currency->update($validated);

            return response()->json([
                'message' => 'Valuta aggiornata con successo',
                'data' => $currency->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento della valuta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified currency
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $currency = Currency::findOrFail($id);

            // Prevent deletion of default currency
            if ($currency->is_default) {
                return response()->json([
                    'message' => 'Impossibile eliminare la valuta predefinita',
                ], 422);
            }

            // Check if currency is used in carts or orders
            if ($currency->carts()->exists() || $currency->orders()->exists()) {
                return response()->json([
                    'message' => 'Impossibile eliminare la valuta con carrelli o ordini associati',
                ], 422);
            }

            $currency->delete();

            return response()->json([
                'message' => 'Valuta eliminata con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione della valuta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get enabled currencies only
     */
    public function enabled(): JsonResponse
    {
        try {
            $currencies = Currency::where('is_enabled', true)
                ->orderBy('is_default', 'desc')
                ->orderBy('code')
                ->get();

            return response()->json([
                'data' => $currencies,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero delle valute',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get default currency
     */
    public function default(): JsonResponse
    {
        try {
            $currency = Currency::where('is_default', true)->first();

            if (! $currency) {
                return response()->json([
                    'message' => 'Nessuna valuta predefinita trovata',
                ], 404);
            }

            return response()->json([
                'data' => $currency,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero della valuta predefinita',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set currency as default
     */
    public function setAsDefault(string $id): JsonResponse
    {
        try {
            $currency = Currency::findOrFail($id);

            // Remove default from all currencies
            Currency::where('is_default', true)->update(['is_default' => false]);

            // Set this currency as default
            $currency->update(['is_default' => true]);

            return response()->json([
                'message' => 'Valuta impostata come predefinita',
                'data' => $currency->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'impostazione della valuta predefinita',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convert amount between currencies
     */
    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from' => 'required|string|size:3|exists:currencies,code',
            'to' => 'required|string|size:3|exists:currencies,code',
        ]);

        try {
            $fromCurrency = Currency::where('code', $validated['from'])->firstOrFail();
            $toCurrency = Currency::where('code', $validated['to'])->firstOrFail();

            // Convert to base currency first, then to target currency
            $baseAmount = $validated['amount'] / $fromCurrency->rate;
            $convertedAmount = $baseAmount * $toCurrency->rate;

            return response()->json([
                'data' => [
                    'original_amount' => $validated['amount'],
                    'converted_amount' => round($convertedAmount, $toCurrency->precision),
                    'from_currency' => $fromCurrency->code,
                    'to_currency' => $toCurrency->code,
                    'rate' => $toCurrency->rate / $fromCurrency->rate,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la conversione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get exchange rates
     */
    public function rates(): JsonResponse
    {
        try {
            $currencies = Currency::where('is_enabled', true)
                ->select('code', 'rate', 'symbol')
                ->orderBy('code')
                ->get();

            return response()->json([
                'data' => $currencies,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei tassi di cambio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
