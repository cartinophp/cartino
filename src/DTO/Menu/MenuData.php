<?php

namespace Cartino\DTO\Menu;

use Cartino\Models\Menu;

class MenuData
{
    public function __construct(
        public ?int $id,
        public string $handle,
        public string $title,
        public ?string $description,
        public array $settings,
        public bool $is_active,
        public int $sort_order,
        public array $items = [],
    ) {}

    public static function fromModel(Menu $menu): self
    {
        return new self(
            id: $menu->id,
            handle: $menu->handle,
            title: $menu->title,
            description: $menu->description,
            settings: $menu->settings ?? [],
            is_active: $menu->is_active,
            sort_order: $menu->sort_order,
            items: $menu->getTree(),
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            handle: $data['handle'] ?? '',
            title: $data['title'] ?? '',
            description: $data['description'] ?? null,
            settings: $data['settings'] ?? [],
            is_active: (bool) ($data['is_active'] ?? true),
            sort_order: (int) ($data['sort_order'] ?? 0),
            items: $data['items'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'handle' => $this->handle,
            'title' => $this->title,
            'description' => $this->description,
            'settings' => $this->settings,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'items' => $this->items,
        ];
    }
}
