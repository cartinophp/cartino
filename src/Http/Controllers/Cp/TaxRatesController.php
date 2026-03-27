<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Models\TaxRate;
use Cartino\Repositories\TaxRateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxRatesController extends Controller
{
    protected TaxRateRepository $taxRateRepository;

    public function __construct(TaxRateRepository $taxRateRepository)
    {
        $this->taxRateRepository = $taxRateRepository;
    }

    /**
     * Display tax rates
     */
    public function index(Request $request): Response
    {
        $page = Page::make('Aliquote Fiscali')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Impostazioni', '/cp/settings')
            ->breadcrumb('Aliquote Fiscali');

        $filters = $request->only(['search', 'country', 'state', 'is_active', 'sort', 'direction', 'page']);

        $taxRates = $this->taxRateRepository->findAll($filters, 25);
        $countries = $this->taxRateRepository->getCountries();
        $taxZones = $this->taxRateRepository->getTaxZones();

        return Inertia::render('settings-tax-rates', [
            'page' => $page->compile(),
            'taxRates' => $taxRates,
            'countries' => $countries,
            'taxZones' => $taxZones,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a new tax rate
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'description' => 'nullable|string',
            'country' => 'required|string|size:2',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'is_compound' => 'boolean',
            'is_shipping' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            'class' => 'nullable|string|max:100',
            'tax_zone_id' => 'nullable|integer|exists:tax_zones,id',
        ]);

        try {
            $taxRate = $this->taxRateRepository->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Aliquota fiscale creata con successo',
                'taxRate' => $taxRate,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'aliquota fiscale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified tax rate
     */
    public function show(TaxRate $taxRate): Response
    {
        $page = Page::make('Dettagli Aliquota Fiscale')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Impostazioni', '/cp/settings')
            ->breadcrumb('Aliquote Fiscali', '/cp/settings/tax-rates')
            ->breadcrumb($taxRate->name);

        return Inertia::render('tax-rate-show', [
            'page' => $page->compile(),
            'taxRate' => $taxRate->load('taxZone'),
        ]);
    }

    /**
     * Update the specified tax rate
     */
    public function update(Request $request, TaxRate $taxRate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'description' => 'nullable|string',
            'country' => 'required|string|size:2',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'is_compound' => 'boolean',
            'is_shipping' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            'class' => 'nullable|string|max:100',
            'tax_zone_id' => 'nullable|integer|exists:tax_zones,id',
        ]);

        try {
            $updatedTaxRate = $this->taxRateRepository->update($taxRate->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Aliquota fiscale aggiornata con successo',
                'taxRate' => $updatedTaxRate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento dell\'aliquota fiscale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle tax rate status
     */
    public function toggleStatus(TaxRate $taxRate): JsonResponse
    {
        try {
            $updatedTaxRate = $this->taxRateRepository->toggleStatus($taxRate->id);

            return response()->json([
                'success' => true,
                'message' => 'Stato aliquota fiscale aggiornato',
                'taxRate' => $updatedTaxRate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento dello stato',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update priorities for multiple tax rates
     */
    public function updatePriorities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tax_rates' => 'required|array',
            'tax_rates.*.id' => 'required|integer|exists:tax_rates,id',
            'tax_rates.*.priority' => 'required|integer|min:0',
        ]);

        try {
            $this->taxRateRepository->updatePriorities($validated['tax_rates']);

            return response()->json([
                'success' => true,
                'message' => 'Priorità aliquote fiscali aggiornate',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento delle priorità',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate a tax rate
     */
    public function duplicate(TaxRate $taxRate): JsonResponse
    {
        try {
            $duplicatedTaxRate = $this->taxRateRepository->duplicate($taxRate->id);

            return response()->json([
                'success' => true,
                'message' => 'Aliquota fiscale duplicata con successo',
                'taxRate' => $duplicatedTaxRate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la duplicazione dell\'aliquota fiscale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate tax for given parameters
     */
    public function calculateTax(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'country' => 'required|string|size:2',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'product_class' => 'nullable|string|max:100',
            'shipping_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $taxCalculation = $this->taxRateRepository->calculateTax(
                $validated['amount'],
                $validated['country'],
                $validated['state'] ?? null,
                $validated['zip_code'] ?? null,
                $validated['city'] ?? null,
                $validated['product_class'] ?? null,
                $validated['shipping_amount'] ?? 0,
            );

            return response()->json([
                'success' => true,
                'calculation' => $taxCalculation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il calcolo delle tasse',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified tax rate
     */
    public function destroy(TaxRate $taxRate): JsonResponse
    {
        try {
            $this->taxRateRepository->delete($taxRate->id);

            return response()->json([
                'success' => true,
                'message' => 'Aliquota fiscale eliminata con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione dell\'aliquota fiscale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
