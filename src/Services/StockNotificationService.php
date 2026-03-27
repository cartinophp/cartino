<?php

namespace Cartino\Services;

use Cartino\DTO\StockNotification\StockNotificationData;
use Cartino\Jobs\SendStockNotificationEmail;
use Cartino\Models\StockNotification;
use Cartino\Repositories\StockNotificationRepository;
use Illuminate\Support\Facades\Log;

class StockNotificationService
{
    public function __construct(
        private StockNotificationRepository $repository,
    ) {}

    /**
     * Create new stock notification
     */
    public function createNotification(array $data): StockNotificationData
    {
        // Check if user already has notification for this product
        $existing = $this->repository->findExisting($data['user_id'], $data['product_id']);

        if ($existing) {
            // Update existing notification
            $notification = $this->repository->update($existing->id, [
                'status' => 'pending',
                'created_at' => now(),
            ]);
        } else {
            // Create new notification
            $notification = $this->repository->create($data);
        }

        return StockNotificationData::fromModel($notification);
    }

    /**
     * Process stock notifications for a product
     */
    public function processProductNotifications(int $productId, int $newStock): int
    {
        $notifications = $this->repository->getPendingByProduct($productId);
        $sent = 0;

        foreach ($notifications as $notification) {
            if ($this->sendNotification($notification)) {
                $sent++;
            }
        }

        Log::info("Sent {$sent} stock notifications for product {$productId}");

        return $sent;
    }

    /**
     * Send individual notification
     */
    public function sendNotification(StockNotification $notification): bool
    {
        try {
            // Update status to sending
            $this->repository->update($notification->id, [
                'status' => 'sending',
                'sent_at' => now(),
            ]);

            // Dispatch email job
            SendStockNotificationEmail::dispatch($notification);

            // Update status to sent
            $this->repository->update($notification->id, [
                'status' => 'sent',
            ]);

            return true;
        } catch (\Exception $e) {
            // Update status to failed
            $this->repository->update($notification->id, [
                'status' => 'failed',
            ]);

            Log::error('Failed to send stock notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cancel notification
     */
    public function cancelNotification(StockNotification $notification): bool
    {
        return $this->repository->update($notification->id, [
            'status' => 'cancelled',
        ])
            ? true
            : false;
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId, ?string $status = null): array
    {
        $notifications = $this->repository->getByUser($userId, $status);

        return $notifications->map(fn ($notification) => StockNotificationData::fromModel($notification))->toArray();
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Bulk cancel notifications
     */
    public function bulkCancel(array $notificationIds): int
    {
        return $this->repository->bulkUpdate($notificationIds, [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Auto-cleanup old notifications
     */
    public function cleanupOldNotifications(int $daysOld = 30): int
    {
        return $this->repository->cleanupOld($daysOld);
    }

    /**
     * Check if user can create notification for product
     */
    public function canCreateNotification(int $userId, int $productId): bool
    {
        $existing = $this->repository->findExisting($userId, $productId);

        // Allow if no existing notification or if existing is not pending
        return ! $existing || $existing->status !== 'pending';
    }

    /**
     * Get popular products for notifications
     */
    public function getPopularNotificationProducts(int $limit = 10): array
    {
        return $this->repository->getPopularProducts($limit);
    }

    /**
     * Process all pending notifications (for cron job)
     */
    public function processAllPendingNotifications(): array
    {
        $pendingNotifications = $this->repository->getAllPending();
        $results = [
            'total' => $pendingNotifications->count(),
            'sent' => 0,
            'failed' => 0,
        ];

        foreach ($pendingNotifications as $notification) {
            if ($this->sendNotification($notification)) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Get notification by ID
     */
    public function getNotification(int $id): ?StockNotificationData
    {
        $notification = $this->repository->findById($id);

        return $notification ? StockNotificationData::fromModel($notification) : null;
    }

    /**
     * Delete notification
     */
    public function deleteNotification(StockNotification $notification): bool
    {
        return $this->repository->delete($notification->id);
    }
}
