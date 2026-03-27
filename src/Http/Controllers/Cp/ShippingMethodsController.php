<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Models\ShippingMethod;
use Cartino\Repositories\ShippingMethodRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShippingMethodsController extends Controller
{
    protected ShippingMethodRepository $shippingMethodRepository;

    public function __construct(ShippingMethodRepository $shippingMethodRepository)
    {
        $this->shippingMethodRepository = $shippingMethodRepository;
    }

    /**
     * Display shipping methods
     */
    public function index(Request $request): Response
    {
        $page = Page::make('Metodi di Spedizione')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Impostazioni', '/cp/settings')
            ->breadcrumb('Metodi di Spedizione');

        $filters = $request->only(['search', 'is_enabled', 'type', 'zone', 'sort', 'direction', 'page']);

        $shippingMethods = $this->shippingMethodRepository->findAll($filters, 25);
        $zones = $this->shippingMethodRepository->getShippingZones();
        $types = $this->shippingMethodRepository->getShippingTypes();

        return Inertia::render('settings-shipping-methods', [
            'page' => $page->compile(),
            'shippingMethods' => $shippingMethods,
            'zones' => $zones,
            'types' => $types,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a new shipping method
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:shipping_methods,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:flat_rate,free,local_pickup,weight_based,zone_based',
            'cost' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_weight' => 'nullable|numeric|min:0',
            'processing_time' => 'nullable|string|max:100',
            'zones' => 'nullable|array',
            'zones.*' => 'string|size:2',
            'is_taxable' => 'boolean',
            'sort_order' => 'integer|min:0',
            'settings' => 'nullable|array',
        ]);

        try {
            $shippingMethod = $this->shippingMethodRepository->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Metodo di spedizione creato con successo',
                'shippingMethod' => $shippingMethod,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione del metodo di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified shipping method
     */
    public function show(ShippingMethod $shippingMethod): Response
    {
        $page = Page::make('Dettagli Metodo di Spedizione')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Impostazioni', '/cp/settings')
            ->breadcrumb('Metodi di Spedizione', '/cp/settings/shipping-methods')
            ->breadcrumb($shippingMethod->name);

        return Inertia::render('shipping-method-show', [
            'page' => $page->compile(),
            'shippingMethod' => $shippingMethod,
        ]);
    }

    /**
     * Update the specified shipping method
     */
    public function update(Request $request, ShippingMethod $shippingMethod): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:shipping_methods,slug,'.$shippingMethod->id,
            'description' => 'nullable|string',
            'type' => 'required|in:flat_rate,free,local_pickup,weight_based,zone_based',
            'cost' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_weight' => 'nullable|numeric|min:0',
            'processing_time' => 'nullable|string|max:100',
            'zones' => 'nullable|array',
            'zones.*' => 'string|size:2',
            'is_taxable' => 'boolean',
            'sort_order' => 'integer|min:0',
            'settings' => 'nullable|array',
        ]);

        try {
            $updatedShippingMethod = $this->shippingMethodRepository->update($shippingMethod->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Metodo di spedizione aggiornato con successo',
                'shippingMethod' => $updatedShippingMethod,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento del metodo di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle shipping method status
     */
    public function toggleStatus(ShippingMethod $shippingMethod): JsonResponse
    {
        try {
            $updatedShippingMethod = $this->shippingMethodRepository->toggleStatus($shippingMethod->id);

            return response()->json([
                'success' => true,
                'message' => 'Stato metodo di spedizione aggiornato',
                'shippingMethod' => $updatedShippingMethod,
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
     * Update sort order for multiple shipping methods
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_methods' => 'required|array',
            'shipping_methods.*.id' => 'required|integer|exists:shipping_methods,id',
            'shipping_methods.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            $this->shippingMethodRepository->updateSortOrder($validated['shipping_methods']);

            return response()->json([
                'success' => true,
                'message' => 'Ordine metodi di spedizione aggiornato',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento dell\'ordine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate shipping cost
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'method_id' => 'required|integer|exists:shipping_methods,id',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'dimensions.length' => 'numeric|min:0',
            'dimensions.width' => 'numeric|min:0',
            'dimensions.height' => 'numeric|min:0',
            'destination' => 'required|array',
            'destination.country' => 'required|string|size:2',
            'destination.state' => 'nullable|string',
            'destination.zip_code' => 'nullable|string',
            'order_total' => 'nullable|numeric|min:0',
        ]);

        try {
            $cost = $this->shippingMethodRepository->calculateShippingCost(
                $validated['method_id'],
                $validated['destination'],
                $validated['weight'] ?? 0,
                $validated['dimensions'] ?? [],
                $validated['order_total'] ?? 0,
            );

            return response()->json([
                'success' => true,
                'cost' => $cost,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il calcolo della spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate a shipping method
     */
    public function duplicate(ShippingMethod $shippingMethod): JsonResponse
    {
        try {
            $duplicatedShippingMethod = $this->shippingMethodRepository->duplicate($shippingMethod->id);

            return response()->json([
                'success' => true,
                'message' => 'Metodo di spedizione duplicato con successo',
                'shippingMethod' => $duplicatedShippingMethod,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la duplicazione del metodo di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified shipping method
     */
    public function destroy(ShippingMethod $shippingMethod): JsonResponse
    {
        try {
            $this->shippingMethodRepository->delete($shippingMethod->id);

            return response()->json([
                'success' => true,
                'message' => 'Metodo di spedizione eliminato con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione del metodo di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
