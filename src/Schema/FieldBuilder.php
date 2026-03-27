<?php

namespace Cartino\Schema;

use Illuminate\Support\Collection;

class FieldBuilder
{
    protected array $fieldTypes = [];

    public function __construct()
    {
        $this->registerDefaultFieldTypes();
    }

    /**
     * Register field type.
     */
    public function register(string $type, string $class): void
    {
        $this->fieldTypes[$type] = $class;
    }

    /**
     * Get field type class.
     */
    public function getFieldType(string $type): ?string
    {
        return $this->fieldTypes[$type] ?? null;
    }

    /**
     * Get all registered field types.
     */
    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }

    /**
     * Build field from schema definition.
     */
    public function buildField(string $handle, array $config): ?FieldType
    {
        $type = $config['type'] ?? 'text';
        $fieldClass = $this->getFieldType($type);

        if (! $fieldClass || ! class_exists($fieldClass)) {
            return null;
        }

        return new $fieldClass($handle, $config);
    }

    /**
     * Build fields collection from schema.
     */
    public function buildFields(array $fieldsConfig): Collection
    {
        return collect($fieldsConfig)->map(function ($config, $handle) {
            return $this->buildField($handle, $config);
        })->filter();
    }

    /**
     * Register default field types.
     */
    protected function registerDefaultFieldTypes(): void
    {
        $this->register('text', TextFieldType::class);
        $this->register('textarea', TextareaFieldType::class);
        $this->register('number', NumberFieldType::class);
        $this->register('email', EmailFieldType::class);
        $this->register('select', SelectFieldType::class);
        $this->register('toggle', ToggleFieldType::class);
        $this->register('date', DateFieldType::class);

        // TODO: Implement remaining field types
        // $this->register('markdown', MarkdownFieldType::class);
        // $this->register('url', UrlFieldType::class);
        // ... other field types
    }
}
