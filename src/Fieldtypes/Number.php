<?php

namespace Cartino\Fieldtypes;

class Number extends Fieldtype
{
    public function process($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if ($this->config('format') === 'decimal') {
            return (float) $value;
        }

        return (int) $value;
    }

    public function toJsonSchema()
    {
        $type = $this->config('format') === 'decimal' ? 'number' : 'integer';

        return [
            'type' => $type,
            'title' => $this->display(),
            'description' => $this->instructions(),
            'minimum' => $this->config('min'),
            'maximum' => $this->config('max'),
            'multipleOf' => $this->config('step', 1),
        ];
    }

    public function toGqlType()
    {
        return [
            'type' => $this->config('format') === 'decimal' ? 'Float' : 'Int',
        ];
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules[] = 'numeric';

        if ($min = $this->config('min')) {
            $rules[] = "min:$min";
        }

        if ($max = $this->config('max')) {
            $rules[] = "max:$max";
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
