<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_review_id' => $this->product_review_id,
            'type' => $this->type,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
        ];
    }
}
