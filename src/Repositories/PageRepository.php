<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PageRepository extends BaseRepository
{
    protected string $cachePrefix = 'pages';

    protected function makeModel(): Model
    {
        return new Page;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Page::class)
            ->allowedFilters([
                'title',
                'slug',
                'status',
                AllowedFilter::exact('site_id'),
                AllowedFilter::scope('published'),
            ])
            ->allowedSorts(['title', 'created_at', 'published_at'])
            ->allowedIncludes(['site'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or slug
     */
    public function findOne(int|string $slugOrId): ?Page
    {
        return $this->model
            ->where('id', $slugOrId)
            ->orWhere('slug', $slugOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Page
    {
        $page = $this->model->create($data);

        $this->clearCache();

        return $page;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Page
    {
        $page = $this->findOrFail($id);
        $page->update($data);

        $this->clearCache();

        return $page->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $page = $this->findOrFail($id);
        $deleted = $page->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Pages can always be deleted
    }

    /**
     * Toggle page status
     */
    public function toggleStatus(int $id): Page
    {
        $page = $this->findOrFail($id);
        $newStatus = $page->status === 'published' ? 'draft' : 'published';
        $page->update(['status' => $newStatus]);

        $this->clearCache();

        return $page->fresh();
    }
}
