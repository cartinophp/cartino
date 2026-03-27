<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Repositories\ShippingMethodRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingMethodController extends ApiController
{
    public function __construct(
        protected ShippingMethodRepository $shippingMethodRepository,
    ) {}

    /**
     * Display a listing of shipping methods
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_enabled', 'carrier', 'calculation_type']);
        $perPage = $request->get('per_page', 25);

        $shippingMethods = $this->shippingMethodRepository->findAll($filters, $perPage);

        return response()->json([
            'data' => $shippingMethods->items(),
            'meta' => [
                'current_page' => $shippingMethods->currentPage(),
                'last_page' => $shippingMethods->lastPage(),
                'per_page' => $shippingMethods->perPage(),
                'total' => $shippingMethods->total(),
                'from' => $shippingMethods->firstItem(),
                'to' => $shippingMethods->lastItem(),
            ],
            'links' => [
                'first' => $shippingMethods->url(1),
                'last' => $shippingMethods->url($shippingMethods->lastPage()),
                'prev' => $shippingMethods->previousPageUrl(),
                'next' => $shippingMethods->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created shipping method
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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
                'message' => 'Metodo di spedizione creato con successo',
                'data' => $shippingMethod,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la creazione del metodo di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified shipping method
     */
    public function show(string $id): JsonResponse
    {
        try {
            $shippingMethod = $this->shippingMethodRepository->find($id);

            return response()->json([
                'data' => $shippingMethod,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Metodo di spedizione non trovato',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified shipping method
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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
            $shippingMethod = $this->shippingMethodRepository->update($id, $validated);

            return response()->json([
                'message' => 'Metodo di spedizione aggiornato con successo',
                'data' => $shippingMethod,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento del metodo di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified shipping method
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->shippingMethodRepository->delete($id);

            return response()->json([
                'message' => 'Metodo di spedizione eliminato con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione del metodo di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get enabled shipping methods
     */
    public function enabled(): JsonResponse
    {
        try {
            $shippingMethods = $this->shippingMethodRepository->getEnabled();

            return response()->json([
                'data' => $shippingMethods,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei metodi di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get shipping methods available for location
     */
    public function availableForLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => 'required|string|size:2',
            'state' => 'nullable|string',
        ]);

        try {
            $shippingMethods = $this->shippingMethodRepository->getAvailableForLocation(
                $validated['country'],
                $validated['state'] ?? null,
            );

            return response()->json([
                'data' => $shippingMethods,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei metodi di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate shipping cost
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'method_id' => 'required|integer|exists:shipping_methods,id',
            'weight' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'item_count' => 'nullable|integer|min:0',
            'destination' => 'required|array',
            'destination.country' => 'required|string|size:2',
            'destination.state' => 'nullable|string',
            'destination.zip_code' => 'nullable|string',
        ]);

        try {
            $cartData = [
                'weight' => $validated['weight'] ?? 0,
                'subtotal' => $validated['subtotal'] ?? 0,
                'item_count' => $validated['item_count'] ?? 0,
                'destination' => $validated['destination'],
            ];

            $cost = $this->shippingMethodRepository->calculateShippingCost($validated['method_id'], $cartData);

            return response()->json([
                'data' => [
                    'cost' => $cost,
                    'method_id' => $validated['method_id'],
                    'formatted_cost' => '€'.number_format($cost, 2),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il calcolo del costo di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle shipping method status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $shippingMethod = $this->shippingMethodRepository->toggleStatus($id);

            return response()->json([
                'message' => 'Stato del metodo di spedizione aggiornato',
                'data' => $shippingMethod,
            ]);
        } catch (\Exception $e) {
            return response()->json([
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
                'message' => 'Ordine dei metodi di spedizione aggiornato con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento dell\'ordine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get shipping zones
     */
    public function zones(): JsonResponse
    {
        try {
            $zones = $this->shippingMethodRepository->getShippingZones();

            return response()->json([
                'data' => $zones,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero delle zone di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get shipping types
     */
    public function types(): JsonResponse
    {
        try {
            $types = $this->shippingMethodRepository->getShippingTypes();

            return response()->json([
                'data' => $types,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei tipi di spedizione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
