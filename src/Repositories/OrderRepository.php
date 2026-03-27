<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Customer;
use Cartino\Models\Order;
use Cartino\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderRepository extends BaseRepository
{
    protected array $with = ['customer'];

    protected string $cachePrefix = 'orders';

    protected function makeModel(): Model
    {
        return new Order;
    }

    /**
     * Get paginated orders with filters and search
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? config('settings.pagination.per_page', 15);

        return QueryBuilder::for(Order::class)
            ->with([
                'customer',
                'lines',
            ])
            ->allowedFilters([
                'order_number',
                'status',
                'payment_status',
                AllowedFilter::exact('customer_id'),
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
            ])
            ->allowedSorts(['order_number', 'created_at', 'total_amount', 'status'])
            ->allowedIncludes(['customer', 'lines', 'lines.product', 'shipping_address', 'billing_address'])
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends($filters);
    }

    /**
     * Find one by ID or order number
     */
    public function findOne(int|string $orderNumberOrId): ?Order
    {
        $cacheKey = "order:{$orderNumberOrId}";

        return $this->cacheQuery($cacheKey, function () use ($orderNumberOrId) {
            return $this->model
                ->with(['customer', 'items.product', 'shippingAddress', 'billingAddress'])
                ->where('id', $orderNumberOrId)
                ->orWhere('order_number', $orderNumberOrId)
                ->firstOrFail();
        });
    }

    /**
     * Create one
     */
    public function createOne(array $data): Order
    {
        // Generate order number if not provided
        if (empty($data['order_number'])) {
            $data['order_number'] = $this->generateOrderNumber();
        }

        $order = $this->model->create($data);
        $this->clearModelCache();

        return $order;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Order
    {
        $order = $this->findOrFail($id);
        $order->update($data);
        $this->clearModelCache();

        return $order->fresh(['customer', 'items.product']);
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $order = $this->findOrFail($id);
        $deleted = $order->delete();
        $this->clearModelCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $order = $this->findOrFail($id);

        return in_array($order->status, ['draft', 'cancelled']);
    }

    /**
     * Create a new order with items
     */
    public function createWithItems(array $orderData, array $items): Order
    {
        // Clear cache
        $this->clearCache();

        // Generate order number if not provided
        if (empty($orderData['order_number'])) {
            $orderData['order_number'] = $this->generateOrderNumber();
        }

        // Calculate totals
        $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $shippingAmount = $orderData['shipping_amount'] ?? 0;
        $orderData['total_amount'] = $subtotal + $shippingAmount;

        // Create order
        $order = $this->model->create($orderData);

        // Add order items
        foreach ($items as $item) {
            $order
                ->items()
                ->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
        }

        return $order->load(['customer', 'items.product']);
    }

    /**
     * Update order with items
     */
    public function updateWithItems(int $id, array $orderData, array $items): Model
    {
        // Clear cache
        $this->clearCache();

        $order = $this->model->find($id);

        // Calculate totals
        $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $shippingAmount = $orderData['shipping_amount'] ?? 0;
        $orderData['total_amount'] = $subtotal + $shippingAmount;

        // Update order
        $order->update($orderData);

        // Update order items - delete old ones and create new ones
        $order->items()->delete();

        foreach ($items as $item) {
            $order
                ->items()
                ->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
        }

        return $order->load(['customer', 'items.product']);
    }

    /**
     * Update order
     */
    public function update(int $id, array $attributes): Model
    {
        // Clear cache
        $this->clearCache();

        $order = $this->model->find($id);
        $order->update($attributes);

        return $order;
    }

    /**
     * Delete order
     */
    public function delete(int $id): bool
    {
        // Clear cache
        $this->clearCache();

        $order = $this->model->find($id);

        // Delete order items first
        $order->items()->delete();

        return $order->delete();
    }

    /**
     * Get customers for order creation
     */
    public function getCustomersForSelect(): Collection
    {
        return Customer::select('id', 'first_name', 'last_name', 'email')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Get products for order creation
     */
    public function getProductsForSelect(): Collection
    {
        return Product::select('id', 'name', 'price')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.date('Y').'-'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->model->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Cancel an order
     */
    public function cancel(int $id): ?Order
    {
        $order = $this->model->find($id);

        if (! $order || $order->status === 'cancelled' || $order->status === 'delivered') {
            return null;
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->clearCache();

        return $order->fresh();
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(int $id): ?Order
    {
        $order = $this->model->find($id);

        if (! $order || $order->payment_status === 'paid') {
            return null;
        }

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->clearCache();

        return $order->fresh();
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped(int $id, array $shippingData): ?Order
    {
        $order = $this->model->find($id);

        if (! $order || in_array($order->status, ['shipped', 'delivered', 'cancelled'])) {
            return null;
        }

        $updateData = [
            'status' => 'shipped',
            'shipped_at' => now(),
        ];

        if (isset($shippingData['tracking_number'])) {
            $updateData['tracking_number'] = $shippingData['tracking_number'];
        }

        if (isset($shippingData['carrier'])) {
            $updateData['carrier'] = $shippingData['carrier'];
        }

        $order->update($updateData);

        $this->clearCache();

        return $order->fresh();
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(int $id): ?Order
    {
        $order = $this->model->find($id);

        if (! $order || in_array($order->status, ['delivered', 'cancelled'])) {
            return null;
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $this->clearCache();

        return $order->fresh();
    }

    /**
     * Get order statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $cacheKey = $this->getCacheKey('statistics', md5(serialize($filters)));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            $query = $this->model->newQuery();

            // Apply date filters
            if (! empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }

            // Apply status filter
            if (! empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $orders = $query->get();

            $totalOrders = $orders->count();
            $totalRevenue = $orders->sum('total_amount');
            $averageOrderValue = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

            $statusCounts = $orders->groupBy('status')->map->count()->toArray();
            $paymentStatusCounts = $orders->groupBy('payment_status')->map->count()->toArray();

            return [
                'total_orders' => $totalOrders,
                'total_revenue' => round($totalRevenue, 2),
                'average_order_value' => round($averageOrderValue, 2),
                'status_breakdown' => $statusCounts,
                'payment_status_breakdown' => $paymentStatusCounts,
            ];
        });
    }

    /**
     * Bulk action for orders
     */
    public function bulkAction(string $action, array $ids, array $metadata = []): array
    {
        $validatedIds = $this->model
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
        $processedCount = 0;
        $errors = [];

        foreach ($validatedIds as $id) {
            try {
                switch ($action) {
                    case 'cancel':
                        $order = $this->cancel($id);
                        if ($order) {
                            $processedCount++;
                        } else {
                            $errors[] = "Order ID {$id}: Cannot cancel order";
                        }
                        break;

                    case 'mark_as_paid':
                        $order = $this->markAsPaid($id);
                        if ($order) {
                            $processedCount++;
                        } else {
                            $errors[] = "Order ID {$id}: Cannot mark as paid";
                        }
                        break;

                    case 'mark_as_shipped':
                        $shippingData = $metadata['shipping'] ?? [];
                        $order = $this->markAsShipped($id, $shippingData);
                        if ($order) {
                            $processedCount++;
                        } else {
                            $errors[] = "Order ID {$id}: Cannot mark as shipped";
                        }
                        break;

                    case 'mark_as_delivered':
                        $order = $this->markAsDelivered($id);
                        if ($order) {
                            $processedCount++;
                        } else {
                            $errors[] = "Order ID {$id}: Cannot mark as delivered";
                        }
                        break;

                    case 'export':
                        $processedCount++;
                        break;

                    default:
                        $errors[] = "Order ID {$id}: Unknown action '{$action}'";
                }
            } catch (\Exception $e) {
                $errors[] = "Order ID {$id}: {$e->getMessage()}";
            }
        }

        $this->clearCache();

        return [
            'processed' => $processedCount,
            'total' => count($ids),
            'errors' => $errors,
            'success' => count($errors) === 0,
        ];
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }
}
