<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Requests\CP\StoreOrderRequest;
use Cartino\Http\Resources\CP\CustomerResource;
use Cartino\Http\Resources\CP\OrderResource;
use Cartino\Models\Customer;
use Cartino\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class OrderController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:browse_orders')->only(['index', 'show']);
        $this->middleware('can:create_orders')->only(['create', 'store']);
        $this->middleware('can:update_orders')->only(['edit', 'update']);
        $this->middleware('can:delete_orders')->only(['destroy']);
    }

    /**
     * Display orders listing.
     */
    public function index(Request $request): Response
    {
        $this->addDashboardBreadcrumb()->addBreadcrumb('Orders');

        $filters = $this->getFilters([
            'search',
            'status',
            'payment_status',
            'fulfillment_status',
            'customer_id',
            'total_min',
            'total_max',
            'created_at',
        ]);

        $orders = Order::query()
            ->with(['customer', 'items.product'])
            ->withCount(['items'])
            ->when($filters, fn ($query) => $this->applyFilters($query, $filters))
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', 15));

        $page = Page::make('Orders')
            ->primaryAction('Create order', route('cp.orders.create'))
            ->secondaryActions([
                ['label' => 'Export orders', 'url' => route('cp.orders.export')],
                ['label' => 'Abandoned checkouts', 'url' => route('cp.abandoned-checkouts.index')],
                ['label' => 'Draft orders', 'url' => route('cp.orders.index', ['status' => 'draft'])],
            ]);

        return $this->inertiaResponse('orders/index', [
            'page' => $page->compile(),
            'orders' => $orders->through(fn ($order) => new OrderResource($order)),
            'filters' => $filters,
            'stats' => $this->getOrderStats(),
        ]);
    }

    /**
     * Show create form.
     */
    public function create(Request $request): Response
    {
        $this->addDashboardBreadcrumb()->addBreadcrumb('Orders', 'cartino.orders.index')->addBreadcrumb('Create order');

        $customer = null;
        if ($request->has('customer')) {
            $customer = Customer::find($request->input('customer'));
        }

        $page = Page::make('Create order')
            ->primaryAction('Save order', null, ['form' => 'order-form'])
            ->secondaryActions([
                ['label' => 'Save as draft', 'action' => 'save_draft'],
                ['label' => 'Save & send invoice', 'action' => 'save_send_invoice'],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'OrderGeneralForm'],
                'items' => ['label' => 'Items', 'component' => 'OrderItemsForm'],
                'shipping' => ['label' => 'Shipping', 'component' => 'OrderShippingForm'],
                'payment' => ['label' => 'Payment', 'component' => 'OrderPaymentForm'],
                'notes' => ['label' => 'Notes', 'component' => 'OrderNotesForm'],
            ]);

        return $this->inertiaResponse('orders/Create', [
            'page' => $page->compile(),
            'customer' => $customer ? new CustomerResource($customer) : null,
        ]);
    }

    /**
     * Store new order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = Order::create($request->validated());

        // Handle order items
        if ($request->has('items')) {
            $this->syncOrderItems($order, $request->input('items'));
        }

        // Handle shipping address
        if ($request->has('shipping_address')) {
            $order->shippingAddress()->create($request->input('shipping_address'));
        }

        // Handle billing address
        if ($request->has('billing_address')) {
            $order->billingAddress()->create($request->input('billing_address'));
        }

        $action = $request->input('_action', 'save');

        $redirectUrl = match ($action) {
            'save_draft' => route('cp.orders.edit', $order),
            'save_send_invoice' => route('cp.orders.show', $order),
            default => route('cp.orders.index'),
        };

        // Send invoice if requested
        if ($action === 'save_send_invoice') {
            // TODO: Implement invoice sending
        }

        return $this->successResponse('Order created successfully', [
            'order' => new OrderResource($order),
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Display order details.
     */
    public function show(Order $order): Response
    {
        $order->load([
            'customer',
            'items.product.media',
            'shippingAddress',
            'billingAddress',
            'payments',
            'fulfillments',
            'refunds',
        ]);

        $this->addDashboardBreadcrumb()->addBreadcrumb('Orders', 'cartino.orders.index')->addBreadcrumb($order->number);

        $page = Page::make("Order #{$order->number}")
            ->primaryAction('Edit order', route('cp.orders.edit', $order))
            ->secondaryActions([
                ['label' => 'Print invoice', 'action' => 'print_invoice'],
                ['label' => 'Send invoice', 'action' => 'send_invoice'],
                ['label' => 'Create fulfillment', 'url' => route('cp.fulfillments.create', $order)],
                ['label' => 'Refund', 'url' => route('cp.refunds.create', $order)],
                ['label' => 'Archive', 'action' => 'archive'],
            ])
            ->tabs([
                'overview' => ['label' => 'Overview', 'component' => 'OrderOverview'],
                'timeline' => ['label' => 'Timeline', 'component' => 'OrderTimeline'],
                'fulfillments' => ['label' => 'Fulfillments', 'component' => 'OrderFulfillments'],
                'payments' => ['label' => 'Payments', 'component' => 'OrderPayments'],
                'customer' => ['label' => 'Customer', 'component' => 'OrderCustomer'],
            ]);

        return $this->inertiaResponse('orders/Show', [
            'page' => $page->compile(),
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Order $order): Response
    {
        $order->load([
            'customer',
            'items.product',
            'shippingAddress',
            'billingAddress',
        ]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Orders', 'cartino.orders.index')
            ->addBreadcrumb($order->number, route('cp.orders.show', $order))
            ->addBreadcrumb('Edit');

        $page = Page::make("Edit Order #{$order->number}")
            ->primaryAction('Update order', null, ['form' => 'order-form'])
            ->secondaryActions([
                ['label' => 'View order', 'url' => route('cp.orders.show', $order)],
                ['label' => 'Send invoice', 'action' => 'send_invoice'],
                ['label' => 'Cancel order', 'action' => 'cancel', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'OrderGeneralForm'],
                'items' => ['label' => 'Items', 'component' => 'OrderItemsForm'],
                'shipping' => ['label' => 'Shipping', 'component' => 'OrderShippingForm'],
                'payment' => ['label' => 'Payment', 'component' => 'OrderPaymentForm'],
                'notes' => ['label' => 'Notes', 'component' => 'OrderNotesForm'],
            ]);

        return $this->inertiaResponse('orders/Edit', [
            'page' => $page->compile(),
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Update order.
     */
    public function update(StoreOrderRequest $request, Order $order): JsonResponse
    {
        $order->update($request->validated());

        // Handle order items
        if ($request->has('items')) {
            $this->syncOrderItems($order, $request->input('items'));
        }

        // Handle addresses
        if ($request->has('shipping_address')) {
            $order->shippingAddress()->updateOrCreate([], $request->input('shipping_address'));
        }

        if ($request->has('billing_address')) {
            $order->billingAddress()->updateOrCreate([], $request->input('billing_address'));
        }

        return $this->successResponse('Order updated successfully', [
            'order' => new OrderResource($order->fresh([
                'customer',
                'items.product',
                'shippingAddress',
                'billingAddress',
            ])),
        ]);
    }

    /**
     * Delete/Cancel order.
     */
    public function destroy(Order $order): JsonResponse
    {
        if ($order->status === 'delivered') {
            return $this->errorResponse('Cannot delete delivered orders');
        }

        // If order has payments, mark as cancelled instead of deleting
        if ($order->payments()->exists()) {
            $order->update(['status' => 'cancelled']);

            return $this->successResponse('Order cancelled successfully');
        }

        $order->delete();

        return $this->successResponse('Order deleted successfully');
    }

    /**
     * Handle bulk operations.
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:fulfill,cancel,archive,export,print_labels',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:orders,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        return $this->handleBulkOperation($action, $ids, function ($action, $ids) {
            $orders = Order::whereIn('id', $ids);

            return match ($action) {
                'fulfill' => $this->handleBulkFulfill($orders),
                'cancel' => $orders->update(['status' => 'cancelled']),
                'archive' => $orders->update(['archived_at' => now()]),
                'export' => $this->handleBulkExport($orders),
                'print_labels' => $this->handleBulkPrintLabels($orders),
            };
        });
    }

    /**
     * Apply search filter for orders.
     */
    protected function applySearchFilter($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('number', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhereHas(
                'customer',
                function ($customerQuery) use ($search) {
                    $customerQuery->where('first_name', 'like', "%{$search}%")->orWhere(
                        'last_name',
                        'like',
                        "%{$search}%",
                    )->orWhere('email', 'like', "%{$search}%");
                },
            );
        });
    }

    /**
     * Apply custom filters.
     */
    protected function applyCustomFilter($query, string $key, $value): void
    {
        match ($key) {
            'payment_status' => $query->where('payment_status', $value),
            'fulfillment_status' => $query->where('fulfillment_status', $value),
            'customer_id' => $query->where('customer_id', $value),
            'total_min' => $query->where('total', '>=', $value),
            'total_max' => $query->where('total', '<=', $value),
            default => parent::applyCustomFilter($query, $key, $value),
        };
    }

    /**
     * Get order statistics.
     */
    private function getOrderStats(): array
    {
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total'),
            'average_order_value' => Order::where('status', 'completed')->avg('total') ?? 0,
        ];
    }

    /**
     * Sync order items.
     */
    private function syncOrderItems(Order $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $item) {
            $order
                ->items()
                ->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? null,
                ]);
        }

        // Recalculate order totals
        $order->calculateTotals();
    }

    /**
     * Handle bulk fulfill.
     */
    private function handleBulkFulfill($orders): int
    {
        $count = 0;
        $orders
            ->get()
            ->each(function ($order) use (&$count) {
                if ($order->status === 'pending' || $order->status === 'processing') {
                    $order->update(['status' => 'fulfilled']);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Handle bulk export.
     */
    private function handleBulkExport($orders): int
    {
        $count = $orders->count();

        // TODO: Implement actual export logic
        return $count;
    }

    /**
     * Handle bulk print labels.
     */
    private function handleBulkPrintLabels($orders): int
    {
        $count = $orders->count();

        // TODO: Implement label printing logic
        return $count;
    }
}
