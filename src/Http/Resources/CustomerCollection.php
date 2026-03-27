<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CustomerResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'filters' => [
                'status' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                    ['value' => 'pending', 'label' => 'Pending'],
                ],
                'gender' => [
                    ['value' => 'male', 'label' => 'Male'],
                    ['value' => 'female', 'label' => 'Female'],
                    ['value' => 'other', 'label' => 'Other'],
                ],
            ],
            'sorts' => [
                ['value' => 'created_at', 'label' => 'Date Created'],
                ['value' => 'updated_at', 'label' => 'Last Updated'],
                ['value' => 'first_name', 'label' => 'First Name'],
                ['value' => 'last_name', 'label' => 'Last Name'],
                ['value' => 'email', 'label' => 'Email'],
                ['value' => 'orders_count', 'label' => 'Order Count'],
                ['value' => 'orders_sum_total_amount', 'label' => 'Total Spent'],
            ],
            'bulk_actions' => [
                ['value' => 'delete', 'label' => 'Delete Selected'],
                ['value' => 'export', 'label' => 'Export Selected'],
                ['value' => 'activate', 'label' => 'Activate Selected'],
                ['value' => 'deactivate', 'label' => 'Deactivate Selected'],
            ],
        ];
    }
}
