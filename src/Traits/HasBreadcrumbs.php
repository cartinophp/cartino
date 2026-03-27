<?php

declare(strict_types=1);

namespace Cartino\Traits;

trait HasBreadcrumbs
{
    /**
     * Breadcrumbs for the current page.
     */
    protected array $breadcrumbs = [];

    /**
     * Add breadcrumb item.
     */
    protected function addBreadcrumb(string $title, ?string $route = null, array $parameters = []): self
    {
        $this->breadcrumbs[] = [
            'title' => $title,
            'route' => $route,
            'parameters' => $parameters,
            'url' => $route ? route($route, $parameters) : null,
        ];

        return $this;
    }

    /**
     * Set breadcrumbs array.
     */
    protected function setBreadcrumbs(array $breadcrumbs): self
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    /**
     * Get breadcrumbs.
     */
    protected function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    /**
     * Clear breadcrumbs.
     */
    protected function clearBreadcrumbs(): self
    {
        $this->breadcrumbs = [];

        return $this;
    }

    /**
     * Add default dashboard breadcrumb.
     */
    protected function addDashboardBreadcrumb(): self
    {
        return $this->addBreadcrumb('Dashboard', 'cp.dashboard');
    }
}
