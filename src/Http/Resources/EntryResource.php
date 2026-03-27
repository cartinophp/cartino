<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'collection' => $this->collection,
            'slug' => $this->slug,
            'title' => $this->title,
            'data' => $this->data,
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'locale' => $this->locale,
            'order' => $this->order,
            'url' => $this->url(),
            'is_published' => $this->isPublished(),
            // Relationships
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                ];
            }),
            'parent' => new EntryResource($this->whenLoaded('parent')),
            'children' => EntryResource::collection($this->whenLoaded('children')),
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
