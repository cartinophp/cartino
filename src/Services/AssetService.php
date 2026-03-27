<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\Asset;
use Cartino\Models\AssetContainer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class AssetService
{
    public function __construct(
        protected ImageManager $imageManager,
    ) {}

    public function upload(
        UploadedFile $file,
        string $container,
        ?string $folder = null,
        ?int $userId = null,
    ): Asset {
        $containerModel = AssetContainer::where('handle', $container)->firstOrFail();

        // Validate file
        $containerModel->validateFile($file);

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file, $container, $folder);
        $path = $folder ? "{$folder}/{$filename}" : $filename;

        // Calculate file hash for deduplication
        $hash = hash_file('sha256', $file->getRealPath());

        // Check for duplicate
        if (config('media.validation.check_duplicates')) {
            $duplicate = Asset::where('container', $container)
                ->where('hash', $hash)
                ->where('size', $file->getSize())
                ->first();

            if ($duplicate) {
                return $duplicate;
            }
        }

        // Store file
        $stored = $file->storeAs($folder ?? '', $filename, ['disk' => $containerModel->disk]);

        if (! $stored) {
            throw new \RuntimeException('Failed to store file');
        }

        // Get file info
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);

        // Get image/video dimensions if applicable
        $dimensions = $this->getMediaDimensions($file, $mimeType);

        // Create asset record
        $asset = Asset::create([
            'container' => $container,
            'folder' => $folder ?? '',
            'basename' => $filename,
            'filename' => $filenameWithoutExt,
            'extension' => $extension,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $size,
            'width' => $dimensions['width'] ?? null,
            'height' => $dimensions['height'] ?? null,
            'duration' => $dimensions['duration'] ?? null,
            'aspect_ratio' => $dimensions['aspect_ratio'] ?? null,
            'hash' => $hash,
            'uploaded_by' => $userId ?? auth()->id(),
            'meta' => [
                'alt' => $filenameWithoutExt,
                'title' => $filenameWithoutExt,
            ],
        ]);

        // Optimize image if enabled
        if (config('media.optimization.enabled') && str_starts_with($mimeType, 'image/')) {
            $this->optimizeImage($asset);
        }

        return $asset->fresh();
    }

    protected function generateUniqueFilename(UploadedFile $file, string $container, ?string $folder): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($originalName);

        $filename = $slug.'.'.$extension;
        $counter = 1;

        while ($this->fileExists($container, $folder, $filename)) {
            $filename = $slug.'-'.$counter.'.'.$extension;
            $counter++;
        }

        return $filename;
    }

    protected function fileExists(string $container, ?string $folder, string $filename): bool
    {
        $path = $folder ? "{$folder}/{$filename}" : $filename;

        return Asset::where('container', $container)->where('path', $path)->exists();
    }

    protected function getMediaDimensions(UploadedFile $file, string $mimeType): array
    {
        $dimensions = [];

        try {
            if (str_starts_with($mimeType, 'image/')) {
                $image = $this->imageManager->read($file->getRealPath());
                $dimensions['width'] = $image->width();
                $dimensions['height'] = $image->height();

                if ($dimensions['width'] && $dimensions['height']) {
                    $dimensions['aspect_ratio'] = round($dimensions['width'] / $dimensions['height'], 4);
                }
            } elseif (str_starts_with($mimeType, 'video/')) {
                // For video, you might want to use FFmpeg
                // This is a placeholder - implement based on your needs
                $dimensions = $this->getVideoDimensions($file);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            logger()->warning('Failed to get media dimensions: '.$e->getMessage());
        }

        return $dimensions;
    }

    protected function getVideoDimensions(UploadedFile $file): array
    {
        // Placeholder for video dimension extraction
        // You can use PHP-FFMpeg package for this
        return [];
    }

    protected function optimizeImage(Asset $asset): void
    {
        try {
            $disk = $asset->disk();
            $path = $asset->path;
            $tempPath = storage_path('app/temp/'.$asset->basename);

            // Download file to temp location
            file_put_contents($tempPath, $disk->get($path));

            $optimized = false;

            // Run optimization tools based on file type
            if ($asset->extension === 'jpg' || $asset->extension === 'jpeg') {
                $optimized = $this->optimizeJpeg($tempPath);
            } elseif ($asset->extension === 'png') {
                $optimized = $this->optimizePng($tempPath);
            }

            if ($optimized) {
                // Upload optimized file back
                $disk->put($path, file_get_contents($tempPath));

                // Update file size
                $asset->update(['size' => filesize($tempPath)]);
            }

            // Clean up temp file
            @unlink($tempPath);
        } catch (\Exception $e) {
            logger()->warning('Image optimization failed: '.$e->getMessage());
        }
    }

    protected function optimizeJpeg(string $path): bool
    {
        $config = config('media.optimization.tools.jpegoptim');

        if (! $config['enabled'] || ! file_exists($config['path'])) {
            return false;
        }

        $options = implode(' ', $config['options']);
        $command = sprintf('%s %s %s', $config['path'], $options, escapeshellarg($path));
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    protected function optimizePng(string $path): bool
    {
        $config = config('media.optimization.tools.optipng');

        if (! $config['enabled'] || ! file_exists($config['path'])) {
            return false;
        }

        $options = implode(' ', $config['options']);
        $command = sprintf('%s %s %s', $config['path'], $options, escapeshellarg($path));
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    public function delete(Asset $asset): bool
    {
        return $asset->delete();
    }

    public function move(Asset $asset, string $newFolder): Asset
    {
        $oldPath = $asset->path;
        $newPath = $newFolder ? "{$newFolder}/{$asset->basename}" : $asset->basename;

        $disk = $asset->disk();

        if (! $disk->move($oldPath, $newPath)) {
            throw new \RuntimeException('Failed to move file');
        }

        $asset->update([
            'folder' => $newFolder,
            'path' => $newPath,
        ]);

        return $asset->fresh();
    }

    public function rename(Asset $asset, string $newFilename): Asset
    {
        // Ensure extension is preserved
        $extension = $asset->extension;
        if (! str_ends_with($newFilename, ".{$extension}")) {
            $newFilename .= ".{$extension}";
        }

        $folder = $asset->folder;
        $newPath = $folder ? "{$folder}/{$newFilename}" : $newFilename;

        $disk = $asset->disk();

        if (! $disk->move($asset->path, $newPath)) {
            throw new \RuntimeException('Failed to rename file');
        }

        $filenameWithoutExt = pathinfo($newFilename, PATHINFO_FILENAME);

        $asset->update([
            'basename' => $newFilename,
            'filename' => $filenameWithoutExt,
            'path' => $newPath,
        ]);

        return $asset->fresh();
    }

    public function updateMeta(Asset $asset, array $meta): Asset
    {
        $currentMeta = $asset->meta ?? [];
        $asset->update([
            'meta' => array_merge($currentMeta, $meta),
        ]);

        return $asset->fresh();
    }

    public function setFocusPoint(Asset $asset, int $x, int $y): Asset
    {
        // Convert coordinates to Glide crop format (0-100%)
        $focusCss = "{$x}-{$y}";

        $asset->update(['focus_css' => $focusCss]);

        return $asset->fresh();
    }
}
