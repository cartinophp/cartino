<?php

declare(strict_types=1);

namespace Cartino\Cp;

/**
 * Listing configuration — Statamic-style.
 *
 * Defines columns, filters, bulk actions and sort/pagination options
 * so that the Vue side can be a thin, data-driven DataTable wrapper.
 */
class Listing
{
    protected array $columns = [];

    protected array $filters = [];

    protected array $bulkActions = [];

    protected ?string $sortColumn = null;

    protected string $sortDirection = 'asc';

    protected int $perPage = 15;

    protected array $perPageOptions = [15, 25, 50, 100];

    protected ?string $searchPlaceholder = null;

    protected bool $searchable = true;

    protected bool $selectable = true;

    protected ?string $emptyTitle = null;

    protected ?string $emptyDescription = null;

    protected ?array $emptyAction = null;

    protected ?string $emptyIcon = null;

    public static function make(): static
    {
        return new static;
    }

    // ------------------------------------------------------------------
    //  Columns
    // ------------------------------------------------------------------

    /**
     * Add a column definition.
     */
    public function column(
        string $key,
        ?string $label = null,
        bool $sortable = false,
        ?string $width = null,
        ?string $format = null,
        ?string $component = null,
    ): static {
        $col = [
            'key' => $key,
            'label' => $label ?? str($key)->headline()->toString(),
            'sortable' => $sortable,
        ];

        if ($width !== null) {
            $col['width'] = $width;
        }
        if ($format !== null) {
            $col['format'] = $format;
        }
        if ($component !== null) {
            $col['component'] = $component;
        }

        $this->columns[] = $col;

        return $this;
    }

    /**
     * Bulk-set columns from array.
     */
    public function columns(array $columns): static
    {
        foreach ($columns as $col) {
            $this->columns[] = $col;
        }

        return $this;
    }

    // ------------------------------------------------------------------
    //  Filters
    // ------------------------------------------------------------------

    /**
     * Add a filter definition.
     */
    public function filter(
        string $key,
        ?string $label = null,
        string $type = 'select',
        array $options = [],
        ?string $placeholder = null,
    ): static {
        $this->filters[] = [
            'key' => $key,
            'label' => $label ?? str($key)->headline()->toString(),
            'type' => $type,
            'options' => $options,
            'placeholder' => $placeholder,
        ];

        return $this;
    }

    // ------------------------------------------------------------------
    //  Bulk Actions
    // ------------------------------------------------------------------

    /**
     * Add a bulk action.
     */
    public function bulkAction(
        string $action,
        string $label,
        bool $destructive = false,
        ?string $confirm = null,
    ): static {
        $this->bulkActions[] = [
            'action' => $action,
            'label' => $label,
            'destructive' => $destructive,
            'confirm' => $confirm,
        ];

        return $this;
    }

    // ------------------------------------------------------------------
    //  Sorting / Pagination
    // ------------------------------------------------------------------

    public function sort(string $column, string $direction = 'asc'): static
    {
        $this->sortColumn = $column;
        $this->sortDirection = $direction;

        return $this;
    }

    public function perPage(int $perPage, array $options = []): static
    {
        $this->perPage = $perPage;
        if ($options) {
            $this->perPageOptions = $options;
        }

        return $this;
    }

    // ------------------------------------------------------------------
    //  Search
    // ------------------------------------------------------------------

    public function searchable(bool $searchable = true, ?string $placeholder = null): static
    {
        $this->searchable = $searchable;
        $this->searchPlaceholder = $placeholder;

        return $this;
    }

    public function selectable(bool $selectable = true): static
    {
        $this->selectable = $selectable;

        return $this;
    }

    // ------------------------------------------------------------------
    //  Empty State
    // ------------------------------------------------------------------

    public function emptyState(string $title, ?string $description = null, ?array $action = null, ?string $icon = null): static
    {
        $this->emptyTitle = $title;
        $this->emptyDescription = $description;
        $this->emptyAction = $action;
        $this->emptyIcon = $icon;

        return $this;
    }

    // ------------------------------------------------------------------
    //  Compile
    // ------------------------------------------------------------------

    public function toArray(): array
    {
        $data = [
            'columns' => $this->columns,
            'filters' => $this->filters,
            'bulkActions' => $this->bulkActions,
            'sort' => $this->sortColumn ? ['column' => $this->sortColumn, 'direction' => $this->sortDirection] : null,
            'perPage' => $this->perPage,
            'perPageOptions' => $this->perPageOptions,
            'searchable' => $this->searchable,
            'selectable' => $this->selectable,
        ];

        if ($this->searchPlaceholder) {
            $data['searchPlaceholder'] = $this->searchPlaceholder;
        }

        if ($this->emptyTitle) {
            $data['empty'] = [
                'title' => $this->emptyTitle,
                'description' => $this->emptyDescription,
                'action' => $this->emptyAction,
                'icon' => $this->emptyIcon,
            ];
        }

        return $data;
    }
}
