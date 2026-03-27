<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetContainerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'handle' => $this->handle,
            'title' => $this->title,
            'disk' => $this->disk,
            'allow_uploads' => $this->allow_uploads,
            'allow_downloading' => $this->allow_downloading,
            'allow_renaming' => $this->allow_renaming,
            'allow_moving' => $this->allow_moving,
            'allowed_extensions' => $this->allowed_extensions,
            'max_file_size' => $this->max_file_size,
            'max_file_size_mb' => $this->max_file_size ? round(($this->max_file_size / 1024) / 1024, 2) : null,
            'settings' => $this->settings,
            'glide_presets' => $this->glide_presets,
            'assets_count' => $this->whenCounted('assets'),
            'recent_assets' => $this->whenLoaded('assets', fn () => AssetResource::collection($this->assets)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
