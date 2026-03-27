<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class Locales extends BasicDictionary
{
    protected string $valueKey = 'code';

    protected array $keywords = ['locales', 'locale', 'language', 'region'];

    protected array $searchable = ['code', 'name', 'language', 'country'];

    protected function getItemLabel(array $item): string
    {
        return $item['name'];
    }

    protected function getItems(): array
    {
        return [
            ['code' => 'it_IT', 'name' => 'Italian (Italy)', 'language' => 'Italian', 'country' => 'Italy'],
            [
                'code' => 'en_US',
                'name' => 'English (United States)',
                'language' => 'English',
                'country' => 'United States',
            ],
            [
                'code' => 'en_GB',
                'name' => 'English (United Kingdom)',
                'language' => 'English',
                'country' => 'United Kingdom',
            ],
            ['code' => 'de_DE', 'name' => 'German (Germany)', 'language' => 'German', 'country' => 'Germany'],
            ['code' => 'fr_FR', 'name' => 'French (France)', 'language' => 'French', 'country' => 'France'],
            ['code' => 'es_ES', 'name' => 'Spanish (Spain)', 'language' => 'Spanish', 'country' => 'Spain'],
            ['code' => 'pt_PT', 'name' => 'Portuguese (Portugal)', 'language' => 'Portuguese', 'country' => 'Portugal'],
            ['code' => 'pt_BR', 'name' => 'Portuguese (Brazil)', 'language' => 'Portuguese', 'country' => 'Brazil'],
            ['code' => 'nl_NL', 'name' => 'Dutch (Netherlands)', 'language' => 'Dutch', 'country' => 'Netherlands'],
            ['code' => 'pl_PL', 'name' => 'Polish (Poland)', 'language' => 'Polish', 'country' => 'Poland'],
        ];
    }
}
