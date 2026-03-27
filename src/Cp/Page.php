<?php

namespace Cartino\Cp;

class Page
{
    protected string $title;

    protected string $handle;

    protected array $actions = [];

    protected array $headerActions = [];

    protected array $blocks = [];

    protected array $tabs = [];

    protected array $breadcrumbs = [];

    protected ?string $subtitle = null;

    protected ?string $backUrl = null;

    protected array $meta = [];

    public function __construct(string $title, ?string $handle = null)
    {
        $this->title = $title;
        $this->handle = $handle ?? str($title)->slug()->toString();
    }

    /**
     * Create new page
     */
    public static function make(string $title, ?string $handle = null): self
    {
        return new static($title, $handle);
    }

    /**
     * Set subtitle
     */
    public function subtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Set back URL
     */
    public function backUrl(string $url): self
    {
        $this->backUrl = $url;

        return $this;
    }

    /**
     * Add primary action (Shopify style)
     */
    public function primaryAction(string $label, ?string $url = null, array $options = []): self
    {
        $this->actions['primary'] = [
            'label' => $label,
            'url' => $url,
            'type' => 'primary',
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Add secondary actions
     */
    public function secondaryActions(array $actions): self
    {
        $this->actions['secondary'] = $actions;

        return $this;
    }

    /**
     * Add header action
     */
    public function headerAction(string $component, array $props = []): self
    {
        $this->headerActions[] = [
            'component' => $component,
            'props' => $props,
        ];

        return $this;
    }

    /**
     * Add breadcrumb
     */
    public function breadcrumb(string $label, ?string $url = null): self
    {
        $this->breadcrumbs[] = [
            'label' => $label,
            'url' => $url,
        ];

        return $this;
    }

    /**
     * Add content block
     */
    public function block(string $component, array $props = [], int $order = 100): self
    {
        $this->blocks[] = [
            'component' => $component,
            'props' => $props,
            'order' => $order,
        ];

        return $this;
    }

    /**
     * Add card block
     */
    public function card(?string $title = null): PageCard
    {
        $card = new PageCard($title);
        $this->blocks[] = $card;

        return $card;
    }

    /**
     * Add layout block
     */
    public function layout(): PageLayout
    {
        $layout = new PageLayout;
        $this->blocks[] = $layout;

        return $layout;
    }

    /**
     * Add tab
     */
    public function tab(string $name, string $label, string $component, array $props = []): self
    {
        $this->tabs[] = [
            'name' => $name,
            'label' => $label,
            'component' => $component,
            'props' => $props,
        ];

        return $this;
    }

    /**
     * Set multiple tabs at once
     */
    public function tabs(array $tabs): self
    {
        foreach ($tabs as $name => $config) {
            $this->tab(
                $name,
                $config['label'] ?? ucfirst($name),
                $config['component'] ?? 'DefaultComponent',
                $config['props'] ?? [],
            );
        }

        return $this;
    }

    /**
     * Set page meta
     */
    public function meta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    /**
     * Get compiled page data
     */
    public function compile(): array
    {
        return [
            'title' => $this->title,
            'handle' => $this->handle,
            'subtitle' => $this->subtitle,
            'backUrl' => $this->backUrl,
            'breadcrumbs' => $this->breadcrumbs,
            'actions' => $this->actions,
            'headerActions' => $this->headerActions,
            'blocks' => $this->compileBlocks(),
            'tabs' => $this->tabs,
            'meta' => $this->meta,
        ];
    }

    /**
     * Compile blocks
     */
    protected function compileBlocks(): array
    {
        return collect($this->blocks)
            ->map(function ($block) {
                if ($block instanceof PageBlock) {
                    return $block->compile();
                }

                return $block;
            })
            ->sortBy('order')
            ->values()
            ->toArray();
    }
}
