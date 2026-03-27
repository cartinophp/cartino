<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Repositories\TaxRateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRateController extends ApiController
{
    public function __construct(
        protected TaxRateRepository $taxRateRepository,
    ) {}

    /**
     * Display a listing of tax rates
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'type', 'country']);
        $perPage = $request->get('per_page', 25);

        $taxRates = $this->taxRateRepository->findAll($filters, $perPage);

        return response()->json([
            'data' => $taxRates->items(),
            'meta' => [
                'current_page' => $taxRates->currentPage(),
                'last_page' => $taxRates->lastPage(),
                'per_page' => $taxRates->perPage(),
                'total' => $taxRates->total(),
                'from' => $taxRates->firstItem(),
                'to' => $taxRates->lastItem(),
            ],
            'links' => [
                'first' => $taxRates->url(1),
                'last' => $taxRates->url($taxRates->lastPage()),
                'prev' => $taxRates->previousPageUrl(),
                'next' => $taxRates->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created tax rate
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:tax_rates,code',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'is_compound' => 'boolean',
            'is_inclusive' => 'boolean',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'states' => 'nullable|array',
            'states.*' => 'string',
            'postcodes' => 'nullable|array',
            'postcodes.*' => 'string',
            'product_categories' => 'nullable|array',
            'product_categories.*' => 'integer|exists:categories,id',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string',
        ]);

        try {
            $taxRate = $this->taxRateRepository->create($validated);

            return response()->json([
                'message' => 'Aliquota fiscale creata con successo',
                'data' => $taxRate,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la creazione dell\'aliquota fiscale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified tax rate
     */
    public function show(string $id): JsonResponse
    {
        try {
            $taxRate = $this->taxRateRepository->find($id);

            return response()->json([
                'data' => $taxRate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Aliquota fiscale non trovata',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified tax rate
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:tax_rates,code,'.$id,
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'is_compound' => 'boolean',
            'is_inclusive' => 'boolean',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'states' => 'nullable|array',
            'states.*' => 'string',
            'postcodes' => 'nullable|array',
            'postcodes.*' => 'string',
            'product_categories' => 'nullable|array',
            'product_categories.*' => 'integer|exists:categories,id',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string',
        ]);

        try {
            $taxRate = $this->taxRateRepository->update($id, $validated);

            return response()->json([
                'message' => 'Aliquota fiscale aggiornata con successo',
                'data' => $taxRate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento dell\'aliquota fiscale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified tax rate
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->taxRateRepository->delete($id);

            return response()->json([
                'message' => 'Aliquota fiscale eliminata con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione dell\'aliquota fiscale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get applicable tax rates for location and amount
     */
    public function getApplicable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => 'required|string|size:2',
            'state' => 'nullable|string',
            'postcode' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'product_category_id' => 'nullable|integer|exists:categories,id',
        ]);

        try {
            $taxRates = $this->taxRateRepository->getApplicableRates(
                $validated['country'],
                $validated['state'] ?? null,
                $validated['postcode'] ?? null,
                $validated['amount'],
                $validated['product_category_id'] ?? null,
            );

            return response()->json([
                'data' => $taxRates,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero delle aliquote fiscali',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'country' => 'required|string|size:2',
            'state' => 'nullable|string',
            'postcode' => 'nullable|string',
            'product_category_id' => 'nullable|integer|exists:categories,id',
            'is_inclusive' => 'boolean',
        ]);

        try {
            $calculation = $this->taxRateRepository->calculateTax(
                $validated['amount'],
                $validated['country'],
                $validated['state'] ?? null,
                $validated['postcode'] ?? null,
                $validated['product_category_id'] ?? null,
                $validated['is_inclusive'] ?? false,
            );

            return response()->json([
                'data' => $calculation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il calcolo delle tasse',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle tax rate status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $taxRate = $this->taxRateRepository->toggleStatus($id);

            return response()->json([
                'message' => 'Stato dell\'aliquota fiscale aggiornato',
                'data' => $taxRate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento dello stato',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle bulk actions
     */
    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,enable,disable,export',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:tax_rates,id',
        ]);

        try {
            $result = $this->taxRateRepository->bulkAction($validated['action'], $validated['ids']);

            return response()->json([
                'message' => "Azione '{$validated['action']}' eseguita su {$result['count']} aliquote fiscali",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'esecuzione dell\'azione bulk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
