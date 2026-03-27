<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
{
    /**
     * Transform the search result into an array.
     * This extends EntryResource with additional search-specific data
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'collection' => $this->collection,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->getExcerpt(),
            'data' => $this->data,
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'locale' => $this->locale,
            'order' => $this->order,
            'url' => $this->url(),
            'is_published' => $this->isPublished(),
            // Extract commonly used fields from JSON data for easier access
            'category' => $this->get('category'),
            'tags' => $this->get('tags', []),
            'image' => $this->get('image'),
            'featured' => $this->get('featured', false),
            // Relationships with eager loading optimization
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                ];
            }),
            'parent' => $this->whenLoaded('parent', function () {
                return $this->parent
                    ? [
                        'id' => $this->parent->id,
                        'collection' => $this->parent->collection,
                        'slug' => $this->parent->slug,
                        'title' => $this->parent->title,
                        'locale' => $this->parent->locale,
                    ] : null;
            }),
            'children_count' => $this->whenLoaded('children', function () {
                return $this->children->count();
            }),
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get excerpt from entry data or title
     */
    private function getExcerpt(): ?string
    {
        // Try to get excerpt from data field
        if ($excerpt = $this->get('excerpt')) {
            return $excerpt;
        }

        // Try to get from content and limit to 200 chars
        if ($content = $this->get('content')) {
            return $this->limitText($content, 200);
        }

        // Fallback to title
        return $this->limitText($this->title, 100);
    }

    /**
     * Limit text to specified length
     */
    private function limitText(string $text, int $length): string
    {
        // Strip HTML tags
        $text = strip_tags($text);

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).'...';
    }
}
