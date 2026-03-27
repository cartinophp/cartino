<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Models\PaymentGateway;
use Cartino\Repositories\PaymentGatewayRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentGatewaysController extends Controller
{
    protected PaymentGatewayRepository $paymentGatewayRepository;

    public function __construct(PaymentGatewayRepository $paymentGatewayRepository)
    {
        $this->paymentGatewayRepository = $paymentGatewayRepository;
    }

    /**
     * Display payment gateways
     */
    public function index(Request $request): Response
    {
        $page = Page::make('Gateway di Pagamento')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Impostazioni', '/cp/settings')
            ->breadcrumb('Gateway di Pagamento');

        $filters = $request->only(['search', 'is_enabled', 'provider', 'test_mode', 'sort', 'direction', 'page']);

        $gateways = $this->paymentGatewayRepository->findAll($filters, 25);
        $providers = $this->paymentGatewayRepository->getProviders();

        return Inertia::render('settings-payment-gateways', [
            'page' => $page->compile(),
            'gateways' => $gateways,
            'providers' => $providers,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a new payment gateway
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:payment_gateways,slug',
            'description' => 'nullable|string',
            'provider' => 'required|string|max:100',
            'config' => 'nullable|array',
            'is_default' => 'boolean',
            'supported_currencies' => 'nullable|array',
            'webhook_url' => 'nullable|url',
            'test_mode' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        try {
            $gateway = $this->paymentGatewayRepository->create($validated);

            // If this is set as default, update others
            if ($validated['is_default'] ?? false) {
                $this->paymentGatewayRepository->setAsDefault($gateway->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gateway di pagamento creato con successo',
                'gateway' => $gateway,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione del gateway',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payment gateway
     */
    public function show(PaymentGateway $paymentGateway): Response
    {
        $page = Page::make('Dettagli Gateway')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Impostazioni', '/cp/settings')
            ->breadcrumb('Gateway di Pagamento', '/cp/settings/payment-gateways')
            ->breadcrumb($paymentGateway->name);

        return Inertia::render('payment-gateway-show', [
            'page' => $page->compile(),
            'gateway' => $paymentGateway,
        ]);
    }

    /**
     * Update the specified payment gateway
     */
    public function update(Request $request, PaymentGateway $paymentGateway): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:payment_gateways,slug,'.$paymentGateway->id,
            'description' => 'nullable|string',
            'provider' => 'required|string|max:100',
            'config' => 'nullable|array',
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'supported_currencies' => 'nullable|array',
            'webhook_url' => 'nullable|url',
            'test_mode' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        try {
            $updatedGateway = $this->paymentGatewayRepository->update($paymentGateway->id, $validated);

            // If this is set as default, update others
            if ($validated['is_default'] ?? false) {
                $this->paymentGatewayRepository->setAsDefault($paymentGateway->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gateway di pagamento aggiornato con successo',
                'gateway' => $updatedGateway,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento del gateway',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set gateway as default
     */
    public function setDefault(PaymentGateway $paymentGateway): JsonResponse
    {
        try {
            $updatedGateway = $this->paymentGatewayRepository->setAsDefault($paymentGateway->id);

            return response()->json([
                'success' => true,
                'message' => 'Gateway impostato come predefinito',
                'gateway' => $updatedGateway,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'impostazione del gateway predefinito',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle gateway status
     */
    public function toggleStatus(PaymentGateway $paymentGateway): JsonResponse
    {
        try {
            $updatedGateway = $this->paymentGatewayRepository->toggleStatus($paymentGateway->id);

            return response()->json([
                'success' => true,
                'message' => 'Stato gateway aggiornato',
                'gateway' => $updatedGateway,
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
     * Update gateway configuration
     */
    public function updateConfig(Request $request, PaymentGateway $paymentGateway): JsonResponse
    {
        $validated = $request->validate([
            'config' => 'required|array',
        ]);

        try {
            $updatedGateway = $this->paymentGatewayRepository->updateConfig($paymentGateway->id, $validated['config']);

            return response()->json([
                'success' => true,
                'message' => 'Configurazione gateway aggiornata',
                'gateway' => $updatedGateway,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento della configurazione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gateways' => 'required|array',
            'gateways.*.id' => 'required|integer|exists:payment_gateways,id',
            'gateways.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            $this->paymentGatewayRepository->updateSortOrder($validated['gateways']);

            return response()->json([
                'success' => true,
                'message' => 'Ordine gateway aggiornato',
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
     * Remove the specified payment gateway
     */
    public function destroy(PaymentGateway $paymentGateway): JsonResponse
    {
        try {
            $this->paymentGatewayRepository->delete($paymentGateway->id);

            return response()->json([
                'success' => true,
                'message' => 'Gateway di pagamento eliminato con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione del gateway',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
