<?php

namespace Cartino\Cp;

use Illuminate\Support\Collection;

class Dashboard
{
    protected static array $cards = [];

    protected static array $quickActions = [];

    protected static array $metrics = [];

    /**
     * Register a dashboard card
     */
    public static function card(string $component, array $props = [], int $order = 100): void
    {
        static::$cards[] = [
            'component' => $component,
            'props' => $props,
            'order' => $order,
        ];
    }

    /**
     * Register a quick action
     */
    public static function quickAction(
        string $label,
        string $url,
        ?string $icon = null,
        array $permissions = [],
    ): void {
        static::$quickActions[] = [
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
            'permissions' => $permissions,
        ];
    }

    /**
     * Register a metric
     */
    public static function metric(
        string $label,
        callable $value,
        ?string $icon = null,
        string $color = 'blue',
    ): void {
        static::$metrics[] = [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'color' => $color,
        ];
    }

    /**
     * Get all dashboard cards
     */
    public static function cards(): Collection
    {
        return collect(static::$cards)->sortBy('order')->values();
    }

    /**
     * Get all quick actions
     */
    public static function quickActions(): Collection
    {
        return collect(static::$quickActions);
    }

    /**
     * Get all metrics
     */
    public static function metrics(): Collection
    {
        return collect(static::$metrics)->map(function ($metric) {
            $metric['value'] = is_callable($metric['value']) ? call_user_func($metric['value']) : $metric['value'];

            return $metric;
        });
    }

    /**
     * Get dashboard data for API
     */
    public static function data(): array
    {
        return [
            'cards' => static::cards(),
            'quick_actions' => static::quickActions(),
            'metrics' => static::metrics(),
            'recent_orders' => static::getRecentOrders(),
            'sales_stats' => static::getSalesStats(),
        ];
    }

    /**
     * Get recent orders
     */
    protected static function getRecentOrders(): Collection
    {
        return collect(); // Will be implemented with Order model
    }

    /**
     * Get sales statistics
     */
    protected static function getSalesStats(): array
    {
        return [
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
            'total' => 0,
        ];
    }
}
