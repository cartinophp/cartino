<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Services\GlideService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Asset extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'container',
        'folder',
        'basename',
        'filename',
        'extension',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'duration',
        'aspect_ratio',
        'meta',
        'data',
        'focus_css',
        'uploaded_by',
        'hash',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'aspect_ratio' => 'decimal:4',
        'meta' => 'array',
        'data' => 'array',
    ];

    protected $appends = [
        'url',
        'type',
        'is_image',
        'is_video',
        'is_audio',
        'is_document',
    ];

    public function containerModel(): BelongsTo
    {
        return $this->belongsTo(AssetContainer::class, 'container', 'handle');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function transformations(): HasMany
    {
        return $this->hasMany(AssetTransformation::class);
    }

    public function folderModel(): BelongsTo
    {
        return $this->belongsTo(AssetFolder::class, 'folder', 'path')->where('container', $this->container);
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        $container = $this->containerModel;

        return $container ? $container->url($this->path) : Storage::url($this->path);
    }

    public function getTypeAttribute(): string
    {
        return $this->getFileType();
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function getIsAudioAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    public function getIsDocumentAttribute(): bool
    {
        return in_array($this->extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    }

    // Methods
    public function getFileType(): string
    {
        if ($this->is_image) {
            return 'image';
        }
        if ($this->is_video) {
            return 'video';
        }
        if ($this->is_audio) {
            return 'audio';
        }
        if ($this->is_document) {
            return 'document';
        }

        return 'file';
    }

    public function disk(): FilesystemAdapter
    {
        $container = $this->containerModel;

        return $container ? $container->disk() : Storage::disk();
    }

    public function exists(): bool
    {
        return $this->disk()->exists($this->path);
    }

    public function delete(): ?bool
    {
        // Delete physical file
        if ($this->exists()) {
            $this->disk()->delete($this->path);
        }

        // Delete all transformations
        $this->transformations()->each(function ($transformation) {
            $transformation->deleteFile();
            $transformation->delete();
        });

        return parent::delete();
    }

    public function glide(array $params = [], ?string $preset = null): string
    {
        if ($preset) {
            $presetParams = $this->containerModel?->getPreset($preset) ?? config("media.presets.{$preset}", []);
            $params = array_merge($presetParams, $params);
        }

        // Apply focus point if available
        if ($this->focus_css && ! isset($params['crop'])) {
            $params['crop'] = $this->focus_css;
        }

        return app(GlideService::class)->url($this->path, $params);
    }

    public function responsive(?array $breakpoints = null): array
    {
        $breakpoints = $breakpoints ?? config('media.responsive.breakpoints', []);
        $srcset = [];

        foreach ($breakpoints as $width) {
            $url = $this->glide(['w' => $width]);
            $srcset[] = "{$url} {$width}w";
        }

        return [
            'src' => $this->url,
            'srcset' => implode(', ', $srcset),
            'sizes' => '100vw',
        ];
    }

    public function getMeta(string $key, $default = null)
    {
        return data_get($this->meta, $key, $default);
    }

    public function setMeta(string $key, $value): self
    {
        $meta = $this->meta ?? [];
        data_set($meta, $key, $value);
        $this->meta = $meta;

        return $this;
    }

    public function alt(): ?string
    {
        return $this->getMeta('alt');
    }

    public function title(): ?string
    {
        return $this->getMeta('title') ?? $this->filename;
    }

    public function caption(): ?string
    {
        return $this->getMeta('caption');
    }

    public function humanFileSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    // Scopes
    public function scopeInContainer($query, string $container)
    {
        return $query->where('container', $container);
    }

    public function scopeInFolder($query, string $folder)
    {
        return $query->where('folder', $folder);
    }

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    public function scopeDocuments($query)
    {
        return $query->whereIn('extension', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q
                ->where('filename', 'like', "%{$search}%")
                ->orWhere('basename', 'like', "%{$search}%")
                ->orWhereRaw("meta->>'alt' like ?", ["%{$search}%"])
                ->orWhereRaw("meta->>'title' like ?", ["%{$search}%"]);
        });
    }
}
