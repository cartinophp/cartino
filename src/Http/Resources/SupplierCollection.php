<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplierCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public string $collects = SupplierResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'path' => $this->path(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
            'filters' => $this->getAvailableFilters(),
            'sorting' => $this->getAvailableSorting(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
                'resource' => 'suppliers',
                'summary' => $this->getCollectionSummary(),
            ],
        ];
    }

    /**
     * Get available filters for suppliers
     */
    private function getAvailableFilters(): array
    {
        return [
            'status' => [
                'label' => 'Stato',
                'options' => [
                    ['value' => 'active', 'label' => 'Attivo'],
                    ['value' => 'inactive', 'label' => 'Inattivo'],
                    ['value' => 'suspended', 'label' => 'Sospeso'],
                ],
            ],
            'priority' => [
                'label' => 'Priorità',
                'options' => [
                    ['value' => 'low', 'label' => 'Bassa'],
                    ['value' => 'normal', 'label' => 'Normale'],
                    ['value' => 'high', 'label' => 'Alta'],
                    ['value' => 'critical', 'label' => 'Critica'],
                ],
            ],
            'country_code' => [
                'label' => 'Paese',
                'type' => 'select',
                'endpoint' => '/api/countries',
            ],
            'min_rating' => [
                'label' => 'Valutazione minima',
                'type' => 'number',
                'min' => 0,
                'max' => 5,
                'step' => 0.1,
            ],
            'is_preferred' => [
                'label' => 'Fornitore preferito',
                'type' => 'boolean',
            ],
            'is_verified' => [
                'label' => 'Verificato',
                'type' => 'boolean',
            ],
            'created_from' => [
                'label' => 'Creato da',
                'type' => 'date',
            ],
            'created_to' => [
                'label' => 'Creato fino a',
                'type' => 'date',
            ],
        ];
    }

    /**
     * Get available sorting options
     */
    private function getAvailableSorting(): array
    {
        return [
            'options' => [
                ['value' => 'name', 'label' => 'Nome'],
                ['value' => 'code', 'label' => 'Codice'],
                ['value' => 'status', 'label' => 'Stato'],
                ['value' => 'priority', 'label' => 'Priorità'],
                ['value' => 'rating', 'label' => 'Valutazione'],
                ['value' => 'created_at', 'label' => 'Data creazione'],
                ['value' => 'updated_at', 'label' => 'Ultimo aggiornamento'],
            ],
            'directions' => [
                ['value' => 'asc', 'label' => 'Crescente'],
                ['value' => 'desc', 'label' => 'Decrescente'],
            ],
            'default' => [
                'field' => 'name',
                'direction' => 'asc',
            ],
        ];
    }

    /**
     * Get collection summary statistics
     */
    private function getCollectionSummary(): array
    {
        if ($this->isEmpty()) {
            return [
                'total_suppliers' => 0,
                'active_suppliers' => 0,
                'preferred_suppliers' => 0,
                'verified_suppliers' => 0,
                'average_rating' => 0,
            ];
        }

        $collection = $this->collection;

        return [
            'total_suppliers' => $collection->count(),
            'active_suppliers' => $collection->filter(fn ($supplier) => $supplier->status === 'active')->count(),
            'preferred_suppliers' => $collection->filter(fn ($supplier) => $supplier->is_preferred)->count(),
            'verified_suppliers' => $collection->filter(fn ($supplier) => $supplier->is_verified)->count(),
            'average_rating' => round($collection->avg('rating'), 2),
        ];
    }
}
