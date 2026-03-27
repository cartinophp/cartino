<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : ((array) $this->resource);

        $mappedData = collect($data['data'] ?? [])->map(function ($item) {
            $itemArray = is_array($item) ? $item : ((array) $item);

            return [
                'period' => $itemArray['period'] ?? null,
                'orders_count' => $itemArray['orders_count'] ?? 0,
                'revenue' => round($itemArray['revenue'] ?? 0, 2),
                'average_order_value' => round($itemArray['average_order_value'] ?? 0, 2),
                'items_sold' => $itemArray['items_sold'] ?? 0,
            ];
        });

        return [
            'period' => $data['period'] ?? null,
            'summary' => [
                'total_revenue' => round($data['summary']['total_revenue'] ?? 0, 2),
                'total_orders' => $data['summary']['total_orders'] ?? 0,
                'average_order_value' => round($data['summary']['average_order_value'] ?? 0, 2),
                'total_items_sold' => $data['summary']['total_items_sold'] ?? 0,
            ],
            'data' => $mappedData,
        ];
    }
}
