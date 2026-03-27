<?php

declare(strict_types=1);

namespace Cartino\GraphQL\Queries;

class SettingsQuery
{
    /**
     * Get settings
     */
    public function get(): array
    {
        return [
            'store_name' => setting('store.name', config('app.name')),
            'store_email' => setting('store.email', config('mail.from.address')),
            'store_phone' => setting('store.phone'),
            'currency' => setting('store.currency', 'USD'),
            'locale' => setting('store.locale', config('app.locale')),
            'timezone' => setting('store.timezone', config('app.timezone')),
        ];
    }
}
