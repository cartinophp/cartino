<?php

namespace Cartino\Repositories;

use Cartino\Models\StockNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StockNotificationRepository extends BaseRepository
{
    protected string $cachePrefix = 'stock_notifications';

    protected function makeModel(): Model
    {
        return new StockNotification;
    }

    /**
     * Get paginated stock notifications with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['customer']);

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Customer filter
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Product filter
        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Product type filter
        if (! empty($filters['product_type'])) {
            $query->where('product_type', $filters['product_type']);
        }

        // Preferred method filter
        if (! empty($filters['preferred_method'])) {
            $query->where('preferred_method', $filters['preferred_method']);
        }

        // Date range filter
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q
                    ->where('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('product_handle', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('email', 'like', "%{$search}%")->orWhere(
                            'first_name',
                            'like',
                            "%{$search}%",
                        )->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get pending notifications for a product
     */
    public function getPendingForProduct(string $productType, int $productId, ?array $variantData = null): Category
    {
        $query = $this->model->where('status', 'pending')->where('product_type', $productType)->where(
            'product_id',
            $productId,
        );

        if ($variantData) {
            $query->where('variant_data', json_encode($variantData));
        }

        return $query->with(['customer'])->get();
    }

    /**
     * Notify customers about stock availability
     */
    public function notifyStockAvailable(string $productType, int $productId, ?array $variantData = null): int
    {
        $notifications = $this->getPendingForProduct($productType, $productId, $variantData);
        $notified = 0;

        foreach ($notifications as $notification) {
            if ($this->sendNotification($notification)) {
                $notification->update([
                    'status' => 'notified',
                    'notified_at' => now(),
                ]);
                $notified++;
            }
        }

        $this->clearCache();

        return $notified;
    }

    /**
     * Send individual notification
     */
    protected function sendNotification(StockNotification $notification): bool
    {
        try {
            // Here you would implement the actual notification sending
            // For email notifications
            if (in_array($notification->preferred_method, ['email', 'both'])) {
                // Send email notification
                // Mail::to($notification->email)->send(new StockAvailableNotification($notification));
            }

            // For SMS notifications
            if (in_array($notification->preferred_method, ['sms', 'both']) && $notification->phone) {
                // Send SMS notification
                // SMS::send($notification->phone, $message);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send stock notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get notifications by customer
     */
    public function getByCustomer(int $customerId, ?string $status = null): Category
    {
        $cacheKey = $this->getCacheKey('customer', $customerId.'_'.$status);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use (
            $customerId,
            $status,
        ) {
            $query = $this->model->where('customer_id', $customerId);

            if ($status) {
                $query->where('status', $status);
            }

            return $query->orderBy('created_at', 'desc')->get();
        });
    }

    /**
     * Cancel notification
     */
    public function cancel(int $notificationId): bool
    {
        $notification = $this->find($notificationId);

        if (! $notification || $notification->status !== 'pending') {
            return false;
        }

        $notification->update(['status' => 'cancelled']);
        $this->clearCache();

        return true;
    }

    /**
     * Bulk cancel notifications
     */
    public function bulkCancel(array $notificationIds): int
    {
        $cancelled = $this->model
            ->whereIn('id', $notificationIds)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $this->clearCache();

        return $cancelled;
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(int $days = 30): array
    {
        $cacheKey = $this->getCacheKey('statistics', $days);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($days) {
            $startDate = now()->subDays($days);

            return [
                'total_notifications' => $this->model->where('created_at', '>=', $startDate)->count(),
                'pending_notifications' => $this->model
                    ->where('status', 'pending')
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                'sent_notifications' => $this->model
                    ->where('status', 'notified')
                    ->where('notified_at', '>=', $startDate)
                    ->count(),
                'cancelled_notifications' => $this->model
                    ->where('status', 'cancelled')
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                'top_requested_products' => $this->getTopRequestedProducts(10, $days),
                'notifications_by_method' => $this->model
                    ->where('created_at', '>=', $startDate)
                    ->selectRaw('preferred_method, count(*) as count')
                    ->groupBy('preferred_method')
                    ->pluck('count', 'preferred_method'),
            ];
        });
    }

    /**
     * Get top requested products
     */
    public function getTopRequestedProducts(int $limit = 10, int $days = 30): Category
    {
        $startDate = now()->subDays($days);

        return $this->model
            ->where('created_at', '>=', $startDate)
            ->selectRaw('product_type, product_id, product_handle, count(*) as request_count')
            ->groupBy('product_type', 'product_id', 'product_handle')
            ->orderBy('request_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean old notifications
     */
    public function cleanOldNotifications(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);

        $deleted = $this->model
            ->where('created_at', '<', $cutoffDate)
            ->whereIn('status', ['notified', 'cancelled'])
            ->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Get duplicate notifications
     */
    public function getDuplicates(
        int $customerId,
        string $productType,
        int $productId,
        ?array $variantData = null,
    ): Category {
        $query = $this->model
            ->where('customer_id', $customerId)
            ->where('product_type', $productType)
            ->where('product_id', $productId)
            ->where('status', 'pending');

        if ($variantData) {
            $query->where('variant_data', json_encode($variantData));
        }

        return $query->get();
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }

    /**
     * Find existing notification for user and product
     */
    public function findExisting(int $userId, int $productId): ?StockNotification
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('status', 'pending')
            ->first();
    }

    /**
     * Get pending notifications by product
     */
    public function getPendingByProduct(int $productId)
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('status', 'pending')
            ->with('user')
            ->get();
    }

    /**
     * Get notifications by user
     */
    public function getByUser(int $userId, ?string $status = null)
    {
        $query = $this->model->where('user_id', $userId)->with('product');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Bulk update notifications
     */
    public function bulkUpdate(array $notificationIds, array $data): int
    {
        $updated = $this->model->whereIn('id', $notificationIds)->update($data);

        if ($updated) {
            $this->clearCache();
        }

        return $updated;
    }

    /**
     * Cleanup old notifications
     */
    public function cleanupOld(int $daysOld = 30): int
    {
        return $this->model
            ->where('created_at', '<', now()->subDays($daysOld))
            ->whereIn('status', ['sent', 'failed', 'cancelled'])
            ->delete();
    }

    /**
     * Get popular products for notifications
     */
    public function getPopularProducts(int $limit = 10): array
    {
        return $this->model
            ->selectRaw('product_id, COUNT(*) as notification_count')
            ->groupBy('product_id')
            ->orderBy('notification_count', 'desc')
            ->limit($limit)
            ->with('product:id,name')
            ->get()
            ->toArray();
    }

    /**
     * Get all pending notifications
     */
    public function getAllPending()
    {
        return $this->model
            ->where('status', 'pending')
            ->with(['user', 'product'])
            ->get();
    }

    /**
     * Find notification by ID
     */
    public function findById(int $id): ?StockNotification
    {
        return $this->model->with(['user', 'product'])->find($id);
    }
}
