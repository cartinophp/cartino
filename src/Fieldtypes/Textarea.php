<?php

namespace Cartino\Fieldtypes;

class Textarea extends Fieldtype
{
    public function toJsonSchema()
    {
        return [
            'type' => 'string',
            'title' => $this->display(),
            'description' => $this->instructions(),
            'maxLength' => $this->config('max_length'),
            'minLength' => $this->config('min_length'),
        ];
    }

    public function component()
    {
        return 'field-textarea';
    }

    public function isSortable()
    {
        return false;
    }

    public function isFilterable()
    {
        return true;
    }
}
