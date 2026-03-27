<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_id' => $this->site_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'url' => $this->url,
            'is_default' => $this->is_default,
            'status' => $this->status,
            'locales' => $this->locales,
            'currencies' => $this->currencies,
            'settings' => $this->settings,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // Relationships
            'site' => new SiteResource($this->whenLoaded('site')),
        ];
    }
}
