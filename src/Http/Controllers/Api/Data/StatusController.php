<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api\Data;

use Cartino\Enums\OrderStatus;
use Cartino\Enums\Status;
use Cartino\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    /**
     * Get all available statuses for different resources
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'product' => $this->formatStatuses([
                Status::Active,
                Status::Draft,
                Status::Archived,
            ]),
            'customer' => $this->formatStatuses([
                Status::Active,
                Status::Inactive,
            ]),
            'order' => $this->formatStatuses(OrderStatus::cases()),
            'general' => $this->formatStatuses(Status::cases()),
        ]);
    }

    /**
     * Get statuses for a specific resource type
     */
    public function show(string $type): JsonResponse
    {
        $statuses = match ($type) {
            'product' => [
                Status::Active,
                Status::Draft,
                Status::Archived,
            ],
            'customer' => [
                Status::Active,
                Status::Inactive,
            ],
            'order' => OrderStatus::cases(),
            'general' => Status::cases(),
            default => [],
        };

        if (empty($statuses)) {
            return response()->json([
                'message' => "Status type '{$type}' not found",
            ], 404);
        }

        return response()->json([
            'type' => $type,
            'statuses' => $this->formatStatuses($statuses),
        ]);
    }

    /**
     * Format status cases into a consistent structure
     */
    protected function formatStatuses(array $cases): array
    {
        return array_map(function ($status) {
            return [
                'value' => $status->value,
                'label' => method_exists($status, 'label') ? $status->label() : $status->name,
                'color' => method_exists($status, 'color') ? $status->color() : 'gray',
            ];
        }, $cases);
    }
}
