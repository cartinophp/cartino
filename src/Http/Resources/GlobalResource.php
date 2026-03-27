<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GlobalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'handle' => $this->handle,
            'title' => $this->title,
            'data' => $this->data,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
