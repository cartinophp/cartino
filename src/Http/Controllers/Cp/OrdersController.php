<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Listing;
use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Models\Order;
use Cartino\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
        $page = Page::make('Orders')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Orders');

        $listing = Listing::make()
            ->column('order_number', 'Order', sortable: true)
            ->column('customer_name', 'Customer', sortable: true)
            ->column('status', 'Status', sortable: true)
            ->column('payment_status', 'Payment', sortable: true)
            ->column('total', 'Total', sortable: true, format: 'currency')
            ->column('items_count', 'Items', sortable: true)
            ->column('created_at', 'Date', sortable: true, format: 'date')
            ->column('actions', '', sortable: false, width: '100px')
            ->filter('status', 'Status', 'select', [
                ['value' => '', 'label' => 'All Statuses'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'processing', 'label' => 'Processing'],
                ['value' => 'shipped', 'label' => 'Shipped'],
                ['value' => 'delivered', 'label' => 'Delivered'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
                ['value' => 'refunded', 'label' => 'Refunded'],
            ], 'All Statuses')
            ->filter('payment_status', 'Payment', 'select', [
                ['value' => '', 'label' => 'All'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'failed', 'label' => 'Failed'],
                ['value' => 'refunded', 'label' => 'Refunded'],
            ], 'All')
            ->bulkAction('fulfill', 'Fulfill')
            ->bulkAction('cancel', 'Cancel', destructive: true, confirm: 'Cancel selected orders?')
            ->bulkAction('archive', 'Archive')
            ->bulkAction('export', 'Export')
            ->searchable(placeholder: 'Search orders...')
            ->emptyState('No orders yet', 'Orders will appear here when customers make purchases.', icon: 'shopping-cart')
            ->sort('created_at', 'desc')
            ->perPage(25);

        $data = QueryBuilder::for(Order::class)
            ->with(['customer', 'lines'])
            ->allowedFilters([
                'order_number', 'status', 'payment_status',
                AllowedFilter::exact('customer_id'),
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
            ])
            ->allowedSorts(['order_number', 'created_at', 'total_amount', 'status'])
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 25))
            ->withQueryString();

        return Inertia::render('orders/index', [
            'page' => $page->compile(),
            'listing' => $listing->toArray(),
            'data' => $data,
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

        return Inertia::render('orders/show', [
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
