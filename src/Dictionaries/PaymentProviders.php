<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class PaymentProviders extends BasicDictionary
{
    protected array $keywords = ['payment', 'gateway', 'provider', 'checkout'];

    protected function getItems(): array
    {
        return [
            [
                'value' => 'stripe',
                'label' => 'Stripe',
                'supports' => ['card', 'apple_pay', 'google_pay'],
                'countries' => ['*'],
            ],
            ['value' => 'paypal', 'label' => 'PayPal', 'supports' => ['paypal', 'venmo'], 'countries' => ['*']],
            [
                'value' => 'square',
                'label' => 'Square',
                'supports' => ['card', 'apple_pay', 'google_pay'],
                'countries' => ['US', 'CA', 'GB', 'AU'],
            ],
            [
                'value' => 'braintree',
                'label' => 'Braintree',
                'supports' => ['card', 'paypal', 'apple_pay'],
                'countries' => ['*'],
            ],
            [
                'value' => 'mollie',
                'label' => 'Mollie',
                'supports' => ['ideal', 'bancontact', 'card'],
                'countries' => ['NL', 'BE', 'DE', 'FR'],
            ],
            ['value' => 'satispay', 'label' => 'Satispay', 'supports' => ['satispay'], 'countries' => ['IT']],
            [
                'value' => 'bank_transfer',
                'label' => __('cartino::dictionaries.payment_bank_transfer'),
                'supports' => ['bank_transfer'],
                'countries' => ['*'],
            ],
            [
                'value' => 'cash_on_delivery',
                'label' => __('cartino::dictionaries.payment_cash_on_delivery'),
                'supports' => ['cash'],
                'countries' => ['*'],
            ],
        ];
    }
}
