<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Customer;
use Cartino\Models\Order;
use Cartino\Models\OrderLine;
use Cartino\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    /**
     * Get all sales metrics in a single query (dashboard batch)
     */
    public function getSalesBatch(\DateTime $from, \DateTime $to): array
    {
        $result = DB::selectOne("
            SELECT
                COALESCE(SUM(total), 0) as total_revenue,
                COUNT(*) as total_orders,
                COALESCE(AVG(total), 0) as average_order_value,
                COALESCE(SUM((SELECT SUM(quantity) FROM order_lines WHERE order_lines.order_id = orders.id)), 0) as total_items_sold
            FROM orders
            WHERE created_at BETWEEN ? AND ?
            AND status != 'cancelled'
        ", [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);

        return [
            'total_revenue' => (float) $result->total_revenue,
            'total_orders' => (int) $result->total_orders,
            'average_order_value' => (float) $result->average_order_value,
            'total_items_sold' => (int) $result->total_items_sold,
        ];
    }

    /**
     * Get all customer counts in a single query
     */
    public function getCustomerCounts(\DateTime $from, \DateTime $to): array
    {
        $newCustomers = DB::selectOne('
            SELECT COUNT(*) as count
            FROM customers
            WHERE created_at BETWEEN ? AND ?
        ', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);

        $returningCustomers = DB::selectOne('
            SELECT COUNT(DISTINCT customer_id) as count
            FROM orders
            WHERE created_at BETWEEN ? AND ?
            AND customer_id IN (
                SELECT customer_id
                FROM orders
                GROUP BY customer_id
                HAVING COUNT(*) > 1
            )
        ', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);

        $totalCustomers = DB::selectOne('SELECT COUNT(*) as count FROM customers');

        return [
            'new_customers' => (int) $newCustomers->count,
            'returning_customers' => (int) $returningCustomers->count,
            'total_customers' => (int) $totalCustomers->count,
        ];
    }

    /**
     * Get all product counts in a single query
     */
    public function getProductCounts(): array
    {
        // Note: inventory is managed at variant level
        $result = DB::selectOne('
            SELECT
                COUNT(DISTINCT p.id) as total_products,
                SUM(CASE WHEN pv.inventory_quantity < 10 AND pv.inventory_quantity > 0 THEN 1 ELSE 0 END) as low_stock_products,
                SUM(CASE WHEN pv.inventory_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_products
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
        ');

        return [
            'total_products' => (int) $result->total_products,
            'low_stock_products' => (int) $result->low_stock_products,
            'out_of_stock_products' => (int) $result->out_of_stock_products,
        ];
    }

    /**
     * Get total revenue for period
     */
    public function getTotalRevenue(\DateTime $from, \DateTime $to): float
    {
        return Order::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelled')->sum('total') ?? 0;
    }

    /**
     * Get total orders for period
     */
    public function getTotalOrders(\DateTime $from, \DateTime $to): int
    {
        return Order::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelled')->count();
    }

    /**
     * Get average order value for period
     */
    public function getAverageOrderValue(\DateTime $from, \DateTime $to): float
    {
        return Order::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelled')->avg('total') ?? 0;
    }

    /**
     * Get total items sold for period
     */
    public function getTotalItemsSold(\DateTime $from, \DateTime $to): int
    {
        return OrderLine::join('orders', 'order_lines.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.status', '!=', 'cancelled')
            ->sum('order_lines.quantity') ?? 0;
    }

    /**
     * Get new customers for period
     */
    public function getNewCustomers(\DateTime $from, \DateTime $to): int
    {
        return Customer::whereBetween('created_at', [$from, $to])->count();
    }

    /**
     * Get returning customers for period
     */
    public function getReturningCustomers(\DateTime $from, \DateTime $to): int
    {
        return Customer::whereHas(
            'orders',
            function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            },
            '>',
            1,
        )->count();
    }

    /**
     * Get average customer lifetime value
     */
    public function getAverageCustomerLifetimeValue(): float
    {
        return Customer::selectRaw('AVG(total_spent) as avg_ltv')->value('avg_ltv') ?? 0;
    }

    /**
     * Get sales time series data
     */
    public function getSalesTimeSeries(
        \DateTime $from,
        \DateTime $to,
        string $groupBy = 'day',
    ): Collection {
        $dateFormat = $this->getDateFormat($groupBy);

        $results = DB::select(
            "
            SELECT
                DATE_FORMAT(orders.created_at, '{$dateFormat}') as period,
                COUNT(*) as orders_count,
                COALESCE(SUM(orders.total), 0) as revenue,
                COALESCE(AVG(orders.total), 0) as average_order_value,
                COALESCE(SUM(
                    (SELECT SUM(quantity) FROM order_lines WHERE order_lines.order_id = orders.id)
                ), 0) as items_sold
            FROM orders
            WHERE orders.created_at BETWEEN ? AND ?
            AND orders.status != 'cancelled'
            GROUP BY period
            ORDER BY period
        ",
            [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')],
        );

        return collect($results)
            ->map(fn ($row) => [
                'period' => $row->period,
                'orders_count' => (int) $row->orders_count,
                'revenue' => (float) $row->revenue,
                'average_order_value' => (float) $row->average_order_value,
                'items_sold' => (int) $row->items_sold,
            ]);
    }

    /**
     * Get new customers time series
     */
    public function getNewCustomersTimeSeries(
        \DateTime $from,
        \DateTime $to,
        string $groupBy = 'day',
    ): Collection {
        $dateFormat = $this->getDateFormat($groupBy);

        $results = DB::select(
            "
            SELECT 
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                COUNT(*) as count
            FROM customers
            WHERE created_at BETWEEN ? AND ?
            GROUP BY period
            ORDER BY period
        ",
            [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')],
        );

        return collect($results)
            ->map(fn ($row) => [
                'period' => $row->period,
                'count' => (int) $row->count,
            ]);
    }

    /**
     * Get top customers by spending
     */
    public function getTopCustomers(\DateTime $from, \DateTime $to, int $limit = 10): Collection
    {
        $results = DB::select("
            SELECT
                c.id,
                c.first_name,
                c.last_name,
                c.email,
                COUNT(o.id) as orders_count,
                COALESCE(SUM(o.total), 0) as total_spent
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id
                AND o.created_at BETWEEN ? AND ?
                AND o.status != 'cancelled'
            GROUP BY c.id, c.first_name, c.last_name, c.email
            HAVING orders_count > 0
            ORDER BY total_spent DESC
            LIMIT ?
        ", [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'), $limit]);

        return collect($results)
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => trim($row->first_name.' '.$row->last_name),
                'email' => $row->email,
                'orders_count' => (int) $row->orders_count,
                'total_spent' => (float) $row->total_spent,
            ]);
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts(
        \DateTime $from,
        \DateTime $to,
        int $limit = 20,
    ): Collection {
        $results = DB::select("
            SELECT
                p.id,
                p.title as name,
                p.handle as sku,
                COALESCE(SUM(ol.quantity), 0) as units_sold,
                COALESCE(SUM(ol.line_total), 0) as revenue
            FROM products p
            INNER JOIN order_lines ol ON p.id = ol.product_id
            INNER JOIN orders o ON ol.order_id = o.id
            WHERE o.created_at BETWEEN ? AND ?
            AND o.status != 'cancelled'
            GROUP BY p.id, p.title, p.handle
            ORDER BY units_sold DESC
            LIMIT ?
        ", [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'), $limit]);

        return collect($results)
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'sku' => $row->sku,
                'units_sold' => (int) $row->units_sold,
                'revenue' => (float) $row->revenue,
            ]);
    }

    /**
     * Get top revenue products
     */
    public function getTopRevenueProducts(
        \DateTime $from,
        \DateTime $to,
        int $limit = 20,
    ): Collection {
        $results = DB::select("
            SELECT
                p.id,
                p.title as name,
                p.handle as sku,
                COALESCE(SUM(ol.quantity), 0) as units_sold,
                COALESCE(SUM(ol.line_total), 0) as revenue
            FROM products p
            INNER JOIN order_lines ol ON p.id = ol.product_id
            INNER JOIN orders o ON ol.order_id = o.id
            WHERE o.created_at BETWEEN ? AND ?
            AND o.status != 'cancelled'
            GROUP BY p.id, p.title, p.handle
            ORDER BY revenue DESC
            LIMIT ?
        ", [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'), $limit]);

        return collect($results)
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'sku' => $row->sku,
                'units_sold' => (int) $row->units_sold,
                'revenue' => (float) $row->revenue,
            ]);
    }

    /**
     * Get revenue time series with breakdown
     */
    public function getRevenueTimeSeries(
        \DateTime $from,
        \DateTime $to,
        string $groupBy = 'day',
    ): Collection {
        $dateFormat = $this->getDateFormat($groupBy);

        $results = DB::select(
            "
            SELECT
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                COALESCE(SUM(subtotal), 0) as gross_sales,
                COALESCE(SUM(discount_total), 0) as discounts,
                COALESCE(SUM(tax_total), 0) as taxes,
                COALESCE(SUM(shipping_total), 0) as shipping,
                COALESCE(SUM(total), 0) as net_sales,
                COUNT(*) as orders_count
            FROM orders
            WHERE created_at BETWEEN ? AND ?
            AND status != 'cancelled'
            GROUP BY period
            ORDER BY period
        ",
            [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')],
        );

        return collect($results)
            ->map(fn ($row) => [
                'period' => $row->period,
                'gross_sales' => (float) $row->gross_sales,
                'discounts' => (float) $row->discounts,
                'taxes' => (float) $row->taxes,
                'shipping' => (float) $row->shipping,
                'net_sales' => (float) $row->net_sales,
                'orders_count' => (int) $row->orders_count,
            ]);
    }

    /**
     * Get orders grouped by status
     */
    public function getOrdersByStatus(\DateTime $from, \DateTime $to): Collection
    {
        return Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total) as total_amount')
            ->groupBy('status')
            ->get();
    }

    /**
     * Get inventory summary
     */
    public function getInventorySummary(int $lowStockThreshold = 10): array
    {
        // Note: inventory and price are managed at variant level
        $result = DB::selectOne('
            SELECT
                COUNT(DISTINCT p.id) as total_products,
                SUM(CASE WHEN pv.inventory_quantity > 0 THEN 1 ELSE 0 END) as in_stock,
                SUM(CASE WHEN pv.inventory_quantity > 0 AND pv.inventory_quantity <= ? THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN pv.inventory_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                COALESCE(SUM(pv.inventory_quantity * pv.price), 0) as total_inventory_value
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
        ', [$lowStockThreshold]);

        return [
            'total_products' => (int) $result->total_products,
            'in_stock' => (int) $result->in_stock,
            'low_stock' => (int) $result->low_stock,
            'out_of_stock' => (int) $result->out_of_stock,
            'total_inventory_value' => (float) $result->total_inventory_value,
        ];
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10, int $limit = 50): Collection
    {
        // Note: inventory and price are managed at variant level
        return DB::table('products as p')
            ->join('product_variants as pv', 'p.id', '=', 'pv.product_id')
            ->where('pv.inventory_quantity', '>', 0)
            ->where('pv.inventory_quantity', '<=', $threshold)
            ->select('p.id', 'p.title as name', 'pv.sku', 'pv.inventory_quantity', 'pv.price')
            ->orderBy('pv.inventory_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts(int $limit = 50): Collection
    {
        // Note: inventory and price are managed at variant level
        return DB::table('products as p')
            ->join('product_variants as pv', 'p.id', '=', 'pv.product_id')
            ->where('pv.inventory_quantity', 0)
            ->select('p.id', 'p.title as name', 'pv.sku', 'pv.price')
            ->limit($limit)
            ->get();
    }

    /**
     * Export sales data
     */
    public function exportSalesData(\DateTime $from, \DateTime $to): array
    {
        return Order::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->select('id', 'order_number', 'customer_id', 'status', 'total', 'created_at')
            ->get()
            ->toArray();
    }

    /**
     * Export customers data
     */
    public function exportCustomersData(\DateTime $from, \DateTime $to): array
    {
        return Customer::whereBetween('created_at', [$from, $to])
            ->select('id', 'name', 'email', 'phone', 'created_at')
            ->get()
            ->toArray();
    }

    /**
     * Export products data
     */
    public function exportProductsData(\DateTime $from, \DateTime $to): array
    {
        return Product::select('products.*')
            ->selectRaw('COALESCE(SUM(order_lines.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(order_lines.line_total), 0) as revenue')
            ->leftJoin('order_lines', 'products.id', '=', 'order_lines.product_id')
            ->leftJoin('orders', function ($join) use ($from, $to) {
                $join->on('order_lines.order_id', '=', 'orders.id')->whereBetween('orders.created_at', [
                    $from,
                    $to,
                ])->where('orders.status', '!=', 'cancelled');
            })
            ->groupBy('products.id')
            ->get()
            ->toArray();
    }

    /**
     * Export revenue data
     */
    public function exportRevenueData(\DateTime $from, \DateTime $to): array
    {
        return Order::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->select('created_at', 'subtotal', 'discount_total', 'tax_total', 'shipping_total', 'total')
            ->get()
            ->toArray();
    }

    /**
     * Export orders data
     */
    public function exportOrdersData(\DateTime $from, \DateTime $to): array
    {
        return Order::whereBetween('created_at', [$from, $to])
            ->with(['customer:id,first_name,last_name,email', 'lines:order_id,product_id,quantity,unit_price'])
            ->get()
            ->toArray();
    }

    /**
     * Get date format for MySQL DATE_FORMAT based on group by
     */
    private function getDateFormat(string $groupBy): string
    {
        return match ($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };
    }
}
