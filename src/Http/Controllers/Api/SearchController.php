<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Resources\SearchResource;
use Cartino\Repositories\SearchRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends ApiController
{
    public function __construct(
        private readonly SearchRepository $repository,
    ) {}

    /**
     * Dynamic search endpoint for headless frontend
     *
     * Features:
     * - Full-text search on title and JSON data fields
     * - Dynamic filters (collection, status, author, tags, category, dates)
     * - Breadcrumbs generation based on active filters
     * - Faceted search (available filters with counts)
     * - Optimized with eager loading to prevent N+1 queries
     *
     * Example request:
     * POST /api/search
     * {
     *   "q": "laravel",
     *   "collection": "blog",
     *   "locale": "it",
     *   "status": "published",
     *   "tags": ["php", "backend"],
     *   "category": "tutorial",
     *   "author_id": 1,
     *   "date_from": "2024-01-01",
     *   "date_to": "2024-12-31",
     *   "sort": "published_at",
     *   "sort_direction": "desc",
     *   "per_page": 20,
     *   "include_filters": true
     * }
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'collection' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', 'in:draft,published,scheduled'],
            'author_id' => ['nullable', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'category' => ['nullable', 'string'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'string', 'in:title,published_at,created_at,updated_at,order'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'include_filters' => ['nullable', 'boolean'],
        ]);

        // Execute search through repository (handles all query building, filtering, and optimization)
        $searchData = $this->repository->dynamicSearch($validated);

        // Extract results
        $results = $searchData['results'];
        $breadcrumbs = $searchData['breadcrumbs'];
        $filters = $searchData['filters'];

        // Return formatted response
        return $this->successResponse([
            'results' => SearchResource::collection($results),
            'breadcrumbs' => $breadcrumbs,
            'filters' => $filters,
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
            ],
            'links' => [
                'first' => $results->url(1),
                'last' => $results->url($results->lastPage()),
                'prev' => $results->previousPageUrl(),
                'next' => $results->nextPageUrl(),
            ],
        ]);
    }
}
