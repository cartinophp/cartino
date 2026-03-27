<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Http\Controllers\Cp\Concerns\HandlesFlashMessages;
use Cartino\Traits\HasBreadcrumbs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, HandlesFlashMessages, HasBreadcrumbs, ValidatesRequests;

    /**
     * Return success JSON response.
     */
    protected function successResponse(
        string $message = 'Operation completed successfully',
        array $data = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Return error JSON response.
     */
    protected function errorResponse(
        string $message = 'Operation failed',
        array $errors = [],
        int $status = 422,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Return Inertia response with standardized data structure.
     */
    protected function inertiaResponse(string $component, array $props = []): Response
    {
        return Inertia::render($component, array_merge([
            'breadcrumbs' => $this->getBreadcrumbs(),
        ], $props));
    }

    /**
     * Return redirect with success message.
     */
    protected function redirectWithSuccess(string $route, string $message, array $parameters = []): RedirectResponse
    {
        $this->flashSuccess($message);

        return redirect()->route($route, $parameters);
    }

    /**
     * Return redirect with error message.
     */
    protected function redirectWithError(string $route, string $message, array $parameters = []): RedirectResponse
    {
        $this->flashError($message);

        return redirect()->route($route, $parameters);
    }

    /**
     * Get paginated data in standardized format.
     */
    protected function getPaginatedData($query, int $perPage = 15): array
    {
        $paginated = $query->paginate($perPage);

        return [
            'data' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
                'path' => $paginated->path(),
                'links' => $paginated->linkCollection()->toArray(),
            ],
        ];
    }

    /**
     * Validate permissions for current user.
     */
    protected function checkPermission(string $permission): void
    {
        $this->authorize($permission);
    }

    /**
     * Get filters from request.
     */
    protected function getFilters(array $allowed = []): array
    {
        $filters = request()->only($allowed);

        return array_filter($filters, function ($value) {
            return ! is_null($value) && $value !== '';
        });
    }

    /**
     * Apply common filters to query.
     */
    protected function applyFilters($query, array $filters): mixed
    {
        foreach ($filters as $key => $value) {
            match ($key) {
                'search' => $query->where(function ($q) use ($value) {
                    $this->applySearchFilter($q, $value);
                }),
                'status' => $query->where('status', $value),
                'created_at' => $this->applyDateFilter($query, 'created_at', $value),
                'updated_at' => $this->applyDateFilter($query, 'updated_at', $value),
                default => $this->applyCustomFilter($query, $key, $value),
            };
        }

        return $query;
    }

    /**
     * Apply search filter - to be overridden by child controllers.
     */
    protected function applySearchFilter($query, string $search): void
    {
        // Default implementation - override in child controllers
    }

    /**
     * Apply date filter.
     */
    protected function applyDateFilter($query, string $field, $value): void
    {
        if (is_array($value)) {
            if (isset($value['from'])) {
                $query->whereDate($field, '>=', $value['from']);
            }
            if (isset($value['to'])) {
                $query->whereDate($field, '<=', $value['to']);
            }
        } else {
            $query->whereDate($field, $value);
        }
    }

    /**
     * Apply custom filter - to be overridden by child controllers.
     */
    protected function applyCustomFilter($query, string $key, $value): void
    {
        // Default implementation - override in child controllers
    }
}
