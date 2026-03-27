<?php

namespace Cartino\Fieldtypes;

use Carbon\Carbon;

class Date extends Fieldtype
{
    public function process($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        try {
            return $this->parseDate($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function preProcess($value)
    {
        if (is_null($value)) {
            return null;
        }

        try {
            return $this->parseDate($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function augment($value)
    {
        if (is_null($value)) {
            return null;
        }

        try {
            $date = $this->parseDate($value);
            $format = $this->config('format', 'Y-m-d');

            return [
                'date' => $date->format('Y-m-d'),
                'formatted' => $date->format($format),
                'timestamp' => $date->getTimestamp(),
                'iso' => $date->toISOString(),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        return new \DateTime($value);
    }

    public function toJsonSchema()
    {
        return [
            'type' => 'string',
            'format' => 'date',
            'title' => $this->display(),
            'description' => $this->instructions(),
        ];
    }

    public function toGqlType()
    {
        return [
            'type' => 'Date',
        ];
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = 'date';

        if ($earliest = $this->config('earliest_date')) {
            $rules[] = "after_or_equal:$earliest";
        }

        if ($latest = $this->config('latest_date')) {
            $rules[] = "before_or_equal:$latest";
        }

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
