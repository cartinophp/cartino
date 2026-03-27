<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class Item
{
    public function __construct(
        private mixed $value,
        private string $label,
        private array $extra = [],
    ) {}

    public function value(): mixed
    {
        return $this->value;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function extra(): array
    {
        return $this->extra;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->extra[$key] ?? $default;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'extra' => $this->extra,
        ];
    }
}
