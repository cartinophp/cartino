<?php

namespace Cartino\Jobs;

use Cartino\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public array $backoff = [10, 30, 60]; // Retry delays in seconds

    public function __construct(
        private Webhook $webhook,
        private array $payload,
    ) {
        $this->onQueue('webhooks');
    }

    public function handle(): void
    {
        Log::info('Dispatching webhook', [
            'webhook_id' => $this->webhook->id,
            'url' => $this->webhook->url,
            'event' => $this->payload['event'] ?? 'unknown',
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->retry(2, 1000) // Retry 2 times with 1 second delay
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Shopper-Signature' => $this->generateSignature(),
                    'X-Shopper-Event' => $this->payload['event'] ?? 'unknown',
                    'User-Agent' => 'Laravel-Shopper-Webhook/1.0',
                ])
                ->post($this->webhook->url, $this->payload);

            if ($response->successful()) {
                Log::info('Webhook dispatched successfully', [
                    'webhook_id' => $this->webhook->id,
                    'status_code' => $response->status(),
                    'response_time' => $response->transferStats?->getTransferTime(),
                ]);

                // Update webhook success stats
                $this->webhook->increment('success_count');
                $this->webhook->update(['last_success_at' => now()]);
            } else {
                throw new \Exception("HTTP {$response->status()}: {$response->body()}");
            }
        } catch (\Exception $e) {
            Log::error('Webhook dispatch failed', [
                'webhook_id' => $this->webhook->id,
                'url' => $this->webhook->url,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Update webhook failure stats
            $this->webhook->increment('failure_count');
            $this->webhook->update(['last_failure_at' => now()]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Webhook dispatch failed permanently', [
            'webhook_id' => $this->webhook->id,
            'url' => $this->webhook->url,
            'error' => $exception->getMessage(),
            'total_attempts' => $this->attempts(),
        ]);

        // Optionally disable webhook after too many failures
        if ($this->webhook->failure_count > 50) {
            $this->webhook->update([
                'is_active' => false,
                'disabled_reason' => 'Too many consecutive failures',
            ]);
        }
    }

    private function generateSignature(): string
    {
        if (! $this->webhook->secret) {
            return '';
        }

        return hash_hmac('sha256', json_encode($this->payload), $this->webhook->secret);
    }
}
