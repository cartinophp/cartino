<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Cartino\Models\Courier;
use Illuminate\Http\Request;

/**
 * @mixin Courier
 */
class CourierResource extends BaseResource
{
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'description' => $this->description,
            'website' => $this->website,
            'tracking_url' => $this->tracking_url,
            'delivery_time_min' => $this->delivery_time_min,
            'delivery_time_max' => $this->delivery_time_max,
            'delivery_time' => $this->delivery_time,
            'status' => $this->status,
            'is_enabled' => $this->is_enabled,
            'logo_url' => $this->logo,
            'seo' => $this->seo,
            'meta' => $this->meta,
            'orders_count' => $this->whenCounted('orders') ?? $this->orders()->count(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // Display values
            'display_status' => $this->status === 'active' ? 'Attivo' : 'Inattivo',
            'is_active' => $this->status === 'active',
        ];
    }
}
