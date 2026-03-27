<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class UnitsOfMeasure extends BasicDictionary
{
    protected array $keywords = ['units', 'measure', 'weight', 'dimension'];

    protected function getItems(): array
    {
        return [
            // Weight
            ['value' => 'g', 'label' => 'Grams', 'type' => 'weight', 'system' => 'metric', 'symbol' => 'g'],
            ['value' => 'kg', 'label' => 'Kilograms', 'type' => 'weight', 'system' => 'metric', 'symbol' => 'kg'],
            ['value' => 'lb', 'label' => 'Pounds', 'type' => 'weight', 'system' => 'imperial', 'symbol' => 'lb'],
            ['value' => 'oz', 'label' => 'Ounces', 'type' => 'weight', 'system' => 'imperial', 'symbol' => 'oz'],
            // Length
            ['value' => 'mm', 'label' => 'Millimeters', 'type' => 'length', 'system' => 'metric', 'symbol' => 'mm'],
            ['value' => 'cm', 'label' => 'Centimeters', 'type' => 'length', 'system' => 'metric', 'symbol' => 'cm'],
            ['value' => 'm', 'label' => 'Meters', 'type' => 'length', 'system' => 'metric', 'symbol' => 'm'],
            ['value' => 'in', 'label' => 'Inches', 'type' => 'length', 'system' => 'imperial', 'symbol' => 'in'],
            ['value' => 'ft', 'label' => 'Feet', 'type' => 'length', 'system' => 'imperial', 'symbol' => 'ft'],
            // Volume
            ['value' => 'ml', 'label' => 'Milliliters', 'type' => 'volume', 'system' => 'metric', 'symbol' => 'ml'],
            ['value' => 'l', 'label' => 'Liters', 'type' => 'volume', 'system' => 'metric', 'symbol' => 'l'],
            [
                'value' => 'fl_oz',
                'label' => 'Fluid Ounces',
                'type' => 'volume',
                'system' => 'imperial',
                'symbol' => 'fl oz',
            ],
            ['value' => 'gal', 'label' => 'Gallons', 'type' => 'volume', 'system' => 'imperial', 'symbol' => 'gal'],
        ];
    }
}
