<?php

namespace Cartino\Http\Controllers\Api;

use Cartino\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomersController extends ApiController
{
    public function __construct(
        protected CustomerRepository $customerRepository,
    ) {}

    /**
     * Display a listing of customers
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'group_id', 'location']);
        $perPage = $request->get('per_page', 25);

        $customers = $this->customerRepository->findAll($filters, $perPage);

        return response()->json([
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
            ],
            'links' => [
                'first' => $customers->url(1),
                'last' => $customers->url($customers->lastPage()),
                'prev' => $customers->previousPageUrl(),
                'next' => $customers->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'customer_group_id' => 'nullable|integer|exists:customer_groups,id',
            'is_active' => 'boolean',
            'accepts_marketing' => 'boolean',
            'password' => 'required|string|min:8|confirmed',
            'addresses' => 'nullable|array',
            'addresses.*.type' => 'required|in:billing,shipping',
            'addresses.*.first_name' => 'required|string|max:255',
            'addresses.*.last_name' => 'required|string|max:255',
            'addresses.*.address_line_1' => 'required|string|max:255',
            'addresses.*.city' => 'required|string|max:255',
            'addresses.*.country_code' => 'required|string|size:2',
        ]);

        try {
            $customer = $this->customerRepository->create($validated);

            return response()->json([
                'message' => 'Cliente creato con successo',
                'data' => $customer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la creazione del cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified customer
     */
    public function show(string $id): JsonResponse
    {
        try {
            $customer = $this->customerRepository->findWithOrders($id);

            return response()->json([
                'data' => $customer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cliente non trovato',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'customer_group_id' => 'nullable|integer|exists:customer_groups,id',
            'is_active' => 'boolean',
            'accepts_marketing' => 'boolean',
        ]);

        try {
            $customer = $this->customerRepository->update($id, $validated);

            return response()->json([
                'message' => 'Cliente aggiornato con successo',
                'data' => $customer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento del cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Check if customer has orders
            $customer = $this->customerRepository->find($id);
            if ($customer->orders()->exists()) {
                return response()->json([
                    'message' => 'Impossibile eliminare il cliente con ordini associati',
                ], 422);
            }

            $this->customerRepository->delete($id);

            return response()->json([
                'message' => 'Cliente eliminato con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione del cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer orders
     */
    public function orders(Request $request, string $id): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'date_from', 'date_to']);
            $perPage = $request->get('per_page', 25);

            $orders = $this->customerRepository->getOrders($id, $filters, $perPage);

            return response()->json([
                'data' => $orders->items(),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero degli ordini',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer addresses
     */
    public function addresses(string $id): JsonResponse
    {
        try {
            $addresses = $this->customerRepository->getAddresses($id);

            return response()->json([
                'data' => $addresses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero degli indirizzi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add address to customer
     */
    public function addAddress(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:billing,shipping',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'required|string|size:2',
            'is_default' => 'boolean',
        ]);

        try {
            $address = $this->customerRepository->addAddress($id, $validated);

            return response()->json([
                'message' => 'Indirizzo aggiunto con successo',
                'data' => $address,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiunta dell\'indirizzo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer statistics
     */
    public function statistics(string $id): JsonResponse
    {
        try {
            $statistics = $this->customerRepository->getStatistics($id);

            return response()->json([
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero delle statistiche',
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
            'action' => 'required|in:delete,activate,deactivate,export',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:customers,id',
        ]);

        try {
            $result = $this->customerRepository->bulkAction($validated['action'], $validated['ids']);

            return response()->json([
                'message' => "Azione '{$validated['action']}' eseguita su {$result['count']} clienti",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'esecuzione dell\'azione bulk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer fidelity card information
     */
    public function fidelityCard(string $id): JsonResponse
    {
        try {
            $customer = $this->customerRepository->findWithFidelityCard($id);

            if (! $customer) {
                return response()->json([
                    'message' => 'Cliente non trovato',
                ], 404);
            }

            $fidelityStats = $this->customerRepository->getFidelityStatistics($id);

            return response()->json([
                'data' => $fidelityStats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei dati fidelity',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create fidelity card for customer
     */
    public function createFidelityCard(string $id): JsonResponse
    {
        try {
            $customer = $this->customerRepository->find($id);

            if (! $customer) {
                return response()->json([
                    'message' => 'Cliente non trovato',
                ], 404);
            }

            if ($customer->fidelityCard) {
                return response()->json([
                    'message' => 'Il cliente ha già una carta fedeltà',
                ], 409);
            }

            $card = $customer->getOrCreateFidelityCard();

            return response()->json([
                'message' => 'Carta fedeltà creata con successo',
                'data' => [
                    'card_number' => $card->card_number,
                    'issued_at' => $card->issued_at,
                    'is_active' => $card->is_active,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la creazione della carta fedeltà',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customers with fidelity card information
     */
    public function indexWithFidelity(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'group_id', 'has_fidelity_card', 'fidelity_tier']);
        $perPage = $request->get('per_page', 25);

        $customers = $this->customerRepository->getWithFidelityStats($filters, $perPage);

        return response()->json([
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
            ],
            'links' => [
                'first' => $customers->url(1),
                'last' => $customers->url($customers->lastPage()),
                'prev' => $customers->previousPageUrl(),
                'next' => $customers->nextPageUrl(),
            ],
        ]);
    }
}
