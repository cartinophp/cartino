<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : ((array) $this->resource);

        return [
            'period' => $data['period'] ?? null,
            'sales' => [
                'total_revenue' => round($data['sales']['total_revenue'] ?? 0, 2),
                'total_orders' => $data['sales']['total_orders'] ?? 0,
                'average_order_value' => round($data['sales']['average_order_value'] ?? 0, 2),
                'total_items_sold' => $data['sales']['total_items_sold'] ?? 0,
            ],
            'customers' => $data['customers'] ?? [],
            'products' => $data['products'] ?? [],
        ];
    }
}
