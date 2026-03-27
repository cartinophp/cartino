<?php

namespace Cartino\Fieldtypes;

class Toggle extends Fieldtype
{
    public function process($value)
    {
        return (bool) $value;
    }

    public function preProcess($value)
    {
        return (bool) $value;
    }

    public function augment($value)
    {
        return (bool) $value;
    }

    public function toJsonSchema()
    {
        return [
            'type' => 'boolean',
            'title' => $this->display(),
            'description' => $this->instructions(),
            'default' => (bool) $this->defaultValue(),
        ];
    }

    public function toGqlType()
    {
        return [
            'type' => 'Boolean',
        ];
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = 'boolean';

        return $rules;
    }

    public function defaultValue()
    {
        return (bool) $this->config('default', false);
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
