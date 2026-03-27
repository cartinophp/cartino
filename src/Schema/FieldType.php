<?php

namespace Cartino\Schema;

abstract class FieldType
{
    protected string $handle;

    protected array $config;

    public function __construct(string $handle, array $config = [])
    {
        $this->handle = $handle;
        $this->config = $config;
    }

    /**
     * Get field handle.
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * Get field configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get field type name.
     */
    abstract public function getType(): string;

    /**
     * Get field display name.
     */
    public function getDisplayName(): string
    {
        return $this->config['display'] ?? $this->config['label'] ?? ucfirst(str_replace('_', ' ', $this->handle));
    }

    /**
     * Get field instructions.
     */
    public function getInstructions(): ?string
    {
        return $this->config['instructions'] ?? null;
    }

    /**
     * Check if field is required.
     */
    public function isRequired(): bool
    {
        return $this->config['required'] ?? false;
    }

    /**
     * Get field default value.
     */
    public function getDefault()
    {
        return $this->config['default'] ?? null;
    }

    /**
     * Get field placeholder.
     */
    public function getPlaceholder(): ?string
    {
        return $this->config['placeholder'] ?? null;
    }

    /**
     * Process value before saving.
     */
    public function preProcess($value)
    {
        return $value;
    }

    /**
     * Augment value for output.
     */
    public function augment($value)
    {
        return $value;
    }

    /**
     * Validate field value.
     */
    public function validate($value): array
    {
        $errors = [];

        if ($this->isRequired() && empty($value)) {
            $errors[] = $this->getDisplayName().' is required';
        }

        return $errors;
    }

    /**
     * Get field configuration for frontend.
     */
    public function toArray(): array
    {
        return [
            'handle' => $this->handle,
            'type' => $this->getType(),
            'display' => $this->getDisplayName(),
            'instructions' => $this->getInstructions(),
            'required' => $this->isRequired(),
            'default' => $this->getDefault(),
            'placeholder' => $this->getPlaceholder(),
            'config' => $this->getFieldSpecificConfig(),
        ];
    }

    /**
     * Get field-specific configuration.
     */
    protected function getFieldSpecificConfig(): array
    {
        return [];
    }
}
