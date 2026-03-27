<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'event',
        'endpoint_url',
        'secret',
        'method',
        'headers',
        'is_active',
        'success_count',
        'failure_count',
        'last_success_at',
        'last_failure_at',
        'last_error',
        'max_attempts',
        'timeout_seconds',
    ];

    protected $casts = [
        'headers' => 'array',
        'is_active' => 'boolean',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];

    // Relationships
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    // Accessors
    public function getSuccessRateAttribute(): float
    {
        $total = $this->success_count + $this->failure_count;

        if ($total === 0) {
            return 100;
        }

        return ($this->success_count / $total) * 100;
    }

    public function getIsHealthyAttribute(): bool
    {
        return $this->success_rate >= 95;
    }

    // Methods
    public function recordSuccess(): void
    {
        $this->increment('success_count');
        $this->update([
            'last_success_at' => now(),
            'last_error' => null,
        ]);
    }

    public function recordFailure(string $error): void
    {
        $this->increment('failure_count');
        $this->update([
            'last_failure_at' => now(),
            'last_error' => $error,
        ]);

        // Auto-disable if too many failures
        if ($this->failure_count >= 10 && $this->success_rate < 50) {
            $this->disable();
        }
    }

    public function enable(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function disable(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function test(): bool
    {
        // TODO: Implement webhook testing
        return $this->fire('webhook.test', ['test' => true]);
    }

    public function fire(string $event, array $payload = []): bool
    {
        if (! $this->is_active) {
            return false;
        }

        try {
            $response = \Http::timeout($this->timeout_seconds)->withHeaders(
                $this->headers ?? [],
            )->{strtolower($this->method)}($this->endpoint_url, [
                'event' => $event,
                'payload' => $payload,
                'timestamp' => now()->toISOString(),
                'signature' => $this->generateSignature($payload),
            ]);

            if ($response->successful()) {
                $this->recordSuccess();

                return true;
            } else {
                $this->recordFailure("HTTP {$response->status()}: {$response->body()}");

                return false;
            }
        } catch (\Exception $e) {
            $this->recordFailure($e->getMessage());

            return false;
        }
    }

    private function generateSignature(array $payload): ?string
    {
        if (! $this->secret) {
            return null;
        }

        return hash_hmac('sha256', json_encode($payload), $this->secret);
    }
}
