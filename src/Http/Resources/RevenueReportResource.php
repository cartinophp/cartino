<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : ((array) $this->resource);

        $timeline = collect($data['timeline'] ?? [])->map(function ($item) {
            $itemArray = is_array($item) ? $item : ((array) $item);

            return [
                'period' => $itemArray['period'] ?? null,
                'gross_sales' => round($itemArray['gross_sales'] ?? 0, 2),
                'discounts' => round($itemArray['discounts'] ?? 0, 2),
                'taxes' => round($itemArray['taxes'] ?? 0, 2),
                'shipping' => round($itemArray['shipping'] ?? 0, 2),
                'net_sales' => round($itemArray['net_sales'] ?? 0, 2),
                'orders_count' => $itemArray['orders_count'] ?? 0,
            ];
        });

        return [
            'period' => $data['period'] ?? null,
            'summary' => [
                'gross_sales' => round($data['summary']['gross_sales'] ?? 0, 2),
                'discounts' => round($data['summary']['discounts'] ?? 0, 2),
                'taxes' => round($data['summary']['taxes'] ?? 0, 2),
                'shipping' => round($data['summary']['shipping'] ?? 0, 2),
                'net_sales' => round($data['summary']['net_sales'] ?? 0, 2),
                'orders_count' => $data['summary']['orders_count'] ?? 0,
            ],
            'timeline' => $timeline,
        ];
    }
}
