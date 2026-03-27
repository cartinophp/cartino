<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'container' => $this->container,
            'folder' => $this->folder,
            'path' => $this->path,
            'basename' => $this->basename,
            'filename' => $this->filename,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'type' => $this->type,
            'size' => $this->size,
            'size_human' => $this->humanFileSize(),
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'aspect_ratio' => $this->aspect_ratio,
            'url' => $this->url,
            'meta' => $this->meta,
            'data' => $this->data,
            'focus_css' => $this->focus_css,
            'hash' => $this->hash,
            'is_image' => $this->is_image,
            'is_video' => $this->is_video,
            'is_audio' => $this->is_audio,
            'is_document' => $this->is_document,
            'uploaded_by' => $this->whenLoaded('uploadedBy', fn () => [
                'id' => $this->uploadedBy->id,
                'name' => $this->uploadedBy->name,
            ]),
            'transformations' => $this->whenLoaded(
                'transformations',
                fn () => AssetTransformationResource::collection($this->transformations),
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
