<?php

namespace Cartino\Schema;

use Carbon\Carbon;

class TextFieldType extends FieldType
{
    public function getType(): string
    {
        return 'text';
    }

    protected function getFieldSpecificConfig(): array
    {
        return [
            'maxlength' => $this->config['maxlength'] ?? null,
            'character_limit' => $this->config['character_limit'] ?? null,
        ];
    }

    public function validate($value): array
    {
        $errors = parent::validate($value);

        if (! empty($value)) {
            $maxlength = $this->config['maxlength'] ?? null;
            if ($maxlength && strlen($value) > $maxlength) {
                $errors[] = $this->getDisplayName()." must be {$maxlength} characters or less";
            }
        }

        return $errors;
    }
}

class TextareaFieldType extends FieldType
{
    public function getType(): string
    {
        return 'textarea';
    }

    protected function getFieldSpecificConfig(): array
    {
        return [
            'rows' => $this->config['rows'] ?? 5,
            'character_limit' => $this->config['character_limit'] ?? null,
        ];
    }
}

class NumberFieldType extends FieldType
{
    public function getType(): string
    {
        return 'number';
    }

    protected function getFieldSpecificConfig(): array
    {
        return [
            'min' => $this->config['min'] ?? null,
            'max' => $this->config['max'] ?? null,
            'step' => $this->config['step'] ?? 1,
        ];
    }

    public function preProcess($value)
    {
        if (is_numeric($value)) {
            return is_int($value) ? ((int) $value) : ((float) $value);
        }

        return $value;
    }

    public function validate($value): array
    {
        $errors = parent::validate($value);

        if (! empty($value) && ! is_numeric($value)) {
            $errors[] = $this->getDisplayName().' must be a number';
        }

        if (is_numeric($value)) {
            $min = $this->config['min'] ?? null;
            $max = $this->config['max'] ?? null;

            if ($min !== null && $value < $min) {
                $errors[] = $this->getDisplayName()." must be at least {$min}";
            }

            if ($max !== null && $value > $max) {
                $errors[] = $this->getDisplayName()." must be no more than {$max}";
            }
        }

        return $errors;
    }
}

class SelectFieldType extends FieldType
{
    public function getType(): string
    {
        return 'select';
    }

    protected function getFieldSpecificConfig(): array
    {
        return [
            'options' => $this->getOptions(),
            'multiple' => $this->config['multiple'] ?? false,
            'clearable' => $this->config['clearable'] ?? true,
        ];
    }

    protected function getOptions(): array
    {
        $options = $this->config['options'] ?? [];

        // Support for different option formats
        if (is_array($options) && ! empty($options)) {
            // Already in correct format
            if (isset($options[0]) && is_array($options[0]) && isset($options[0]['value'])) {
                return $options;
            }

            // Convert associative array to options format
            return collect($options)
                ->map(function ($label, $value) {
                    return ['value' => $value, 'label' => $label];
                })
                ->values()
                ->toArray();
        }

        return [];
    }

    public function validate($value): array
    {
        $errors = parent::validate($value);

        if (! empty($value)) {
            $validValues = collect($this->getOptions())->pluck('value')->toArray();

            if ($this->config['multiple'] ?? false) {
                if (! is_array($value)) {
                    $errors[] = $this->getDisplayName().' must be an array';
                } else {
                    foreach ($value as $v) {
                        if (! in_array($v, $validValues)) {
                            $errors[] = "Invalid option selected for {$this->getDisplayName()}";
                            break;
                        }
                    }
                }
            } else {
                if (! in_array($value, $validValues)) {
                    $errors[] = "Invalid option selected for {$this->getDisplayName()}";
                }
            }
        }

        return $errors;
    }
}

class ToggleFieldType extends FieldType
{
    public function getType(): string
    {
        return 'toggle';
    }

    public function preProcess($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getDefault()
    {
        return $this->config['default'] ?? false;
    }
}

class DateFieldType extends FieldType
{
    public function getType(): string
    {
        return 'date';
    }

    protected function getFieldSpecificConfig(): array
    {
        return [
            'format' => $this->config['format'] ?? 'Y-m-d',
            'earliest_date' => $this->config['earliest_date'] ?? null,
            'latest_date' => $this->config['latest_date'] ?? null,
        ];
    }

    public function preProcess($value)
    {
        if ($value && ! ($value instanceof Carbon)) {
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    public function augment($value)
    {
        if ($value instanceof Carbon) {
            return $value->format($this->config['format'] ?? 'Y-m-d');
        }

        return $value;
    }
}

class EmailFieldType extends TextFieldType
{
    public function getType(): string
    {
        return 'email';
    }

    public function validate($value): array
    {
        $errors = parent::validate($value);

        if (! empty($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $this->getDisplayName().' must be a valid email address';
        }

        return $errors;
    }
}
