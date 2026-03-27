<?php

namespace Cartino\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Trait HasCustomFields
 *
 * Provides functionality for models to handle custom fields stored in JSONB data column.
 * Similar to Statamic's approach but using JSON schemas instead of YAML.
 */
trait HasCustomFields
{
    /**
     * Boot the trait
     */
    protected static function bootHasCustomFields(): void
    {
        static::saving(function ($model) {
            $model->validateCustomFields();
        });
    }

    /**
     * Get the custom fields schema for this model
     */
    public function getCustomFieldsSchema(): ?array
    {
        $modelName = Str::snake(class_basename($this));
        $schemaPath = resource_path("schemas/fields/{$modelName}.json");

        if (File::exists($schemaPath)) {
            return json_decode(File::get($schemaPath), true);
        }

        return null;
    }

    /**
     * Get custom field value
     */
    public function getCustomField(string $fieldName, $default = null)
    {
        $data = $this->data ?? [];

        return $data[$fieldName] ?? $default;
    }

    /**
     * Set custom field value
     */
    public function setCustomField(string $fieldName, $value): self
    {
        $data = $this->data ?? [];
        $data[$fieldName] = $value;
        $this->data = $data;

        return $this;
    }

    /**
     * Set multiple custom fields
     */
    public function setCustomFields(array $fields): self
    {
        $data = $this->data ?? [];

        foreach ($fields as $name => $value) {
            $data[$name] = $value;
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Get all custom fields with their values
     */
    public function getCustomFields(): array
    {
        return $this->data ?? [];
    }

    /**
     * Get custom fields with schema information
     */
    public function getCustomFieldsWithSchema(): array
    {
        $schema = $this->getCustomFieldsSchema();
        $data = $this->getCustomFields();

        if (! $schema || ! isset($schema['fields'])) {
            return [];
        }

        $result = [];

        foreach ($schema['fields'] as $field) {
            $name = $field['name'];
            $result[$name] = [
                'field' => $field,
                'value' => $data[$name] ?? $field['default'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Check if a custom field exists in schema
     */
    public function hasCustomField(string $fieldName): bool
    {
        $schema = $this->getCustomFieldsSchema();

        if (! $schema || ! isset($schema['fields'])) {
            return false;
        }

        foreach ($schema['fields'] as $field) {
            if ($field['name'] === $fieldName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate custom fields against schema
     */
    public function validateCustomFields(): bool
    {
        $schema = $this->getCustomFieldsSchema();
        $data = $this->data ?? [];

        if (! $schema || ! isset($schema['fields'])) {
            return true;
        }

        foreach ($schema['fields'] as $field) {
            $name = $field['name'];
            $value = $data[$name] ?? null;

            // Check required fields
            if (($field['required'] ?? false) && ($value === null || $value === '')) {
                throw new \InvalidArgumentException("Custom field '{$name}' is required");
            }

            // Skip validation if value is null and field is not required
            if ($value === null) {
                continue;
            }

            // Type validation
            $this->validateCustomFieldType($name, $value, $field);

            // Custom validation rules
            $this->validateCustomFieldRules($name, $value, $field);
        }

        return true;
    }

    /**
     * Validate field type
     */
    protected function validateCustomFieldType(string $name, $value, array $field): void
    {
        $type = $field['type'];

        switch ($type) {
            case 'text':
            case 'textarea':
            case 'email':
            case 'url':
            case 'rich_text':
                if (! is_string($value)) {
                    throw new \InvalidArgumentException("Custom field '{$name}' must be a string");
                }
                break;

            case 'number':
                if (! is_numeric($value)) {
                    throw new \InvalidArgumentException("Custom field '{$name}' must be a number");
                }
                break;

            case 'boolean':
                if (! is_bool($value)) {
                    throw new \InvalidArgumentException("Custom field '{$name}' must be a boolean");
                }
                break;

            case 'date':
            case 'datetime':
                if (! is_string($value) || ! strtotime($value)) {
                    throw new \InvalidArgumentException("Custom field '{$name}' must be a valid date");
                }
                break;

            case 'select':
                if (! is_string($value)) {
                    throw new \InvalidArgumentException("Custom field '{$name}' must be a string");
                }
                break;

            case 'multi_select':
                if (! is_array($value)) {
                    throw new \InvalidArgumentException("Custom field '{$name}' must be an array");
                }
                break;

            case 'json':
                // Allow arrays, objects, or valid JSON strings
                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \InvalidArgumentException("Custom field '{$name}' must be valid JSON");
                    }
                } elseif (! is_array($value) && ! is_object($value)) {
                    throw new \InvalidArgumentException("Custom field '{$name}' must be JSON, array, or object");
                }
                break;
        }
    }

    /**
     * Validate field rules
     */
    protected function validateCustomFieldRules(string $name, $value, array $field): void
    {
        $validation = $field['validation'] ?? [];

        // Min/Max validation
        if (isset($validation['min']) && is_numeric($value) && $value < $validation['min']) {
            throw new \InvalidArgumentException("Custom field '{$name}' must be at least {$validation['min']}");
        }

        if (isset($validation['max']) && is_numeric($value) && $value > $validation['max']) {
            throw new \InvalidArgumentException("Custom field '{$name}' must be at most {$validation['max']}");
        }

        // Pattern validation
        if (isset($validation['pattern']) && is_string($value) && ! preg_match("/{$validation['pattern']}/", $value)) {
            throw new \InvalidArgumentException("Custom field '{$name}' does not match required pattern");
        }

        // Options validation
        if (isset($validation['options'])) {
            if ($field['type'] === 'select' && ! in_array($value, $validation['options'])) {
                throw new \InvalidArgumentException(
                    "Custom field '{$name}' must be one of: ".implode(', ', $validation['options']),
                );
            }

            if ($field['type'] === 'multi_select' && is_array($value)) {
                foreach ($value as $item) {
                    if (! in_array($item, $validation['options'])) {
                        throw new \InvalidArgumentException("Custom field '{$name}' contains invalid option: {$item}");
                    }
                }
            }
        }
    }

    /**
     * Cast the data attribute to array
     */
    protected function initializeHasCustomFields(): void
    {
        $this->casts['data'] = 'array';
    }
}
