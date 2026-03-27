<?php

declare(strict_types=1);

namespace Cartino\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'event_type',
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'properties',
        'context',
        'occurred_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'context' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = 'analytics_events';
        parent::__construct($attributes);
    }

    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeInDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('occurred_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('occurred_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('occurred_at', now()->month)->whereYear('occurred_at', now()->year);
    }

    public function scopeLastDays($query, int $days)
    {
        return $query->where('occurred_at', '>=', now()->subDays($days));
    }

    public function getProperty(string $key, mixed $default = null): mixed
    {
        return data_get($this->properties, $key, $default);
    }

    public function getContext(string $key, mixed $default = null): mixed
    {
        return data_get($this->context, $key, $default);
    }

    public static function track(
        string $eventType,
        array $properties = [],
        array $context = [],
        ?string $sessionId = null,
        ?string $userId = null,
    ): self {
        return self::create([
            'event_type' => $eventType,
            'session_id' => $sessionId ?? session()->getId(),
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->headers->get('referer'),
            'properties' => $properties,
            'context' => array_merge([
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString(),
            ], $context),
            'occurred_at' => now(),
        ]);
    }

    public static function pageView(string $page, array $properties = []): self
    {
        return self::track('page_view', array_merge([
            'page' => $page,
            'title' => $properties['title'] ?? null,
        ], $properties));
    }

    public static function productView(int $productId, array $properties = []): self
    {
        return self::track('product_view', array_merge([
            'product_id' => $productId,
        ], $properties));
    }

    public static function addToCart(int $productId, int $quantity = 1, array $properties = []): self
    {
        return self::track('add_to_cart', array_merge([
            'product_id' => $productId,
            'quantity' => $quantity,
        ], $properties));
    }

    public static function removeFromCart(int $productId, int $quantity = 1, array $properties = []): self
    {
        return self::track('remove_from_cart', array_merge([
            'product_id' => $productId,
            'quantity' => $quantity,
        ], $properties));
    }

    public static function orderPlaced(int $orderId, array $properties = []): self
    {
        return self::track('order_placed', array_merge([
            'order_id' => $orderId,
        ], $properties));
    }

    public static function customerRegistered(int $customerId, array $properties = []): self
    {
        return self::track('customer_registered', array_merge([
            'customer_id' => $customerId,
        ], $properties));
    }

    public static function searchPerformed(string $query, array $properties = []): self
    {
        return self::track('search_performed', array_merge([
            'search_query' => $query,
        ], $properties));
    }
}
