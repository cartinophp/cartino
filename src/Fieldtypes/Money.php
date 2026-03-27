<?php

namespace Cartino\Fieldtypes;

class Money extends Fieldtype
{
    public function process($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Store as cents/smallest currency unit
        return (int) ($value * 100);
    }

    public function preProcess($value)
    {
        if (is_null($value)) {
            return null;
        }

        // Convert from cents to currency units
        return $value / 100;
    }

    public function augment($value)
    {
        if (is_null($value)) {
            return null;
        }

        $currency = $this->config('currency', 'USD');
        $locale = $this->config('locale', 'en_US');

        return [
            'amount' => $value / 100,
            'cents' => $value,
            'currency' => $currency,
            'formatted' => $this->format($value, $currency, $locale),
        ];
    }

    public function format($value, $currency = 'USD', $locale = 'en_US')
    {
        if (is_null($value)) {
            return null;
        }

        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($value / 100, $currency);
    }

    public function toJsonSchema()
    {
        return [
            'type' => 'number',
            'title' => $this->display(),
            'description' => $this->instructions(),
            'minimum' => 0,
            'multipleOf' => 0.01,
        ];
    }

    public function toGqlType()
    {
        return [
            'type' => 'Money',
        ];
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules[] = 'numeric';
        $rules[] = 'min:0';

        return $rules;
    }

    public function isSortable()
    {
        return true;
    }

    public function isFilterable()
    {
        return true;
    }
}
