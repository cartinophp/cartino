<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->transformData($request);
    }

    /**
     * Transform the resource data.
     */
    abstract protected function transformData(Request $request): array;

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => $this->getMeta($request),
        ];
    }

    /**
     * Get meta information for the resource.
     */
    protected function getMeta(Request $request): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'version' => '1.0',
        ];
    }

    /**
     * Include relationship only when requested.
     */
    protected function whenIncluded(string $include, $callback)
    {
        $includes = explode(',', request()->query('include', ''));

        if (in_array($include, $includes)) {
            return $callback();
        }

        return $this->missingValue();
    }

    /**
     * Format money values.
     */
    protected function formatMoney(?int $cents, string $currency = 'USD'): ?array
    {
        if ($cents === null) {
            return null;
        }

        return [
            'amount' => $cents / 100,
            'formatted' => number_format($cents / 100, 2),
            'currency' => $currency,
            'cents' => $cents,
        ];
    }

    /**
     * Format timestamps.
     */
    protected function formatTimestamp($timestamp): ?array
    {
        if (! $timestamp) {
            return null;
        }

        return [
            'datetime' => $timestamp->toISOString(),
            'human' => $timestamp->diffForHumans(),
            'formatted' => $timestamp->format('M j, Y g:i A'),
        ];
    }
}
