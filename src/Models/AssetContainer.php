<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;

class AssetContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'handle',
        'title',
        'disk',
        'allow_uploads',
        'allow_downloading',
        'allow_renaming',
        'allow_moving',
        'allowed_extensions',
        'max_file_size',
        'settings',
        'glide_presets',
    ];

    protected $casts = [
        'allow_uploads' => 'boolean',
        'allow_downloading' => 'boolean',
        'allow_renaming' => 'boolean',
        'allow_moving' => 'boolean',
        'allowed_extensions' => 'array',
        'max_file_size' => 'integer',
        'settings' => 'array',
        'glide_presets' => 'array',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'container', 'handle');
    }

    public function folders(): HasMany
    {
        return $this->hasMany(AssetFolder::class, 'container', 'handle');
    }

    public function disk(): FilesystemAdapter
    {
        return \Storage::disk($this->disk);
    }

    public function url(string $path): string
    {
        return $this->disk()->url($path);
    }

    public function canUpload(): bool
    {
        return $this->allow_uploads;
    }

    public function canDownload(): bool
    {
        return $this->allow_downloading;
    }

    public function validateFile(UploadedFile $file): void
    {
        $extension = $file->getClientOriginalExtension();

        if ($this->allowed_extensions && ! in_array(strtolower($extension), $this->allowed_extensions)) {
            throw new \InvalidArgumentException("File type .{$extension} is not allowed in this container.");
        }

        if ($this->max_file_size && $file->getSize() > $this->max_file_size) {
            $maxMb = round(($this->max_file_size / 1024) / 1024, 2);
            throw new \InvalidArgumentException("File size exceeds maximum allowed size of {$maxMb}MB.");
        }
    }

    public function getPreset(string $name): ?array
    {
        return $this->glide_presets[$name] ?? config("media.presets.{$name}");
    }
}
