<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : ((array) $this->resource);

        $lowStockProducts = collect($data['low_stock_products'] ?? [])->map(function ($product) {
            $productArray = is_array($product) ? $product : ((array) $product);

            return [
                'id' => $productArray['id'] ?? null,
                'name' => $productArray['name'] ?? null,
                'sku' => $productArray['sku'] ?? null,
                'stock_quantity' => $productArray['stock_quantity'] ?? 0,
                'price_amount' => round($productArray['price_amount'] ?? 0, 2),
            ];
        });

        $outOfStockProducts = collect($data['out_of_stock_products'] ?? [])->map(function ($product) {
            $productArray = is_array($product) ? $product : ((array) $product);

            return [
                'id' => $productArray['id'] ?? null,
                'name' => $productArray['name'] ?? null,
                'sku' => $productArray['sku'] ?? null,
                'price_amount' => round($productArray['price_amount'] ?? 0, 2),
            ];
        });

        return [
            'summary' => [
                'total_products' => $data['summary']['total_products'] ?? 0,
                'in_stock' => $data['summary']['in_stock'] ?? 0,
                'low_stock' => $data['summary']['low_stock'] ?? 0,
                'out_of_stock' => $data['summary']['out_of_stock'] ?? 0,
                'total_inventory_value' => round($data['summary']['total_inventory_value'] ?? 0, 2),
            ],
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
        ];
    }
}
