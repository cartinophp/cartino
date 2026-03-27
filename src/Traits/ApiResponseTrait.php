<?php

namespace Cartino\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    /**
     * Return a paginated API response
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, ?string $message = null): JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ] + ($message ? ['message' => $message] : []));
    }

    /**
     * Return a success response
     */
    protected function successResponse(
        $data = null,
        string $message = 'Operazione completata con successo',
        int $status = 200,
    ): JsonResponse {
        $response = ['success' => true, 'message' => $message];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(
        string $message = 'Si è verificato un errore',
        int $status = 500,
        $errors = null,
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a not found response
     */
    protected function notFoundResponse(string $message = 'Risorsa non trovata'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse(string $message = 'Dati non validi', $errors = null): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return a created response
     */
    protected function createdResponse($data = null, string $message = 'Risorsa creata con successo'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return a bulk action response
     */
    protected function bulkActionResponse(string $action, int $count, array $errors = []): JsonResponse
    {
        $message = "Azione '{$action}' eseguita su {$count} elementi";

        $data = [
            'count' => $count,
            'action' => $action,
        ];

        if (! empty($errors)) {
            $data['errors'] = $errors;
            $message .= ' con alcuni errori';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ], 207); // 207 Multi-Status
        }

        return $this->successResponse($data, $message);
    }
}
