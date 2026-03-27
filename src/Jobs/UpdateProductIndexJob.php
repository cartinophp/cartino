<?php

namespace Cartino\Jobs;

use Cartino\Models\Product;
use Cartino\Services\CacheService;
use Cartino\Services\SearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateProductIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(
        private Product $product,
        private string $action = 'update', // update, delete
    ) {
        $this->onQueue('indexing');
    }

    public function handle(SearchService $search, CacheService $cache): void
    {
        Log::info('Updating product search index', [
            'product_id' => $this->product->id,
            'action' => $this->action,
        ]);

        try {
            switch ($this->action) {
                case 'update':
                    $search->indexProduct($this->product);
                    break;
                case 'delete':
                    $search->removeProduct($this->product);
                    break;
            }

            // Clear product cache
            $cache->invalidateProduct($this->product->id);

            Log::info('Product search index updated successfully', [
                'product_id' => $this->product->id,
                'action' => $this->action,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update product search index', [
                'product_id' => $this->product->id,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
