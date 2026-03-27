<?php

namespace Cartino\Cp;

use Cartino\Models\Order;
use Illuminate\Support\Collection;

class Navigation
{
    protected static array $items = [];

    protected static array $sections = [];

    /**
     * Add navigation item
     */
    public static function item(string $name): NavigationItem
    {
        $item = new NavigationItem($name);
        static::$items[$name] = $item;

        return $item;
    }

    /**
     * Add navigation section
     */
    public static function section(string $name, ?string $label = null): NavigationSection
    {
        $section = new NavigationSection($name, $label ?? $name);
        static::$sections[$name] = $section;

        return $section;
    }

    /**
     * Get all navigation items
     */
    public static function items(): Collection
    {
        return collect(static::$items)->filter(fn ($item) => $item->canView())->sortBy('order')->values();
    }

    /**
     * Get all navigation sections
     */
    public static function sections(): Collection
    {
        return collect(static::$sections)
            ->map(function ($section) {
                $section->items = $section->items()->filter(fn ($item) => $item->canView())->sortBy('order')->values();

                return $section;
            })
            ->filter(fn ($section) => $section->items->isNotEmpty())
            ->sortBy('order')
            ->values();
    }

    /**
     * Get navigation tree for frontend
     */
    public static function tree(): array
    {
        return [
            'sections' => static::sections(),
            'items' => static::items(),
        ];
    }

    /**
     * Register default Shopper navigation
     */
    public static function registerDefaults(): void
    {
        // Dashboard
        static::item('dashboard')
            ->label('Dashboard')
            ->icon('home')
            ->url('/cp')
            ->order(1);

        // Orders Section
        static::section('orders', 'Orders')->order(10);

        static::item('orders.index')
            ->label('Orders')
            ->icon('shopping-bag')
            ->url('/cp/orders')
            ->section('orders')
            ->badge(fn () => Order::pending()->count())
            ->order(1);

        static::item('orders.drafts')
            ->label('Draft orders')
            ->url('/cp/orders/drafts')
            ->section('orders')
            ->order(2);

        static::item('orders.abandoned')
            ->label('Abandoned checkouts')
            ->url('/cp/orders/abandoned')
            ->section('orders')
            ->order(3);

        // Products Section
        static::section('catalog', 'Products')->order(20);

        static::item('products.index')
            ->label('Products')
            ->icon('package')
            ->url('/cp/products')
            ->section('catalog')
            ->order(1);

        static::item('collections.index')
            ->label('Collections')
            ->icon('folder')
            ->url('/cp/collections')
            ->section('catalog')
            ->order(2);

        static::item('inventory')
            ->label('Inventory')
            ->icon('archive')
            ->url('/cp/inventory')
            ->section('catalog')
            ->order(3);

        static::item('gift-cards')
            ->label('Gift cards')
            ->icon('gift')
            ->url('/cp/gift-cards')
            ->section('catalog')
            ->order(4);

        // Customers Section
        static::section('customers', 'Customers')->order(30);

        static::item('customers.index')
            ->label('Customers')
            ->icon('users')
            ->url('/cp/customers')
            ->section('customers')
            ->order(1);

        static::item('customers.segments')
            ->label('Segments')
            ->url('/cp/customers/segments')
            ->section('customers')
            ->order(2);

        // Content Section
        static::section('content', 'Content')->order(40);

        static::item('pages.index')
            ->label('Pages')
            ->icon('file-text')
            ->url('/cp/pages')
            ->section('content')
            ->order(1);

        static::item('blog.posts')
            ->label('Blog posts')
            ->icon('edit-3')
            ->url('/cp/blog/posts')
            ->section('content')
            ->order(2);

        static::item('navigation')
            ->label('Navigation')
            ->icon('menu')
            ->url('/cp/navigation')
            ->section('content')
            ->order(3);

        // Analytics Section
        static::section('analytics', 'Analytics')->order(50);

        static::item('analytics.overview')
            ->label('Overview')
            ->icon('trending-up')
            ->url('/cp/analytics')
            ->section('analytics')
            ->order(1);

        static::item('analytics.reports')
            ->label('Reports')
            ->url('/cp/analytics/reports')
            ->section('analytics')
            ->order(2);

        // Marketing Section
        static::section('marketing', 'Marketing')->order(60);

        static::item('discounts')
            ->label('Discounts')
            ->icon('percent')
            ->url('/cp/discounts')
            ->section('marketing')
            ->order(1);

        static::item('marketing.campaigns')
            ->label('Marketing')
            ->icon('megaphone')
            ->url('/cp/marketing')
            ->section('marketing')
            ->order(2);

        // Settings Section
        static::section('settings', 'Settings')->order(100);

        static::item('settings.general')
            ->label('General')
            ->icon('settings')
            ->url('/cp/settings')
            ->section('settings')
            ->order(1);

        static::item('settings.payments')
            ->label('Payments')
            ->url('/cp/settings/payments')
            ->section('settings')
            ->order(2);

        static::item('settings.shipping')
            ->label('Shipping and delivery')
            ->url('/cp/settings/shipping')
            ->section('settings')
            ->order(3);

        static::item('settings.taxes')
            ->label('Taxes')
            ->url('/cp/settings/taxes')
            ->section('settings')
            ->order(4);

        static::item('settings.notifications')
            ->label('Notifications')
            ->url('/cp/settings/notifications')
            ->section('settings')
            ->order(5);

        // Apps Section
        static::section('apps', 'Apps')->order(90);

        static::item('apps')
            ->label('Apps')
            ->icon('grid')
            ->url('/cp/apps')
            ->section('apps')
            ->order(1);
    }
}
