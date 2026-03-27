<?php

namespace Cartino\Fieldtypes;

use Cartino\Fields\Field;

abstract class Fieldtype
{
    protected $field;

    protected $config = [];

    public function setField(Field $field)
    {
        $this->field = $field;
        $this->config = $field->config()->all();

        return $this;
    }

    public function field()
    {
        return $this->field;
    }

    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config;
        }

        return data_get($this->config, $key, $default);
    }

    public function handle()
    {
        return $this->field->handle();
    }

    /**
     * Process the value before saving to storage
     */
    public function process($value)
    {
        return $value;
    }

    /**
     * Process the value before editing in forms
     */
    public function preProcess($value)
    {
        return $value;
    }

    /**
     * Augment the value for front-end display
     */
    public function augment($value)
    {
        return $value;
    }

    /**
     * Get GraphQL type definition
     */
    public function toGqlType()
    {
        return [
            'type' => 'String',
        ];
    }

    /**
     * Get JSON Schema definition
     */
    public function toJsonSchema()
    {
        return [];
    }

    /**
     * Get validation rules
     */
    public function rules()
    {
        $rules = [];

        if ($this->config('required')) {
            $rules[] = 'required';
        }

        if ($max = $this->config('max_length')) {
            $rules[] = "max:$max";
        }

        if ($min = $this->config('min_length')) {
            $rules[] = "min:$min";
        }

        return $rules;
    }

    /**
     * Get the default value
     */
    public function defaultValue()
    {
        return $this->config('default');
    }

    /**
     * Check if the field is required
     */
    public function isRequired()
    {
        return $this->config('required', false);
    }

    /**
     * Get the field's display name
     */
    public function display()
    {
        return $this->config('display', $this->handle());
    }

    /**
     * Get the field's instructions
     */
    public function instructions()
    {
        return $this->config('instructions');
    }

    /**
     * Get the field's width (for form layouts)
     */
    public function width()
    {
        return $this->config('width', 100);
    }

    /**
     * Check if the field is localizable
     */
    public function isLocalizable()
    {
        return $this->config('localizable', false);
    }

    /**
     * Check if the field is listable (shown in listing views)
     */
    public function isListable()
    {
        return $this->config('listable', true);
    }

    /**
     * Check if the field is sortable
     */
    public function isSortable()
    {
        return $this->config('sortable', false);
    }

    /**
     * Check if the field is filterable
     */
    public function isFilterable()
    {
        return $this->config('filterable', false);
    }

    /**
     * Get component name for Vue.js
     */
    public function component()
    {
        $type = class_basename(static::class);

        return 'field-'.kebab_case($type);
    }

    /**
     * Export field configuration for frontend
     */
    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'type' => $this->type(),
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'required' => $this->isRequired(),
            'width' => $this->width(),
            'component' => $this->component(),
            'config' => $this->config(),
            'default' => $this->defaultValue(),
            'localizable' => $this->isLocalizable(),
            'listable' => $this->isListable(),
            'sortable' => $this->isSortable(),
            'filterable' => $this->isFilterable(),
        ];
    }

    /**
     * Get the fieldtype identifier
     */
    public function type()
    {
        return snake_case(class_basename(static::class));
    }
}
