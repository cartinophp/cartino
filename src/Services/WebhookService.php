<?php

namespace Cartino\Services;

use Cartino\Jobs\DispatchWebhookJob;
use Cartino\Models\Webhook;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch webhook for a specific event
     */
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::active()->forEvent($event)->get();

        Log::info('Dispatching webhooks', [
            'event' => $event,
            'webhook_count' => $webhooks->count(),
        ]);

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $event, $payload);
        }
    }

    /**
     * Dispatch a single webhook
     */
    protected function dispatchWebhook(Webhook $webhook, string $event, array $payload): void
    {
        $webhookPayload = $this->formatPayload($event, $payload);

        DispatchWebhookJob::dispatch($webhook, $webhookPayload);

        Log::info('Webhook job dispatched', [
            'webhook_id' => $webhook->id,
            'event' => $event,
        ]);
    }

    /**
     * Format payload with metadata
     */
    protected function formatPayload(string $event, array $payload): array
    {
        return [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'data' => $payload,
            'version' => '1.0',
        ];
    }

    /**
     * Register webhook events
     */
    public function registerEvents(): array
    {
        return [
            // Order events
            'order.created',
            'order.updated',
            'order.cancelled',
            'order.fulfilled',
            'order.paid',
            'order.refunded',
            // Product events
            'product.created',
            'product.updated',
            'product.deleted',
            'product.stock_low',
            'product.out_of_stock',
            // Customer events
            'customer.created',
            'customer.updated',
            'customer.deleted',
            // App events
            'app.installed',
            'app.uninstalled',
            'app.updated',
        ];
    }

    /**
     * Validate webhook signature
     */
    public function validateSignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Test webhook endpoint
     */
    public function testWebhook(Webhook $webhook): bool
    {
        $testPayload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toISOString(),
            'data' => [
                'message' => 'This is a test webhook',
                'webhook_id' => $webhook->id,
            ],
            'version' => '1.0',
        ];

        try {
            DispatchWebhookJob::dispatchSync($webhook, $testPayload);

            return true;
        } catch (\Exception $e) {
            Log::error('Webhook test failed', [
                'webhook_id' => $webhook->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
