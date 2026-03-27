<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class PhonePrefixes extends BasicDictionary
{
    protected string $valueKey = 'code';

    protected array $keywords = ['phone', 'telephone', 'mobile', 'prefix', 'calling code'];

    protected array $searchable = ['code', 'prefix', 'country'];

    protected function getItemLabel(array $item): string
    {
        return "{$item['country']} ({$item['prefix']})";
    }

    protected function getItems(): array
    {
        return [
            ['code' => 'IT', 'country' => 'Italy', 'prefix' => '+39'],
            ['code' => 'US', 'country' => 'United States', 'prefix' => '+1'],
            ['code' => 'CA', 'country' => 'Canada', 'prefix' => '+1'],
            ['code' => 'GB', 'country' => 'United Kingdom', 'prefix' => '+44'],
            ['code' => 'DE', 'country' => 'Germany', 'prefix' => '+49'],
            ['code' => 'FR', 'country' => 'France', 'prefix' => '+33'],
            ['code' => 'ES', 'country' => 'Spain', 'prefix' => '+34'],
            ['code' => 'PT', 'country' => 'Portugal', 'prefix' => '+351'],
            ['code' => 'NL', 'country' => 'Netherlands', 'prefix' => '+31'],
            ['code' => 'BE', 'country' => 'Belgium', 'prefix' => '+32'],
            ['code' => 'CH', 'country' => 'Switzerland', 'prefix' => '+41'],
            ['code' => 'AT', 'country' => 'Austria', 'prefix' => '+43'],
            ['code' => 'GR', 'country' => 'Greece', 'prefix' => '+30'],
            ['code' => 'PL', 'country' => 'Poland', 'prefix' => '+48'],
            ['code' => 'SE', 'country' => 'Sweden', 'prefix' => '+46'],
            ['code' => 'NO', 'country' => 'Norway', 'prefix' => '+47'],
            ['code' => 'DK', 'country' => 'Denmark', 'prefix' => '+45'],
            ['code' => 'FI', 'country' => 'Finland', 'prefix' => '+358'],
            ['code' => 'IE', 'country' => 'Ireland', 'prefix' => '+353'],
            ['code' => 'CZ', 'country' => 'Czech Republic', 'prefix' => '+420'],
            ['code' => 'HU', 'country' => 'Hungary', 'prefix' => '+36'],
            ['code' => 'RO', 'country' => 'Romania', 'prefix' => '+40'],
            ['code' => 'JP', 'country' => 'Japan', 'prefix' => '+81'],
            ['code' => 'CN', 'country' => 'China', 'prefix' => '+86'],
            ['code' => 'IN', 'country' => 'India', 'prefix' => '+91'],
            ['code' => 'AU', 'country' => 'Australia', 'prefix' => '+61'],
            ['code' => 'NZ', 'country' => 'New Zealand', 'prefix' => '+64'],
            ['code' => 'ZA', 'country' => 'South Africa', 'prefix' => '+27'],
            ['code' => 'BR', 'country' => 'Brazil', 'prefix' => '+55'],
            ['code' => 'AR', 'country' => 'Argentina', 'prefix' => '+54'],
            ['code' => 'MX', 'country' => 'Mexico', 'prefix' => '+52'],
        ];
    }
}
