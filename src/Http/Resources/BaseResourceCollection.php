<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($item) use ($request) {
                return $this->transformItem($item, $request);
            }),
            'meta' => $this->getMeta($request),
            'links' => $this->when($this->resource instanceof LengthAwarePaginator, [
                'first' => $this->resource->url(1),
                'last' => $this->resource->url($this->resource->lastPage()),
                'prev' => $this->resource->previousPageUrl(),
                'next' => $this->resource->nextPageUrl(),
            ]),
            'pagination' => $this->when($this->resource instanceof LengthAwarePaginator, [
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'from' => $this->resource->firstItem(),
                'to' => $this->resource->lastItem(),
            ]),
        ];
    }

    /**
     * Transform individual item.
     */
    abstract protected function transformItem($item, Request $request): array;

    /**
     * Get meta information for the collection.
     */
    protected function getMeta(Request $request): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'version' => '1.0',
            'filters' => $this->getActiveFilters($request),
            'sorting' => $this->getActiveSorting($request),
        ];
    }

    /**
     * Get active filters from request.
     */
    protected function getActiveFilters(Request $request): array
    {
        $filters = [];

        foreach ($request->query() as $key => $value) {
            if (str_starts_with($key, 'filter_') && ! empty($value)) {
                $filters[str_replace('filter_', '', $key)] = $value;
            }
        }

        return $filters;
    }

    /**
     * Get active sorting from request.
     */
    protected function getActiveSorting(Request $request): array
    {
        return [
            'field' => $request->query('sort_by', 'id'),
            'direction' => $request->query('sort_direction', 'asc'),
        ];
    }

    /**
     * Add additional collection meta.
     */
    public function additional(array $data): static
    {
        return parent::additional($data);
    }
}
