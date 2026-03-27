<?php

declare(strict_types=1);

namespace Cartino\DTO;

class BulkOperationDTO
{
    public function __construct(
        public readonly array $ids,
        public readonly array $data = [],
        public readonly bool $force = false,
    ) {}

    public static function forUpdate(array $ids, array $data): self
    {
        return new self($ids, $data);
    }

    public static function forDelete(array $ids, bool $force = false): self
    {
        return new self($ids, [], $force);
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isForceOperation(): bool
    {
        return $this->force;
    }

    public function getIdsCount(): int
    {
        return count($this->ids);
    }
}
