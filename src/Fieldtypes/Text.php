<?php

namespace Cartino\Fieldtypes;

class Text extends Fieldtype
{
    public function toJsonSchema()
    {
        return [
            'type' => 'string',
            'title' => $this->display(),
            'description' => $this->instructions(),
            'maxLength' => $this->config('max_length'),
            'minLength' => $this->config('min_length'),
            'pattern' => $this->config('pattern'),
        ];
    }

    public function toGqlType()
    {
        return [
            'type' => 'String',
        ];
    }

    public function rules()
    {
        $rules = parent::rules();

        if ($pattern = $this->config('pattern')) {
            $rules[] = "regex:$pattern";
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
