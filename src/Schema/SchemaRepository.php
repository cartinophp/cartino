<?php

namespace Cartino\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SchemaRepository
{
    protected string $basePath;

    protected array $cache = [];

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? resource_path('schemas');
    }

    /**
     * Get collection schema.
     */
    public function getCollection(string $handle): ?Collection
    {
        return $this->loadSchema('collections', $handle);
    }

    /**
     * Get entry schema.
     */
    public function getEntry(string $collection, string $handle): ?Collection
    {
        return $this->loadSchema("collections/{$collection}/entries", $handle);
    }

    /**
     * Get fieldset schema.
     */
    public function getFieldset(string $handle): ?Collection
    {
        return $this->loadSchema('fieldsets', $handle);
    }

    /**
     * Get blueprint schema.
     */
    public function getBlueprint(string $handle): ?Collection
    {
        return $this->loadSchema('blueprints', $handle);
    }

    /**
     * Get all collections.
     */
    public function getAllCollections(): Collection
    {
        $collectionsPath = $this->basePath.'/collections';

        if (! File::exists($collectionsPath)) {
            return collect();
        }

        return collect(File::files($collectionsPath))
            ->filter(fn ($file) => $file->getExtension() === 'json')
            ->map(fn ($file) => $this->loadSchemaFromFile($file->getPathname()))
            ->filter()
            ->values();
    }

    /**
     * Get all fieldsets.
     */
    public function getAllFieldsets(): Collection
    {
        $fieldsetsPath = $this->basePath.'/fieldsets';

        if (! File::exists($fieldsetsPath)) {
            return collect();
        }

        return collect(File::files($fieldsetsPath))
            ->filter(fn ($file) => $file->getExtension() === 'json')
            ->map(fn ($file) => $this->loadSchemaFromFile($file->getPathname()))
            ->filter()
            ->values();
    }

    /**
     * Save collection schema.
     */
    public function saveCollection(string $handle, array $schema): bool
    {
        return $this->saveSchema('collections', $handle, $schema);
    }

    /**
     * Save entry schema.
     */
    public function saveEntry(string $collection, string $handle, array $schema): bool
    {
        return $this->saveSchema("collections/{$collection}/entries", $handle, $schema);
    }

    /**
     * Save fieldset schema.
     */
    public function saveFieldset(string $handle, array $schema): bool
    {
        return $this->saveSchema('fieldsets', $handle, $schema);
    }

    /**
     * Delete schema file.
     */
    public function delete(string $type, string $handle): bool
    {
        $filePath = $this->getSchemaPath($type, $handle);

        if (File::exists($filePath)) {
            unset($this->cache[$filePath]);

            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Load schema from file system.
     */
    protected function loadSchema(string $type, string $handle): ?Collection
    {
        $filePath = $this->getSchemaPath($type, $handle);

        if (isset($this->cache[$filePath])) {
            return $this->cache[$filePath];
        }

        if (! File::exists($filePath)) {
            return null;
        }

        $schema = $this->loadSchemaFromFile($filePath);
        $this->cache[$filePath] = $schema;

        return $schema;
    }

    /**
     * Load and parse schema file.
     */
    protected function loadSchemaFromFile(string $filePath): ?Collection
    {
        try {
            $content = File::get($filePath);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \JsonException('Invalid JSON: '.json_last_error_msg());
            }

            return collect($data);
        } catch (\Exception $e) {
            logger()->error("Failed to load schema from {$filePath}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Save schema to file.
     */
    protected function saveSchema(string $type, string $handle, array $schema): bool
    {
        $filePath = $this->getSchemaPath($type, $handle);
        $directory = dirname($filePath);

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        try {
            $content = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $result = File::put($filePath, $content);

            // Update cache
            $this->cache[$filePath] = collect($schema);

            return $result !== false;
        } catch (\Exception $e) {
            logger()->error("Failed to save schema to {$filePath}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Get full file path for schema.
     */
    protected function getSchemaPath(string $type, string $handle): string
    {
        return $this->basePath.'/'.$type.'/'.$handle.'.json';
    }

    /**
     * Clear cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Validate schema structure.
     */
    public function validateSchema(array $schema): array
    {
        $errors = [];

        // Required fields
        if (empty($schema['title'])) {
            $errors['title'] = 'Title is required';
        }

        if (empty($schema['handle'])) {
            $errors['handle'] = 'Handle is required';
        }

        // Validate handle format
        if (! empty($schema['handle']) && ! preg_match('/^[a-z0-9_-]+$/', $schema['handle'])) {
            $errors['handle'] = 'Handle must contain only lowercase letters, numbers, hyphens, and underscores';
        }

        // Validate fields structure
        if (! empty($schema['fields']) && ! is_array($schema['fields'])) {
            $errors['fields'] = 'Fields must be an array';
        }

        return $errors;
    }
}
