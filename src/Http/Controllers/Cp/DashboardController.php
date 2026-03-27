<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Models\Customer;
use Cartino\Models\Order;
use Cartino\Models\Product;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends BaseController
{
    /**
     * Display the control panel dashboard.
     */
    public function index(Request $request): Response
    {
        $this->addBreadcrumb('Dashboard');

        $page = Page::make('Dashboard')
            ->primaryAction('Quick order', route('cp.orders.create'))
            ->secondaryActions([
                ['label' => 'Add product', 'url' => route('cp.products.create')],
                ['label' => 'Add customer', 'url' => route('cp.customers.create')],
                ['label' => 'View reports', 'url' => route('cp.reports.index')],
            ]);

        return $this->inertiaResponse('dashboard/index', [
            'page' => $page->compile(),
            'stats' => $this->getDashboardStats(),
            'charts' => $this->getChartData(),
            'recent_orders' => $this->getRecentOrders(),
            'low_stock_products' => $this->getLowStockProducts(),
            'top_products' => $this->getTopProducts(),
            'activities' => $this->getRecentActivities(),
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    protected function getDashboardStats(): array
    {
        $now = now();
        $lastMonth = $now->copy()->subMonth();

        // Current period stats
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', 'completed')->sum('total');
        $totalCustomers = Customer::count();
        $totalProducts = Product::count();

        // This month stats
        $ordersThisMonth = Order::whereMonth('created_at', $now->month)->count();
        $revenueThisMonth = Order::whereMonth('created_at', $now->month)->where('status', 'completed')->sum('total');
        $customersThisMonth = Customer::whereMonth('created_at', $now->month)->count();

        // Last month stats for comparison
        $ordersLastMonth = Order::whereMonth('created_at', $lastMonth->month)->count();
        $revenueLastMonth = Order::whereMonth('created_at', $lastMonth->month)
            ->where('status', 'completed')
            ->sum('total');
        $customersLastMonth = Customer::whereMonth('created_at', $lastMonth->month)->count();

        return [
            'total_orders' => [
                'value' => $totalOrders,
                'this_month' => $ordersThisMonth,
                'last_month' => $ordersLastMonth,
                'change' => $ordersLastMonth > 0
                    ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
                    : 0,
            ],
            'total_revenue' => [
                'value' => $totalRevenue,
                'formatted' => number_format($totalRevenue, 2).' €',
                'this_month' => $revenueThisMonth,
                'last_month' => $revenueLastMonth,
                'change' => $revenueLastMonth > 0
                    ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                    : 0,
            ],
            'total_customers' => [
                'value' => $totalCustomers,
                'this_month' => $customersThisMonth,
                'last_month' => $customersLastMonth,
                'change' => $customersLastMonth > 0
                    ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100, 1)
                    : 0,
            ],
            'total_products' => [
                'value' => $totalProducts,
                'published' => Product::where('status', 'published')->count(),
                'draft' => Product::where('status', 'draft')->count(),
                'low_stock' => Product::where('stock_quantity', '<=', 10)->count(),
            ],
            'average_order_value' => [
                'value' => $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0,
                'formatted' => $totalOrders > 0 ? (number_format($totalRevenue / $totalOrders, 2).' €') : '0 €',
            ],
        ];
    }

    /**
     * Get chart data for dashboard.
     */
    protected function getChartData(): array
    {
        $days = collect(range(0, 29))
            ->map(function ($day) {
                $date = now()->subDays($day);

                return [
                    'date' => $date->format('Y-m-d'),
                    'orders' => Order::whereDate('created_at', $date)->count(),
                    'revenue' => Order::whereDate('created_at', $date)->where('status', 'completed')->sum('total'),
                ];
            })
            ->reverse()
            ->values();

        return [
            'orders' => [
                'labels' => $days->pluck('date'),
                'data' => $days->pluck('orders'),
            ],
            'revenue' => [
                'labels' => $days->pluck('date'),
                'data' => $days->pluck('revenue'),
            ],
        ];
    }

    /**
     * Get recent orders.
     * Optimized: Select only needed columns to reduce memory usage and improve performance
     */
    protected function getRecentOrders(): array
    {
        return Order::select(['id', 'number', 'customer_id', 'total', 'status', 'created_at'])
            ->with('customer:id,first_name,last_name,email')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'number' => $order->number,
                    'customer_name' => $order->customer?->full_name ?? 'Guest',
                    'total' => $order->total,
                    'formatted_total' => number_format($order->total, 2).' €',
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('Y-m-d H:i'),
                    'url' => route('cp.orders.show', $order),
                ];
            })
            ->toArray();
    }

    /**
     * Get low stock products.
     * Optimized: Select only needed columns to reduce memory usage and improve performance
     */
    protected function getLowStockProducts(): array
    {
        return Product::select(['id', 'name', 'sku', 'stock_quantity', 'image_url'])
            ->where('track_inventory', true)
            ->where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                    'image_url' => $product->image_url,
                    'url' => route('cp.products.show', $product),
                ];
            })
            ->toArray();
    }

    /**
     * Get top selling products.
     * Optimized: Select only needed columns to reduce memory usage and improve performance
     */
    protected function getTopProducts(): array
    {
        // This would typically use order items to calculate
        // For now, return products ordered by created_at
        return Product::select(['id', 'name', 'price', 'image_url', 'status'])
            ->where('status', 'published')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'formatted_price' => number_format($product->price, 2).' €',
                    'image_url' => $product->image_url,
                    'sales' => rand(10, 100), // TODO: Calculate real sales
                    'url' => route('cp.products.show', $product),
                ];
            })
            ->toArray();
    }

    /**
     * Get recent activities.
     * Optimized: Select only needed columns to reduce memory usage and improve performance
     */
    protected function getRecentActivities(): array
    {
        $activities = [];

        // Recent orders
        $recentOrders = Order::select(['id', 'number', 'customer_id', 'created_at'])
            ->with('customer:id,first_name,last_name')
            ->latest()
            ->limit(3)
            ->get();
        foreach ($recentOrders as $order) {
            $activities[] = [
                'id' => 'order_'.$order->id,
                'type' => 'order',
                'icon' => 'shopping-bag',
                'title' => 'New order received',
                'description' => "Order #{$order->number} from ".($order->customer?->full_name ?? 'Guest'),
                'time' => $order->created_at->diffForHumans(),
                'url' => route('cp.orders.show', $order),
            ];
        }

        // Recent customers
        $recentCustomers = Customer::select(['id', 'first_name', 'last_name', 'email', 'created_at'])
            ->latest()
            ->limit(2)
            ->get();
        foreach ($recentCustomers as $customer) {
            $activities[] = [
                'id' => 'customer_'.$customer->id,
                'type' => 'customer',
                'icon' => 'user-plus',
                'title' => 'New customer registered',
                'description' => $customer->full_name.' ('.$customer->email.')',
                'time' => $customer->created_at->diffForHumans(),
                'url' => route('cp.customers.show', $customer),
            ];
        }

        // Recent products
        $recentProducts = Product::select(['id', 'name', 'updated_at'])
            ->latest()
            ->limit(2)
            ->get();
        foreach ($recentProducts as $product) {
            $activities[] = [
                'id' => 'product_'.$product->id,
                'type' => 'product',
                'icon' => 'package',
                'title' => 'Product updated',
                'description' => $product->name,
                'time' => $product->updated_at->diffForHumans(),
                'url' => route('cp.products.show', $product),
            ];
        }

        // Sort by time and return first 10
        return collect($activities)
            ->sortByDesc(function ($activity) {
                // Sort by timestamp, would need real timestamps for accurate sorting
                return $activity['id'];
            })
            ->take(10)
            ->values()
            ->toArray();
    }
}
