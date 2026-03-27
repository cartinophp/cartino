<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class VatRates extends BasicDictionary
{
    protected string $valueKey = 'country';

    protected array $keywords = ['vat', 'tax', 'europe', 'eu'];

    protected function getItemLabel(array $item): string
    {
        return "{$item['country']} - {$item['rate']}%";
    }

    protected function getItems(): array
    {
        return [
            ['country' => 'IT', 'name' => 'Italy', 'rate' => 22, 'reduced_rates' => [10, 5, 4]],
            ['country' => 'DE', 'name' => 'Germany', 'rate' => 19, 'reduced_rates' => [7]],
            ['country' => 'FR', 'name' => 'France', 'rate' => 20, 'reduced_rates' => [10, 5.5, 2.1]],
            ['country' => 'ES', 'name' => 'Spain', 'rate' => 21, 'reduced_rates' => [10, 4]],
            ['country' => 'NL', 'name' => 'Netherlands', 'rate' => 21, 'reduced_rates' => [9]],
            ['country' => 'BE', 'name' => 'Belgium', 'rate' => 21, 'reduced_rates' => [12, 6]],
            ['country' => 'AT', 'name' => 'Austria', 'rate' => 20, 'reduced_rates' => [13, 10]],
            ['country' => 'PT', 'name' => 'Portugal', 'rate' => 23, 'reduced_rates' => [13, 6]],
            ['country' => 'GR', 'name' => 'Greece', 'rate' => 24, 'reduced_rates' => [13, 6]],
            ['country' => 'PL', 'name' => 'Poland', 'rate' => 23, 'reduced_rates' => [8, 5]],
            ['country' => 'SE', 'name' => 'Sweden', 'rate' => 25, 'reduced_rates' => [12, 6]],
            ['country' => 'DK', 'name' => 'Denmark', 'rate' => 25, 'reduced_rates' => []],
            ['country' => 'FI', 'name' => 'Finland', 'rate' => 24, 'reduced_rates' => [14, 10]],
            ['country' => 'IE', 'name' => 'Ireland', 'rate' => 23, 'reduced_rates' => [13.5, 9, 4.8]],
            ['country' => 'CZ', 'name' => 'Czech Republic', 'rate' => 21, 'reduced_rates' => [15, 10]],
            ['country' => 'HU', 'name' => 'Hungary', 'rate' => 27, 'reduced_rates' => [18, 5]],
            ['country' => 'RO', 'name' => 'Romania', 'rate' => 19, 'reduced_rates' => [9, 5]],
            ['country' => 'GB', 'name' => 'United Kingdom', 'rate' => 20, 'reduced_rates' => [5]],
        ];
    }
}
