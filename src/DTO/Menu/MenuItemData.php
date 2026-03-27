<?php

namespace Cartino\DTO\Menu;

use Cartino\Models\MenuItem;

class MenuItemData
{
    public function __construct(
        public ?int $id,
        public int $menu_id,
        public ?int $parent_id,
        public string $title,
        public ?string $url,
        public string $type,
        public ?string $reference_type,
        public ?int $reference_id,
        public array $data,
        public string $status,
        public bool $opens_in_new_window,
        public ?string $css_class,
        public int $sort_order,
        public int $depth,
        public array $children = [],
    ) {}

    public static function fromModel(MenuItem $item): self
    {
        return new self(
            id: $item->id,
            menu_id: $item->menu_id,
            parent_id: $item->parent_id,
            title: $item->title,
            url: $item->getComputedUrl(),
            type: $item->type,
            reference_type: $item->reference_type,
            reference_id: $item->reference_id,
            data: $item->data ?? [],
            status: $item->status ?? 'active',
            opens_in_new_window: $item->opens_in_new_window,
            css_class: $item->css_class,
            sort_order: $item->sort_order,
            depth: $item->depth,
            children: $item->children->map(fn ($child) => self::fromModel($child))->toArray(),
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            menu_id: (int) ($data['menu_id'] ?? 0),
            parent_id: $data['parent_id'] ?? null,
            title: $data['title'] ?? '',
            url: $data['url'] ?? null,
            type: $data['type'] ?? 'link',
            reference_type: $data['reference_type'] ?? null,
            reference_id: $data['reference_id'] ?? null,
            data: $data['data'] ?? [],
            status: $data['status'] ?? 'active',
            opens_in_new_window: (bool) ($data['opens_in_new_window'] ?? false),
            css_class: $data['css_class'] ?? null,
            sort_order: (int) ($data['sort_order'] ?? 0),
            depth: (int) ($data['depth'] ?? 0),
            children: $data['children'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'url' => $this->url,
            'type' => $this->type,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'data' => $this->data,
            'status' => $this->status,
            'opens_in_new_window' => $this->opens_in_new_window,
            'css_class' => $this->css_class,
            'sort_order' => $this->sort_order,
            'depth' => $this->depth,
            'children' => $this->children,
        ];
    }
}
