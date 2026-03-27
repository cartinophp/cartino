<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetTransformationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'preset' => $this->preset,
            'params' => $this->params,
            'url' => $this->url(),
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'access_count' => $this->access_count,
            'last_accessed_at' => $this->last_accessed_at,
            'created_at' => $this->created_at,
        ];
    }
}
