<?php

namespace Cartino\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class BaseDto implements Arrayable
{
    /**
     * Create DTO from request.
     */
    public static function fromRequest(Request $request): static
    {
        return static::from($request->validated());
    }

    /**
     * Create DTO from array.
     */
    public static function from(array $data): static
    {
        $instance = new static;

        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->{$key} = $instance->transformValue($key, $value);
            }
        }

        return $instance;
    }

    /**
     * Create collection of DTOs.
     */
    public static function collect(array $items): Collection
    {
        return collect($items)->map(fn ($item) => static::from($item));
    }

    /**
     * Transform value before assignment.
     */
    protected function transformValue(string $key, $value)
    {
        // Handle nested arrays
        if (is_array($value) && method_exists($this, 'get'.ucfirst($key).'Class')) {
            $class = $this->{'get'.ucfirst($key).'Class'}();

            return $class::collect($value);
        }

        // Handle money fields (convert to cents)
        if ($this->isMoneyField($key) && is_numeric($value)) {
            return (int) ($value * 100);
        }

        // Handle boolean fields
        if ($this->isBooleanField($key)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    /**
     * Check if field is money field.
     */
    protected function isMoneyField(string $key): bool
    {
        return in_array($key, $this->getMoneyFields());
    }

    /**
     * Check if field is boolean field.
     */
    protected function isBooleanField(string $key): bool
    {
        return in_array($key, $this->getBooleanFields());
    }

    /**
     * Get money fields.
     */
    protected function getMoneyFields(): array
    {
        return [];
    }

    /**
     * Get boolean fields.
     */
    protected function getBooleanFields(): array
    {
        return [];
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        $array = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof Collection) {
                $array[$key] = $value->toArray();
            } elseif ($value instanceof Arrayable) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Get only filled attributes.
     */
    public function only(array $keys): array
    {
        return Arr::only($this->toArray(), $keys);
    }

    /**
     * Get all except specified attributes.
     */
    public function except(array $keys): array
    {
        return Arr::except($this->toArray(), $keys);
    }

    /**
     * Check if DTO has attribute.
     */
    public function has(string $key): bool
    {
        return property_exists($this, $key) && isset($this->{$key});
    }

    /**
     * Get attribute value.
     */
    public function get(string $key, $default = null)
    {
        return $this->{$key} ?? $default;
    }

    /**
     * Convert DTO to JSON.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
