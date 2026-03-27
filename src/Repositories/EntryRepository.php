<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Entry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EntryRepository extends BaseRepository
{
    protected string $cachePrefix = 'entry';

    protected int $cacheTtl = 3600; // 1 hour

    protected function makeModel(): Model
    {
        return new Entry;
    }

    /**
     * Get paginated entries with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Entry::class)
            ->allowedFilters([
                'collection',
                'slug',
                'title',
                'locale',
                AllowedFilter::exact('status'),
                AllowedFilter::exact('author_id'),
                AllowedFilter::exact('parent_id'),
                AllowedFilter::scope('published'),
                AllowedFilter::scope('draft'),
                AllowedFilter::scope('scheduled'),
            ])
            ->allowedSorts(['title', 'slug', 'published_at', 'created_at', 'updated_at', 'order'])
            ->allowedIncludes(['author', 'parent', 'children'])
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }

    /**
     * Find one by ID
     */
    public function findOne(int|string $id): ?Entry
    {
        if (is_numeric($id)) {
            return $this->model->with(['author', 'parent', 'children'])->find($id);
        }

        // Try to find by slug
        return $this->model
            ->with(['author', 'parent', 'children'])
            ->where('slug', $id)
            ->first();
    }

    /**
     * Create a new entry
     */
    public function createOne(array $data): Entry
    {
        $entry = $this->model->create($data);
        $this->clearCache();

        return $entry->load(['author', 'parent']);
    }

    /**
     * Update an entry
     */
    public function updateOne(int $id, array $data): Entry
    {
        $entry = $this->model->findOrFail($id);
        $entry->update($data);
        $this->clearCache();

        return $entry->fresh(['author', 'parent', 'children']);
    }

    /**
     * Delete an entry
     */
    public function deleteOne(int $id): bool
    {
        $entry = $this->model->findOrFail($id);
        $deleted = $entry->delete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    /**
     * Check if entry can be deleted
     */
    public function canDelete(int $id): bool
    {
        $entry = $this->model->find($id);

        if (! $entry) {
            return false;
        }

        // Check if has children
        return ! $entry->children()->exists();
    }

    /**
     * Get entries by collection
     */
    public function getByCollection(string $collection, array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Entry::class)
            ->inCollection($collection)
            ->allowedFilters([
                'slug',
                'title',
                'locale',
                AllowedFilter::exact('status'),
                AllowedFilter::exact('author_id'),
                AllowedFilter::scope('published'),
            ])
            ->allowedSorts(['title', 'published_at', 'created_at', 'order'])
            ->allowedIncludes(['author', 'parent', 'children'])
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }

    /**
     * Get published entries
     */
    public function getPublished(?string $collection = null): Collection
    {
        $query = $this->model->published();

        if ($collection) {
            $query->inCollection($collection);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get entry by slug in collection
     */
    public function findBySlug(string $collection, string $slug, string $locale = 'it'): ?Entry
    {
        return $this->model
            ->inCollection($collection)
            ->inLocale($locale)
            ->where('slug', $slug)
            ->with(['author', 'parent', 'children'])
            ->first();
    }

    /**
     * Publish an entry
     */
    public function publish(int $id, ?\DateTime $publishedAt = null): Entry
    {
        $entry = $this->model->findOrFail($id);

        $entry->update([
            'status' => 'published',
            'published_at' => $publishedAt ?? now(),
        ]);

        $this->clearCache();

        return $entry->fresh();
    }

    /**
     * Unpublish an entry (set to draft)
     */
    public function unpublish(int $id): Entry
    {
        $entry = $this->model->findOrFail($id);

        $entry->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->clearCache();

        return $entry->fresh();
    }

    /**
     * Schedule an entry for future publication
     */
    public function schedule(int $id, \DateTime $publishedAt): Entry
    {
        $entry = $this->model->findOrFail($id);

        $entry->update([
            'status' => 'scheduled',
            'published_at' => $publishedAt,
        ]);

        $this->clearCache();

        return $entry->fresh();
    }

    /**
     * Reorder entries
     */
    public function reorder(array $order): bool
    {
        foreach ($order as $position => $id) {
            $this->model->where('id', $id)->update(['order' => $position]);
        }

        $this->clearCache();

        return true;
    }

    /**
     * Get tree structure for hierarchical entries
     */
    public function getTree(string $collection, string $locale = 'it'): Collection
    {
        return $this->model
            ->inCollection($collection)
            ->inLocale($locale)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('order');
            }])
            ->orderBy('order')
            ->get();
    }

    /**
     * Duplicate an entry
     */
    public function duplicate(int $id): Entry
    {
        $original = $this->model->findOrFail($id);

        $duplicate = $original->replicate();
        $duplicate->slug = $original->slug.'-copy';
        $duplicate->title = $original->title.' (Copia)';
        $duplicate->status = 'draft';
        $duplicate->published_at = null;
        $duplicate->save();

        $this->clearCache();

        return $duplicate;
    }
}
