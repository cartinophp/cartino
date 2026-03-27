<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'customer_id' => $this->customer_id,
            'rating' => $this->rating,
            'title' => $this->title,
            'content' => $this->content,
            'pros' => $this->pros,
            'cons' => $this->cons,
            'verified_purchase' => $this->verified_purchase,
            'helpful_count' => $this->helpful_count,
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
            'customer' => $this->whenLoaded('customer', fn () => new CustomerResource($this->customer)),
            'media' => ReviewMediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
