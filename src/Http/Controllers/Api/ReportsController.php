<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\ExportReportRequest;
use Cartino\Http\Requests\Api\ReportRequest;
use Cartino\Http\Resources\CustomersReportResource;
use Cartino\Http\Resources\DashboardResource;
use Cartino\Http\Resources\InventoryReportResource;
use Cartino\Http\Resources\ProductsReportResource;
use Cartino\Http\Resources\RevenueReportResource;
use Cartino\Http\Resources\SalesReportResource;
use Cartino\Models\Customer;
use Cartino\Repositories\ReportRepository;
use Illuminate\Http\JsonResponse;

class ReportsController extends ApiController
{
    public function __construct(
        protected ReportRepository $repository,
    ) {}

    /**
     * Dashboard summary with key metrics
     */
    public function dashboard(ReportRequest $request): JsonResponse
    {
        $from = $request->from ? new \DateTime($request->from) : now()->subDays(30);
        $to = $request->to ? new \DateTime($request->to) : now();

        $cacheKey = "dashboard:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}";

        $data = cache()->remember($cacheKey, 300, function () use ($from, $to) {
            return [
                'period' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                ],
                'sales' => $this->repository->getSalesBatch($from, $to),
                'customers' => $this->repository->getCustomerCounts($from, $to),
                'products' => $this->repository->getProductCounts(),
            ];
        });

        return $this->successResponse(new DashboardResource((object) $data));
    }

    /**
     * Sales report with time series data
     */
    public function sales(ReportRequest $request): JsonResponse
    {
        $from = $request->from ? new \DateTime($request->from) : now()->subDays(30);
        $to = $request->to ? new \DateTime($request->to) : now();
        $groupBy = $request->group_by ?? 'day';

        $cacheKey = "sales:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}:{$groupBy}";

        $data = cache()->remember($cacheKey, 600, function () use ($from, $to, $groupBy) {
            $sales = $this->repository->getSalesTimeSeries($from, $to, $groupBy);

            return [
                'period' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                    'group_by' => $groupBy,
                ],
                'summary' => [
                    'total_revenue' => $sales->sum('revenue'),
                    'total_orders' => $sales->sum('orders_count'),
                    'average_order_value' => $sales->avg('average_order_value'),
                    'total_items_sold' => $sales->sum('items_sold'),
                ],
                'data' => $sales,
            ];
        });

        return $this->successResponse(new SalesReportResource((object) $data));
    }

    /**
     * Customer analytics report
     */
    public function customers(ReportRequest $request): JsonResponse
    {
        $from = $request->from ? new \DateTime($request->from) : now()->subDays(30);
        $to = $request->to ? new \DateTime($request->to) : now();
        $groupBy = $request->group_by ?? 'day';

        $cacheKey = "customers:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}:{$groupBy}";

        $data = cache()->remember($cacheKey, 600, function () use ($from, $to, $groupBy) {
            $newCustomers = $this->repository->getNewCustomersTimeSeries($from, $to, $groupBy);
            $topCustomers = $this->repository->getTopCustomers($from, $to);

            return [
                'period' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                    'group_by' => $groupBy,
                ],
                'summary' => [
                    'new_customers' => $newCustomers->sum('count'),
                    'total_customers' => Customer::count(),
                    'average_ltv' => $this->repository->getAverageCustomerLifetimeValue(),
                ],
                'new_customers_timeline' => $newCustomers,
                'top_customers' => $topCustomers,
            ];
        });

        return $this->successResponse(new CustomersReportResource((object) $data));
    }

    /**
     * Products analytics report
     */
    public function products(ReportRequest $request): JsonResponse
    {
        $from = $request->from ? new \DateTime($request->from) : now()->subDays(30);
        $to = $request->to ? new \DateTime($request->to) : now();
        $limit = $request->limit ?? 20;

        $cacheKey = "products:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}:{$limit}";

        $data = cache()->remember($cacheKey, 600, function () use ($from, $to, $limit) {
            return [
                'period' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                ],
                'top_selling' => $this->repository->getTopSellingProducts($from, $to, $limit),
                'top_revenue' => $this->repository->getTopRevenueProducts($from, $to, $limit),
            ];
        });

        return $this->successResponse(new ProductsReportResource((object) $data));
    }

    /**
     * Revenue breakdown report
     */
    public function revenue(ReportRequest $request): JsonResponse
    {
        $from = $request->from ? new \DateTime($request->from) : now()->subDays(30);
        $to = $request->to ? new \DateTime($request->to) : now();
        $groupBy = $request->group_by ?? 'day';

        $cacheKey = "revenue:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}:{$groupBy}";

        $data = cache()->remember($cacheKey, 600, function () use ($from, $to, $groupBy) {
            $revenue = $this->repository->getRevenueTimeSeries($from, $to, $groupBy);

            return [
                'period' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                    'group_by' => $groupBy,
                ],
                'summary' => [
                    'gross_sales' => $revenue->sum('gross_sales'),
                    'discounts' => $revenue->sum('discounts'),
                    'taxes' => $revenue->sum('taxes'),
                    'shipping' => $revenue->sum('shipping'),
                    'net_sales' => $revenue->sum('net_sales'),
                ],
                'data' => $revenue,
            ];
        });

        return $this->successResponse(new RevenueReportResource((object) $data));
    }

    /**
     * Inventory status report
     */
    public function inventory(ReportRequest $request): JsonResponse
    {
        $lowStockThreshold = $request->input('low_stock_threshold', 10);

        $cacheKey = "inventory:{$lowStockThreshold}";

        $data = cache()->remember($cacheKey, 300, function () use ($lowStockThreshold) {
            return [
                'summary' => $this->repository->getInventorySummary($lowStockThreshold),
                'low_stock_products' => $this->repository->getLowStockProducts($lowStockThreshold),
                'out_of_stock_products' => $this->repository->getOutOfStockProducts(),
            ];
        });

        return $this->successResponse(new InventoryReportResource((object) $data));
    }

    /**
     * Orders by status report
     */
    public function ordersByStatus(ReportRequest $request): JsonResponse
    {
        $from = $request->from ? new \DateTime($request->from) : now()->subDays(30);
        $to = $request->to ? new \DateTime($request->to) : now();

        $cacheKey = "orders_by_status:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}";

        $data = cache()->remember($cacheKey, 300, function () use ($from, $to) {
            $byStatus = $this->repository->getOrdersByStatus($from, $to);

            return [
                'period' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                ],
                'by_status' => $byStatus,
                'total_orders' => $byStatus->sum('count'),
                'total_amount' => $byStatus->sum('total_amount'),
            ];
        });

        return $this->successResponse($data);
    }

    /**
     * Export report data (CSV/Excel ready format)
     */
    public function export(ExportReportRequest $request): JsonResponse
    {
        $reportType = $request->report_type;
        $from = $request->from ? new \DateTime($request->from) : now()->subDays(30);
        $to = $request->to ? new \DateTime($request->to) : now();

        $cacheKey = "export:{$reportType}:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}";

        $data = cache()->remember($cacheKey, 600, function () use ($reportType, $from, $to) {
            return match ($reportType) {
                'sales' => $this->repository->exportSalesData($from, $to),
                'customers' => $this->repository->exportCustomersData($from, $to),
                'products' => $this->repository->exportProductsData($from, $to),
                'revenue' => $this->repository->exportRevenueData($from, $to),
                'orders' => $this->repository->exportOrdersData($from, $to),
            };
        });

        return $this->successResponse([
            'report_type' => $reportType,
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'records_count' => count($data),
            'data' => $data,
        ]);
    }
}
