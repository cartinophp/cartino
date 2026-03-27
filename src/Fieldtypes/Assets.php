<?php

namespace Cartino\Fieldtypes;

class Assets extends Fieldtype
{
    public function process($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if ($this->config('max_files', 1) === 1) {
            return is_array($value) ? ($value[0] ?? null) : $value;
        }

        return is_array($value) ? $value : [$value];
    }

    public function preProcess($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    public function augment($value)
    {
        if (is_null($value)) {
            return null;
        }

        if ($this->config('max_files', 1) === 1) {
            return $this->augmentSingle($value);
        }

        if (! is_array($value)) {
            $value = [$value];
        }

        return array_map([$this, 'augmentSingle'], $value);
    }

    protected function augmentSingle($id)
    {
        // This would typically fetch asset data from storage
        // For now, return basic structure
        return [
            'id' => $id,
            'url' => "/assets/{$id}",
            'title' => "Asset {$id}",
            'alt' => null,
            'width' => null,
            'height' => null,
            'size' => null,
            'mime_type' => null,
        ];
    }

    public function toJsonSchema()
    {
        $schema = [
            'title' => $this->display(),
            'description' => $this->instructions(),
        ];

        if ($this->config('max_files', 1) === 1) {
            $schema['type'] = 'string';
        } else {
            $schema['type'] = 'array';
            $schema['items'] = ['type' => 'string'];
            $schema['maxItems'] = $this->config('max_files');
        }

        return $schema;
    }

    public function toGqlType()
    {
        return [
            'type' => $this->config('max_files', 1) === 1 ? 'Asset' : '[Asset]',
        ];
    }

    public function rules()
    {
        $rules = parent::rules();

        if ($this->config('max_files', 1) > 1) {
            $rules[] = 'array';
            $rules[] = 'max:'.$this->config('max_files');
        }

        if ($allowed = $this->config('allowed_extensions')) {
            $extensions = implode(',', $allowed);
            $rules[] = "mimes:$extensions";
        }

        return $rules;
    }

    public function isSortable()
    {
        return false;
    }

    public function isFilterable()
    {
        return false;
    }
}
