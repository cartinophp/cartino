<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : ((array) $this->resource);

        $topSelling = collect($data['top_selling'] ?? [])->map(function ($product) {
            $productArray = is_array($product) ? $product : ((array) $product);

            return [
                'id' => $productArray['id'] ?? null,
                'name' => $productArray['name'] ?? null,
                'sku' => $productArray['sku'] ?? null,
                'units_sold' => $productArray['units_sold'] ?? 0,
                'revenue' => round($productArray['revenue'] ?? 0, 2),
                'current_stock' => $productArray['stock_quantity'] ?? 0,
            ];
        });

        $topRevenue = collect($data['top_revenue'] ?? [])->map(function ($product) {
            $productArray = is_array($product) ? $product : ((array) $product);
            $unitsSold = $productArray['units_sold'] ?? 0;
            $revenue = $productArray['revenue'] ?? 0;

            return [
                'id' => $productArray['id'] ?? null,
                'name' => $productArray['name'] ?? null,
                'sku' => $productArray['sku'] ?? null,
                'units_sold' => $unitsSold,
                'revenue' => round($revenue, 2),
                'average_price' => $unitsSold > 0 ? round($revenue / $unitsSold, 2) : 0,
            ];
        });

        return [
            'period' => $data['period'] ?? null,
            'top_selling' => $topSelling,
            'top_revenue' => $topRevenue,
        ];
    }
}
