<?php

namespace Cartino\Cp;

use Illuminate\Support\Collection;

class NavigationSection
{
    protected string $name;

    protected string $label;

    protected array $items = [];

    protected int $order = 100;

    protected bool $collapsible = true;

    protected bool $collapsed = false;

    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    /**
     * Set order
     */
    public function order(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set collapsible
     */
    public function collapsible(bool $collapsible = true): self
    {
        $this->collapsible = $collapsible;

        return $this;
    }

    /**
     * Set collapsed
     */
    public function collapsed(bool $collapsed = true): self
    {
        $this->collapsed = $collapsed;

        return $this;
    }

    /**
     * Get items for this section
     */
    public function items(): Collection
    {
        return collect(Navigation::$items ?? [])->filter(fn ($item) => $item->section === $this->name);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'order' => $this->order,
            'collapsible' => $this->collapsible,
            'collapsed' => $this->collapsed,
            'items' => $this->items()
                ->filter(fn ($item) => $item->canView())
                ->sortBy('order')
                ->map(fn ($item) => $item->toArray())
                ->values()
                ->toArray(),
        ];
    }
}
