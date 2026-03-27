<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Entry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SearchRepository extends BaseRepository
{
    protected string $cachePrefix = 'search';

    protected int $cacheTtl = 1800; // 30 minutes

    protected function makeModel(): Model
    {
        return new Entry;
    }

    /**
     * Dynamic search with filters, breadcrumbs and facets
     * Optimized with eager loading to prevent N+1 queries
     */
    public function dynamicSearch(array $filters): array
    {
        // Build optimized query with eager loading
        $query = $this->buildSearchQuery($filters);

        // Apply sorting
        $this->applySorting($query, $filters);

        // Paginate results
        $perPage = $filters['per_page'] ?? 15;
        $results = $query->paginate($perPage);

        // Generate breadcrumbs
        $breadcrumbs = $this->generateBreadcrumbs($filters);

        // Generate available filters (facets) if requested
        $availableFilters = [];
        if ($filters['include_filters'] ?? false) {
            $availableFilters = $this->generateAvailableFilters($filters);
        }

        return [
            'results' => $results,
            'breadcrumbs' => $breadcrumbs,
            'filters' => $availableFilters,
        ];
    }

    /**
     * Build search query with all filters and eager loading
     */
    private function buildSearchQuery(array $filters)
    {
        $query = Entry::query()
            ->with([
                'author:id,name,email',
                'parent:id,collection,slug,title,locale',
                'children' => function ($q) {
                    $q->select(
                        'id',
                        'parent_id',
                        'collection',
                        'slug',
                        'title',
                        'status',
                        'published_at',
                        'locale',
                        'order',
                    )->orderBy('order');
                },
            ])
            ->select([
                'id',
                'collection',
                'slug',
                'title',
                'data',
                'status',
                'published_at',
                'author_id',
                'locale',
                'parent_id',
                'order',
                'created_at',
                'updated_at',
            ]);

        // Full-text search on title and JSON data
        if (! empty($filters['q'])) {
            $searchTerm = $filters['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")->orWhereRaw("JSON_SEARCH(data, 'one', ?) IS NOT NULL", [
                    "%{$searchTerm}%",
                ]);
            });
        }

        // Collection filter
        if (! empty($filters['collection'])) {
            $query->where('collection', $filters['collection']);
        }

        // Locale filter
        if (! empty($filters['locale'])) {
            $query->where('locale', $filters['locale']);
        } else {
            // Default to 'it' if not specified
            $query->where('locale', 'it');
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            // Default to published only
            $query
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                });
        }

        // Author filter
        if (! empty($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        // Parent filter
        if (isset($filters['parent_id'])) {
            if ($filters['parent_id'] === 0 || $filters['parent_id'] === '0') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        // Tags filter (search in JSON data)
        if (! empty($filters['tags']) && is_array($filters['tags'])) {
            foreach ($filters['tags'] as $tag) {
                $query->whereJsonContains('data->tags', $tag);
            }
        }

        // Category filter (search in JSON data)
        if (! empty($filters['category'])) {
            $query->where('data->category', $filters['category']);
        }

        // Date range filters
        if (! empty($filters['date_from'])) {
            $query->where('published_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('published_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort'] ?? 'published_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        // Validate sort field
        $allowedSorts = ['title', 'published_at', 'created_at', 'updated_at', 'order'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'published_at';
        }

        // Validate direction
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

        $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Generate breadcrumbs based on active filters
     */
    private function generateBreadcrumbs(array $filters): array
    {
        $breadcrumbs = [
            [
                'title' => 'Home',
                'url' => '/',
                'active' => false,
            ],
        ];

        // Add collection breadcrumb
        if (! empty($filters['collection'])) {
            $collectionTitle = $this->getCollectionTitle($filters['collection']);
            $breadcrumbs[] = [
                'title' => $collectionTitle,
                'url' => "/search?collection={$filters['collection']}",
                'active' => empty($filters['q']) && empty($filters['category']),
            ];
        }

        // Add category breadcrumb
        if (! empty($filters['category'])) {
            $breadcrumbs[] = [
                'title' => ucfirst($filters['category']),
                'url' => "/search?collection={$filters['collection']}&category={$filters['category']}",
                'active' => empty($filters['q']),
            ];
        }

        // Add search term breadcrumb
        if (! empty($filters['q'])) {
            $breadcrumbs[] = [
                'title' => "Risultati per: {$filters['q']}",
                'url' => null,
                'active' => true,
            ];
        }

        // If no specific filter, mark last breadcrumb as active
        if (! empty($breadcrumbs) && ! $this->hasActiveBreadcrumb($breadcrumbs)) {
            $breadcrumbs[count($breadcrumbs) - 1]['active'] = true;
        }

        return $breadcrumbs;
    }

    /**
     * Get human-readable title for collection
     */
    private function getCollectionTitle(string $collection): string
    {
        $titles = [
            'blog' => 'Blog',
            'news' => 'News',
            'articles' => 'Articoli',
            'pages' => 'Pagine',
            'products' => 'Prodotti',
        ];

        return $titles[$collection] ?? ucfirst($collection);
    }

    /**
     * Check if any breadcrumb is active
     */
    private function hasActiveBreadcrumb(array $breadcrumbs): bool
    {
        foreach ($breadcrumbs as $breadcrumb) {
            if ($breadcrumb['active']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate available filters (facets) based on current result set
     * Uses optimized single queries to avoid N+1
     */
    private function generateAvailableFilters(array $currentFilters): array
    {
        $locale = $currentFilters['locale'] ?? 'it';

        // Build base conditions for all facet queries
        $baseConditions = function ($query) use ($currentFilters, $locale) {
            $query->where('locale', $locale);

            if (! empty($currentFilters['collection'])) {
                $query->where('collection', $currentFilters['collection']);
            }
        };

        // Get all facets in parallel to optimize performance
        return [
            'collections' => $this->getCollectionsFacet($locale),
            'authors' => $this->getAuthorsFacet($locale, $currentFilters),
            'statuses' => $this->getStatusesFacet($locale, $currentFilters),
            'tags' => $this->getTagsFacet($locale, $currentFilters),
            'categories' => $this->getCategoriesFacet($locale, $currentFilters),
        ];
    }

    /**
     * Get collections facet with counts
     */
    private function getCollectionsFacet(string $locale): array
    {
        return DB::table('entries')
            ->select('collection', DB::raw('COUNT(*) as count'))
            ->where('locale', $locale)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->groupBy('collection')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->collection,
                'label' => $this->getCollectionTitle($item->collection),
                'count' => $item->count,
            ])
            ->toArray();
    }

    /**
     * Get authors facet with counts
     */
    private function getAuthorsFacet(string $locale, array $filters): array
    {
        return DB::table('entries')
            ->join('users', 'entries.author_id', '=', 'users.id')
            ->select('users.id as value', 'users.name as label', DB::raw('COUNT(*) as count'))
            ->where('entries.locale', $locale)
            ->where('entries.status', 'published')
            ->where(function ($q) {
                $q->whereNull('entries.published_at')->orWhere('entries.published_at', '<=', now());
            })
            ->when(! empty($filters['collection']), function ($q) use ($filters) {
                $q->where('entries.collection', $filters['collection']);
            })
            ->groupBy('users.id', 'users.name')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->value,
                'label' => $item->label,
                'count' => $item->count,
            ])
            ->toArray();
    }

    /**
     * Get statuses facet with counts
     */
    private function getStatusesFacet(string $locale, array $filters): array
    {
        return DB::table('entries')
            ->select('status as value', DB::raw('COUNT(*) as count'))
            ->where('locale', $locale)
            ->when(! empty($filters['collection']), function ($q) use ($filters) {
                $q->where('collection', $filters['collection']);
            })
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->value,
                'label' => ucfirst($item->value),
                'count' => $item->count,
            ])
            ->toArray();
    }

    /**
     * Get tags facet with counts from JSON data field
     */
    private function getTagsFacet(string $locale, array $filters): array
    {
        $entries = DB::table('entries')
            ->select(DB::raw("JSON_EXTRACT(data, '$.tags') as tags"))
            ->where('locale', $locale)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->when(! empty($filters['collection']), function ($q) use ($filters) {
                $q->where('collection', $filters['collection']);
            })
            ->whereNotNull('data->tags')
            ->get();

        $tagCounts = [];

        foreach ($entries as $entry) {
            if ($entry->tags) {
                $tags = json_decode($entry->tags, true);
                if (is_array($tags)) {
                    foreach ($tags as $tag) {
                        if (is_string($tag)) {
                            $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        arsort($tagCounts);

        return collect($tagCounts)
            ->map(fn ($count, $tag) => [
                'value' => $tag,
                'label' => $tag,
                'count' => $count,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get categories facet with counts from JSON data field
     */
    private function getCategoriesFacet(string $locale, array $filters): array
    {
        return DB::table('entries')
            ->select(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.category')) as category"),
                DB::raw('COUNT(*) as count'),
            )
            ->where('locale', $locale)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->when(! empty($filters['collection']), function ($q) use ($filters) {
                $q->where('collection', $filters['collection']);
            })
            ->whereNotNull('data->category')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->category,
                'label' => ucfirst($item->category),
                'count' => $item->count,
            ])
            ->toArray();
    }
}
