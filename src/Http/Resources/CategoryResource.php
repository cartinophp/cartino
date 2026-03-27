<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'parent_id' => $this->parent_id,
            'level' => $this->level,
            'left' => $this->left,
            'right' => $this->right,
            'sort_order' => $this->sort_order,
            'is_enabled' => $this->is_enabled,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Relationships (recursive children)
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            // Tree info
            'has_children' => $this->when(isset($this->children_count), $this->children_count > 0),
            'children_count' => $this->when(isset($this->children_count), $this->children_count),
        ];
    }
}
