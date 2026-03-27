<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp\Concerns;

use Cartino\Cp\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

trait HasInertiaActions
{
    /**
     * Define the repository instance in the controller
     */
    abstract protected function repository();

    /**
     * Define the resource class for responses
     */
    abstract protected function resourceClass(): string;

    /**
     * Define the entity name for messages (e.g., 'Product', 'Customer')
     */
    abstract protected function entityName(): string;

    /**
     * Define the component path for Inertia (e.g., 'products', 'customers')
     */
    abstract protected function componentPath(): string;

    /**
     * Display a listing of the resource
     */
    public function index(Request $request): Response
    {
        try {
            $filters = $this->getFilters($this->allowedFilters());
            $data = $this->repository()->findAll($filters, request('per_page', 15));

            $page = $this->buildIndexPage();

            $resourceClass = $this->resourceClass();

            return $this->inertiaResponse($this->componentPath().'/Index', [
                'page' => $page->compile(),
                'data' => $data->through(fn ($item) => new $resourceClass($item)),
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            return $this->inertiaResponse($this->componentPath().'/Index', [
                'page' => Page::make($this->entityName())->compile(),
                'error' => 'Error loading data: '.$e->getMessage(),
                'data' => [],
            ]);
        }
    }

    /**
     * Show the form for creating a new resource
     */
    public function create(): Response
    {
        try {
            $page = $this->buildCreatePage();

            return $this->inertiaResponse($this->componentPath().'/Create', [
                'page' => $page->compile(),
            ]);
        } catch (\Exception $e) {
            return $this->inertiaResponse($this->componentPath().'/Create', [
                'page' => Page::make('Create '.$this->entityName())->compile(),
                'error' => 'Error loading form: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Store a newly created resource
     */
    public function store(FormRequest $request): JsonResponse
    {
        try {
            $item = $this->repository()->createOne($request->validated());
            $resourceClass = $this->resourceClass();

            return $this->successResponse($this->entityName().' created successfully', [
                'data' => new $resourceClass($item),
                'redirect' => $this->getRedirectUrl('store', $item),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error creating '.$this->entityName().': '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource
     */
    public function show(Model $model): Response
    {
        try {
            $data = $this->repository()->findOne($model->id);
            $page = $this->buildShowPage($data);
            $resourceClass = $this->resourceClass();

            return $this->inertiaResponse($this->componentPath().'/Show', [
                'page' => $page->compile(),
                'data' => new $resourceClass($data),
            ]);
        } catch (\Exception $e) {
            return $this->inertiaResponse($this->componentPath().'/Show', [
                'page' => Page::make($this->entityName())->compile(),
                'error' => $this->entityName().' not found',
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit(Model $model): Response
    {
        try {
            $data = $this->repository()->findOne($model->id);
            $page = $this->buildEditPage($data);
            $resourceClass = $this->resourceClass();

            return $this->inertiaResponse($this->componentPath().'/Edit', [
                'page' => $page->compile(),
                'data' => new $resourceClass($data),
            ]);
        } catch (\Exception $e) {
            return $this->inertiaResponse($this->componentPath().'/Edit', [
                'page' => Page::make('Edit '.$this->entityName())->compile(),
                'error' => 'Error loading '.$this->entityName().': '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource
     */
    public function update(FormRequest $request, Model $model): JsonResponse
    {
        try {
            $updated = $this->repository()->updateOne($model->id, $request->validated());
            $resourceClass = $this->resourceClass();

            return $this->successResponse($this->entityName().' updated successfully', [
                'data' => new $resourceClass($updated),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error updating '.$this->entityName().': '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource
     */
    public function destroy(Model $model): JsonResponse
    {
        try {
            if (! $this->repository()->canDelete($model->id)) {
                return $this->errorResponse('Cannot delete '.$this->entityName().': has active relations', [], 422);
            }

            $this->repository()->deleteOne($model->id);

            return $this->successResponse($this->entityName().' deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error deleting '.$this->entityName().': '.$e->getMessage());
        }
    }

    /**
     * Define allowed filters for index method
     * Override in controller to customize
     */
    protected function allowedFilters(): array
    {
        return ['search', 'status'];
    }

    /**
     * Build page metadata for index view
     * Override in controller to customize
     */
    protected function buildIndexPage(): Page
    {
        return Page::make($this->entityName());
    }

    /**
     * Build page metadata for show view
     * Override in controller to customize
     */
    protected function buildShowPage($model): Page
    {
        return Page::make($model->name ?? ('View '.$this->entityName()));
    }

    /**
     * Build page metadata for create view
     * Override in controller to customize
     */
    protected function buildCreatePage(): Page
    {
        return Page::make('Create '.$this->entityName());
    }

    /**
     * Build page metadata for edit view
     * Override in controller to customize
     */
    protected function buildEditPage($model): Page
    {
        return Page::make('Edit '.($model->name ?? $this->entityName()));
    }

    /**
     * Get redirect URL after store/update actions
     * Override in controller to customize
     */
    protected function getRedirectUrl(string $action, $model): string
    {
        $routePrefix = 'cartino.'.strtolower($this->componentPath());

        return match ($action) {
            'store' => route($routePrefix.'.edit', $model),
            'update' => route($routePrefix.'.edit', $model),
            default => route($routePrefix.'.index'),
        };
    }
}
