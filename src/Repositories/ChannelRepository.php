<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\QueryBuilder;

class ChannelRepository extends BaseRepository
{
    protected string $cachePrefix = 'channels';

    protected function makeModel(): Model
    {
        return new Channel;
    }

    /**
     * Get paginated channels with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return $query = QueryBuilder::for(Channel::class)
            ->allowedFilters(['name', 'slug', 'is_active'])
            ->allowedSorts(['name', 'created_at'])
            ->allowedIncludes(['site'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page'))
            ->appends($filters);
    }

    /**
     * Find one by ID or slug
     */
    public function findOne(int|string $slugOrId): ?Channel
    {
        return $this->model
            ->where('id', $slugOrId)
            ->orWhere('slug', $slugOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Channel
    {
        $channel = $this->model->create($data);
        $this->clearCache();

        return $channel;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Channel
    {
        $channel = $this->findOrFail($id);
        $channel->update($data);
        $this->clearCache();

        return $channel->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $channel = $this->findOrFail($id);
        $deleted = $channel->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Get active channels
     */
    public function getActive(): Category
    {
        $cacheKey = $this->getCacheKey('active', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get default channel
     */
    public function getDefault(): ?Channel
    {
        $cacheKey = $this->getCacheKey('default', 'channel');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->where('is_default', true)->first();
        });
    }

    /**
     * Toggle channel status
     */
    public function toggleStatus(int $id): Channel
    {
        $channel = $this->findOrFail($id);
        $newStatus = $channel->is_active ? false : true;
        $channel->update(['is_active' => $newStatus]);

        $this->clearCache();

        return $channel->fresh();
    }

    /**
     * Set as default channel
     */
    public function setAsDefault(int $id): Channel
    {
        // Remove default from all channels
        $this->model->where('is_default', true)->update(['is_default' => false]);

        // Set new default
        $channel = $this->findOrFail($id);
        $channel->update(['is_default' => true]);

        $this->clearCache();

        return $channel->fresh();
    }

    /**
     * Check if channel can be deleted
     */
    public function canDelete(int $id): bool
    {
        $channel = $this->find($id);

        if (! $channel) {
            return false;
        }

        // Cannot delete default channel
        if ($channel->is_default) {
            return false;
        }

        // Check if channel has products or orders
        return ! $channel->products()->exists() && ! $channel->orders()->exists();
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(array $ids, bool $status): int
    {
        $updated = $this->model->whereIn('id', $ids)->update(['is_active' => $status]);
        $this->clearCache();

        return $updated;
    }
}
