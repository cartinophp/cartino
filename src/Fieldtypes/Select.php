<?php

namespace Cartino\Fieldtypes;

class Select extends Fieldtype
{
    public function process($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if ($this->config('multiple')) {
            return is_array($value) ? $value : [$value];
        }

        return $value;
    }

    public function preProcess($value)
    {
        if ($this->config('multiple')) {
            return is_array($value) ? $value : [];
        }

        return $value;
    }

    public function augment($value)
    {
        $options = $this->getOptions();

        if ($this->config('multiple')) {
            if (! is_array($value)) {
                return [];
            }

            return collect($value)->map(function ($key) use ($options) {
                return [
                    'key' => $key,
                    'label' => $options[$key] ?? $key,
                ];
            })->all();
        }

        return [
            'key' => $value,
            'label' => $options[$value] ?? $value,
        ];
    }

    public function getOptions()
    {
        $options = $this->config('options', []);

        // If options is a collection name, fetch from it
        if (is_string($options) && $this->isCollectionName($options)) {
            return $this->fetchFromCollection($options);
        }

        return $options;
    }

    protected function isCollectionName($name)
    {
        // Check if it's a collection reference
        return is_string($name) && ! str_contains($name, ':');
    }

    protected function fetchFromCollection($collectionHandle)
    {
        // This would fetch options from a collection
        // For now, return empty array as placeholder
        return [];
    }

    public function toJsonSchema()
    {
        $options = $this->getOptions();
        $schema = [
            'title' => $this->display(),
            'description' => $this->instructions(),
        ];

        if ($this->config('multiple')) {
            $schema['type'] = 'array';
            $schema['items'] = [
                'type' => 'string',
                'enum' => array_keys($options),
            ];
        } else {
            $schema['type'] = 'string';
            $schema['enum'] = array_keys($options);
        }

        return $schema;
    }

    public function toGqlType()
    {
        return [
            'type' => $this->config('multiple') ? '[String]' : 'String',
        ];
    }

    public function rules()
    {
        $rules = parent::rules();

        $options = array_keys($this->getOptions());

        if ($this->config('multiple')) {
            $rules[] = 'array';
            $rules[] = 'in:'.implode(',', $options);
        } else {
            $rules[] = 'in:'.implode(',', $options);
        }

        return $rules;
    }

    public function isSortable()
    {
        return ! $this->config('multiple');
    }

    public function isFilterable()
    {
        return true;
    }
}
