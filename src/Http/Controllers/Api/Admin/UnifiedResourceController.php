<?php

namespace Cartino\Http\Controllers\Api\Admin;

use Cartino\Http\Controllers\Api\ApiController;
use Cartino\Services\FilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UnifiedResourceController extends ApiController
{
    protected FilterService $filterService;

    protected string $modelClass;

    protected string $resourceClass;

    protected array $relationships = [];

    public function __construct(FilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Display a listing of the resource with advanced filters
     */
    public function index(Request $request): JsonResponse
    {
        $this->validateModelClass();

        // Parse filter parameters
        $params = $this->filterService->parseRequest($request->all());

        // Get model configuration
        $config = $this->filterService->getModelConfig($this->modelClass);

        // Apply filters and pagination
        $query = $this->modelClass::query();

        // Apply any pre-filters (like active status)
        $this->applyPreFilters($query, $request);

        $results = $query->paginateFilter($params, $config['per_page']);

        return $this->success([
            'data' => $this->transformResults($results->items()),
            'meta' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
            ],
            'filters' => $params,
            'config' => [
                'available_operators' => config('cartino.filters.operators.enabled'),
                'filterable_fields' => $config['filterable'],
                'sortable_fields' => $config['sortable'],
                'searchable_fields' => $config['searchable'],
            ],
        ]);
    }

    /**
     * Display the specified resource
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $this->validateModelClass();

        $model = $this->modelClass::with($this->relationships)->findOrFail($id);

        return $this->success([
            'data' => $this->transformSingle($model),
        ]);
    }

    /**
     * Search resources
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $params = $this->filterService->parseRequest($request->all());
        $params['search'] = $request->get('q');

        $query = $this->modelClass::query();
        $this->applyPreFilters($query, $request);

        $results = $query->paginateFilter($params);

        return $this->success([
            'data' => $this->transformResults($results->items()),
            'meta' => [
                'total' => $results->total(),
                'query' => $request->get('q'),
            ],
        ]);
    }

    /**
     * Get resource statistics for dashboard
     */
    public function stats(Request $request): JsonResponse
    {
        $this->validateModelClass();

        $cacheKey = 'stats_'.strtolower(class_basename($this->modelClass));

        $stats = Cache::remember($cacheKey, 300, function () {
            $model = new $this->modelClass;

            $baseStats = [
                'total' => $model->count(),
                'recent' => $model->where('created_at', '>=', now()->subDays(7))->count(),
            ];

            // Add model-specific stats
            return array_merge($baseStats, $this->getModelSpecificStats($model));
        });

        return $this->success(['data' => $stats]);
    }

    /**
     * Bulk operations
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|string|in:delete,activate,deactivate,enable,disable',
        ]);

        $this->validateModelClass();

        $count = 0;

        switch ($request->action) {
            case 'delete':
                $count = $this->modelClass::whereIn('id', $request->ids)->delete();
                break;

            case 'activate':
            case 'enable':
                $count = $this->modelClass::whereIn('id', $request->ids)->update(['is_enabled' => true]);
                break;

            case 'deactivate':
            case 'disable':
                $count = $this->modelClass::whereIn('id', $request->ids)->update(['is_enabled' => false]);
                break;
        }

        // Clear cache
        $this->filterService->clearCache(class_basename($this->modelClass));

        return $this->success([
            'message' => "Bulk action completed successfully. {$count} records affected.",
            'affected_count' => $count,
        ]);
    }

    /**
     * Export filtered data
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|string|in:csv,xlsx,json',
            'limit' => 'integer|max:10000',
        ]);

        $params = $this->filterService->parseRequest($request->all());
        $limit = $request->get('limit', 1000);

        $query = $this->modelClass::query();
        $this->applyPreFilters($query, $request);

        $results = $query->filter($params)->limit($limit)->get();

        // In a real implementation, you'd generate the file and return a download URL
        return $this->success([
            'message' => 'Export initiated',
            'records_count' => $results->count(),
            'format' => $request->format,
            // 'download_url' => $downloadUrl,
        ]);
    }

    /**
     * Apply pre-filters before applying user filters
     */
    protected function applyPreFilters($query, Request $request): void
    {
        // Override in subclasses for model-specific pre-filters
    }

    /**
     * Transform results collection
     */
    protected function transformResults($items): array
    {
        if ($this->resourceClass && class_exists($this->resourceClass)) {
            return $this->resourceClass::collection($items)->toArray(request());
        }

        return $items->toArray();
    }

    /**
     * Transform single model
     */
    protected function transformSingle($model): array
    {
        if ($this->resourceClass && class_exists($this->resourceClass)) {
            return (new $this->resourceClass($model))->toArray(request());
        }

        return $model->toArray();
    }

    /**
     * Get model-specific statistics
     */
    protected function getModelSpecificStats($model): array
    {
        $stats = [];

        // Check for common model patterns
        if (method_exists($model, 'scopeEnabled') || isset($model->is_enabled)) {
            $stats['enabled'] = $model->where('is_enabled', true)->count();
            $stats['disabled'] = $model->where('is_enabled', false)->count();
        }

        if (method_exists($model, 'scopeActive') || isset($model->status)) {
            $stats['active'] = $model->where('status', 'active')->count();
        }

        return $stats;
    }

    /**
     * Validate that model class is set
     */
    protected function validateModelClass(): void
    {
        if (! $this->modelClass) {
            throw new \InvalidArgumentException('Model class must be defined in controller');
        }

        if (! class_exists($this->modelClass)) {
            throw new \InvalidArgumentException("Model class {$this->modelClass} does not exist");
        }
    }
}
