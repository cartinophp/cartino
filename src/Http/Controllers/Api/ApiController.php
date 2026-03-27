<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Controllers\Controller;
use Cartino\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Return a successful response with data
     */
    protected function success($data = null, string $message = '', int $status = 200): JsonResponse
    {
        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a successful response for created resources
     */
    protected function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a successful response for updated resources
     */
    protected function updated($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return $this->success($data, $message, 200);
    }

    /**
     * Return a successful response for deleted resources
     */
    protected function deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->success(null, $message, 200);
    }

    /**
     * Return an error response
     */
    protected function error(string $message = 'An error occurred', int $status = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a not found response
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return a validation error response
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Return a resource collection using Laravel's default structure
     */
    protected function collection(AnonymousResourceCollection $collection): AnonymousResourceCollection
    {
        return $collection;
    }

    /**
     * Return a single resource using Laravel's default structure
     */
    protected function resource(JsonResource $resource): JsonResource
    {
        return $resource;
    }
}
