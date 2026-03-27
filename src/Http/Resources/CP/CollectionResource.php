<?php

declare(strict_types=1);

namespace Cartino\Http\Resources\CP;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'handle' => $this->handle,
            'description' => $this->description,
            'body_html' => $this->body_html,
            'status' => $this->status,
            'collection_type' => $this->collection_type,
            'rules' => $this->rules,
            'sort_order' => $this->sort_order,
            'disjunctive' => $this->disjunctive,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'seo' => $this->seo,
            'published_at' => $this->published_at?->toISOString(),
            'published_scope' => $this->published_scope,
            'template_suffix' => $this->template_suffix,
            'url' => $this->url,
            'image_url' => $this->image_url,
            'products_count' => $this->whenCounted('products'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
