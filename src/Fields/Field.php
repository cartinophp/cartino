<?php

namespace Cartino\Fields;

use Illuminate\Support\Str;

class Field
{
    protected $handle;

    protected $config;

    protected $value;

    public function __construct($handle, array $config = [])
    {
        $this->handle = $handle;
        $this->config = collect($config);
    }

    public static function make($handle, array $config = [])
    {
        return new static($handle, $config);
    }

    public function handle()
    {
        return $this->handle;
    }

    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config;
        }

        return $this->config->get($key, $default);
    }

    public function setConfig($config)
    {
        $this->config = collect($config);

        return $this;
    }

    public function mergeConfig($config)
    {
        $this->config = $this->config->merge($config);

        return $this;
    }

    public function type()
    {
        return $this->config('type', 'text');
    }

    public function display()
    {
        return $this->config('display', Str::title(str_replace('_', ' ', $this->handle)));
    }

    public function instructions()
    {
        return $this->config('instructions');
    }

    public function required()
    {
        return $this->config('required', false);
    }

    public function rules()
    {
        return collect($this->config('validate', []))
            ->map(function ($rule) {
                return is_string($rule) ? $rule : $rule;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function fieldtype()
    {
        $type = $this->type();

        $class = 'Cartino\\Fieldtypes\\'.Str::studly($type);

        if (class_exists($class)) {
            return app($class)->setField($this);
        }

        // Fallback to text fieldtype
        return app('Cartino\\Fieldtypes\\Text')->setField($this);
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function process($value)
    {
        return $this->fieldtype()->process($value);
    }

    public function preProcess($value)
    {
        return $this->fieldtype()->preProcess($value);
    }

    public function augment($value)
    {
        return $this->fieldtype()->augment($value);
    }

    public function toGqlType()
    {
        return $this->fieldtype()->toGqlType();
    }

    public function toJsonSchema()
    {
        $schema = [
            'type' => $this->getJsonSchemaType(),
            'title' => $this->display(),
        ];

        if ($description = $this->instructions()) {
            $schema['description'] = $description;
        }

        if ($this->required()) {
            $schema['required'] = true;
        }

        // Add field-type specific schema properties
        $schema = array_merge($schema, $this->fieldtype()->toJsonSchema());

        return $schema;
    }

    protected function getJsonSchemaType()
    {
        $type = $this->type();

        $typeMap = [
            'text' => 'string',
            'textarea' => 'string',
            'markdown' => 'string',
            'number' => 'number',
            'integer' => 'integer',
            'float' => 'number',
            'toggle' => 'boolean',
            'checkbox' => 'boolean',
            'date' => 'string',
            'datetime' => 'string',
            'time' => 'string',
            'select' => 'string',
            'radio' => 'string',
            'checkboxes' => 'array',
            'array' => 'array',
            'json' => 'object',
            'yaml' => 'object',
            'assets' => 'array',
            'collection' => 'array',
            'entries' => 'array',
            'terms' => 'array',
            'users' => 'array',
        ];

        return $typeMap[$type] ?? 'string';
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle,
            'type' => $this->type(),
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'required' => $this->required(),
            'config' => $this->config->all(),
            'json_schema' => $this->toJsonSchema(),
        ];
    }

    public function __toString()
    {
        return $this->handle;
    }
}
