<?php

namespace Cartino\Cp;

abstract class PageBlock
{
    protected int $order = 100;

    protected array $props = [];

    protected string $component;

    public function __construct(?string $component = null)
    {
        $this->component = $component ?? $this->getDefaultComponent();
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
     * Set props
     */
    public function props(array $props): self
    {
        $this->props = array_merge($this->props, $props);

        return $this;
    }

    /**
     * Get default component name
     */
    abstract protected function getDefaultComponent(): string;

    /**
     * Compile block to array
     */
    public function compile(): array
    {
        return [
            'component' => $this->component,
            'props' => $this->props,
            'order' => $this->order,
        ];
    }
}

class PageCard extends PageBlock
{
    protected ?string $title = null;

    protected array $actions = [];

    protected array $content = [];

    protected bool $sectioned = true;

    public function __construct(?string $title = null)
    {
        parent::__construct();
        $this->title = $title;
    }

    protected function getDefaultComponent(): string
    {
        return 'Card';
    }

    /**
     * Set title
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Add action
     */
    public function action(string $label, string $url, array $options = []): self
    {
        $this->actions[] = [
            'label' => $label,
            'url' => $url,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Add content
     */
    public function content(string $component, array $props = []): self
    {
        $this->content[] = [
            'component' => $component,
            'props' => $props,
        ];

        return $this;
    }

    /**
     * Set sectioned
     */
    public function sectioned(bool $sectioned = true): self
    {
        $this->sectioned = $sectioned;

        return $this;
    }

    public function compile(): array
    {
        return [
            'component' => $this->component,
            'props' => [
                'title' => $this->title,
                'actions' => $this->actions,
                'content' => $this->content,
                'sectioned' => $this->sectioned,
                ...$this->props,
            ],
            'order' => $this->order,
        ];
    }
}

class PageLayout extends PageBlock
{
    protected array $sections = [];

    protected function getDefaultComponent(): string
    {
        return 'Layout';
    }

    /**
     * Add one column section
     */
    public function oneColumn(): PageLayoutSection
    {
        $section = new PageLayoutSection('oneColumn');
        $this->sections[] = $section;

        return $section;
    }

    /**
     * Add two column section
     */
    public function twoColumns(int $primaryWidth = 2, int $secondaryWidth = 1): PageLayoutSection
    {
        $section = new PageLayoutSection('twoColumns', [
            'primaryWidth' => $primaryWidth,
            'secondaryWidth' => $secondaryWidth,
        ]);
        $this->sections[] = $section;

        return $section;
    }

    public function compile(): array
    {
        return [
            'component' => $this->component,
            'props' => [
                'sections' => collect($this->sections)->map(fn ($section) => $section->compile())->toArray(),
                ...$this->props,
            ],
            'order' => $this->order,
        ];
    }
}

class PageLayoutSection
{
    protected string $type;

    protected array $props;

    protected array $primary = [];

    protected array $secondary = [];

    public function __construct(string $type, array $props = [])
    {
        $this->type = $type;
        $this->props = $props;
    }

    /**
     * Add primary content
     */
    public function primary(string $component, array $props = []): self
    {
        $this->primary[] = [
            'component' => $component,
            'props' => $props,
        ];

        return $this;
    }

    /**
     * Add secondary content
     */
    public function secondary(string $component, array $props = []): self
    {
        $this->secondary[] = [
            'component' => $component,
            'props' => $props,
        ];

        return $this;
    }

    public function compile(): array
    {
        return [
            'type' => $this->type,
            'props' => $this->props,
            'primary' => $this->primary,
            'secondary' => $this->secondary,
        ];
    }
}
