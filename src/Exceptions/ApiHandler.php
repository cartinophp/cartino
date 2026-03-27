<?php

declare(strict_types=1);

namespace Cartino\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ApiHandler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): JsonResponse
    {
        // Se la richiesta è per API, restituisce sempre JSON
        if ($request->is('api/*') || $request->wantsJson()) {
            return $this->renderApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Render API exception response
     */
    protected function renderApiException(Request $request, Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Errori di validazione',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Errore HTTP',
                'code' => $e->getStatusCode(),
            ], $e->getStatusCode());
        }

        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : 'Errore interno del server',
            'code' => $statusCode,
        ], $statusCode);
    }
}
