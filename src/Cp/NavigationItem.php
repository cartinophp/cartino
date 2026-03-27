<?php

namespace Cartino\Cp;

use Illuminate\Support\Facades\Gate;

class NavigationItem
{
    protected string $name;

    protected ?string $label = null;

    protected ?string $url = null;

    protected ?string $icon = null;

    protected ?string $section = null;

    protected array $permissions = [];

    protected $badge = null;

    protected int $order = 100;

    protected array $children = [];

    protected bool $active = true;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Set the label
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the URL
     */
    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set the icon
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the section
     */
    public function section(string $section): self
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Set permissions
     */
    public function permissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Set badge
     */
    public function badge(callable $callback): self
    {
        $this->badge = $callback;

        return $this;
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
     * Add child item
     */
    public function child(string $name): NavigationItem
    {
        $child = new NavigationItem($name);
        $this->children[] = $child;

        return $child;
    }

    /**
     * Set active status
     */
    public function active(bool $active = true): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Check if user can view this item
     */
    public function canView(): bool
    {
        if (! $this->active) {
            return false;
        }

        if (empty($this->permissions)) {
            return true;
        }

        foreach ($this->permissions as $permission) {
            if (Gate::allows($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get badge value
     */
    public function getBadgeValue()
    {
        if (! $this->badge) {
            return null;
        }

        try {
            return call_user_func($this->badge);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label ?? $this->name,
            'url' => $this->url,
            'icon' => $this->icon,
            'section' => $this->section,
            'badge' => $this->getBadgeValue(),
            'order' => $this->order,
            'children' => collect($this->children)
                ->filter(fn ($child) => $child->canView())
                ->map(fn ($child) => $child->toArray())
                ->values()
                ->toArray(),
        ];
    }
}
