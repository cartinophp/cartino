<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomersReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : ((array) $this->resource);

        $topCustomers = collect($data['top_customers'] ?? [])->map(function ($customer) {
            $customerArray = is_array($customer) ? $customer : ((array) $customer);

            return [
                'id' => $customerArray['id'] ?? null,
                'name' => $customerArray['name'] ?? null,
                'email' => $customerArray['email'] ?? null,
                'orders_count' => $customerArray['orders_count'] ?? 0,
                'total_spent' => round($customerArray['total_spent'] ?? 0, 2),
            ];
        });

        return [
            'period' => $data['period'] ?? null,
            'summary' => [
                'new_customers' => $data['summary']['new_customers'] ?? 0,
                'total_customers' => $data['summary']['total_customers'] ?? 0,
                'average_ltv' => round($data['summary']['average_ltv'] ?? 0, 2),
            ],
            'new_customers_timeline' => $data['new_customers_timeline'] ?? [],
            'top_customers' => $topCustomers,
        ];
    }
}
