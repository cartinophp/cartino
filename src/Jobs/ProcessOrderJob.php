<?php

namespace Cartino\Jobs;

use Cartino\Models\Order;
use Cartino\Services\CacheService;
use Cartino\Services\InventoryService;
use Cartino\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 1;

    public int $timeout = 120;

    public function __construct(
        private Order $order,
    ) {
        $this->onQueue('orders');
    }

    public function handle(InventoryService $inventory, NotificationService $notifications, CacheService $cache): void
    {
        Log::info('Processing order', ['order_id' => $this->order->id]);

        DB::transaction(function () use ($inventory, $notifications, $cache) {
            try {
                // 1. Reduce inventory
                $inventory->reduceStock($this->order);

                // 2. Update order status
                $this->order->update([
                    'status' => 'processing',
                    'processed_at' => now(),
                ]);

                // 3. Send notifications
                $notifications->sendOrderConfirmation($this->order);
                $notifications->notifyAdmins($this->order);

                // 4. Clear related cache
                $cache->invalidateProduct();
                $cache->invalidateStats();

                Log::info('Order processed successfully', [
                    'order_id' => $this->order->id,
                    'total' => $this->order->total_amount,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to process order', [
                    'order_id' => $this->order->id,
                    'error' => $e->getMessage(),
                ]);

                // Mark order as failed
                $this->order->update(['status' => 'failed']);

                throw $e;
            }
        });
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Order processing job failed permanently', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Mark order as failed
        $this->order->update([
            'status' => 'failed',
            'failure_reason' => $exception->getMessage(),
        ]);

        // Send failure notification to admins
        app(NotificationService::class)->notifyOrderFailure($this->order, $exception);
    }
}
