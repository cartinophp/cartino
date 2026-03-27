<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'url' => $this->url,
            'target' => $this->target,
            'sort_order' => $this->sort_order,
            'children' => MenuItemResource::collection($this->whenLoaded('children')),
        ];
    }
}
