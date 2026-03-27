<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Models\Order;
use Cartino\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrdersController extends Controller
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request): Response
    {
        $page = Page::make('Ordini')->breadcrumb('Home', '/cp')->breadcrumb('Ordini');

        $filters = $request->only([
            'search',
            'status',
            'payment_status',
            'date_from',
            'date_to',
            'sort',
            'direction',
            'page',
        ]);

        $orders = $this->orderRepository->findAll($filters, 25);
        $customers = $this->orderRepository->getCustomersForSelect();
        $products = $this->orderRepository->getProductsForSelect();

        return Inertia::render('orders/index', [
            'page' => $page->compile(),
            'orders' => $orders,
            'customers' => $customers,
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_number' => 'nullable|string|unique:orders,order_number',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'required|in:pending,paid,failed,refunded,partially_refunded',
            'shipping_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $items = $validated['items'];
            unset($validated['items']);

            $order = $this->orderRepository->createWithItems($validated, $items);

            return response()->json([
                'success' => true,
                'message' => 'Ordine creato con successo',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'ordine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): Response
    {
        $page = Page::make('Dettagli Ordine')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Ordini', '/cp/orders')
            ->breadcrumb('#'.$order->order_number);

        $order->load([
            'customer',
            'items.product',
            'shippingAddress',
            'billingAddress',
        ]);

        return Inertia::render('order-show', [
            'page' => $page->compile(),
            'order' => $order,
        ]);
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'required|in:pending,paid,failed,refunded,partially_refunded',
            'shipping_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $items = $validated['items'];
            unset($validated['items']);

            $updatedOrder = $this->orderRepository->updateWithItems($order->id, $validated, $items);

            return response()->json([
                'success' => true,
                'message' => 'Ordine aggiornato con successo',
                'order' => $updatedOrder,
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
     * Update only order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded,partially_refunded',
        ]);

        try {
            $updatedOrder = $this->orderRepository->update($order->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Stato ordine aggiornato con successo',
                'order' => $updatedOrder,
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
     * Remove the specified order
     */
    public function destroy(Order $order): JsonResponse
    {
        try {
            $this->orderRepository->delete($order->id);

            return response()->json([
                'success' => true,
                'message' => 'Ordine eliminato con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione dell\'ordine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
