<?php

declare(strict_types=1);

namespace Cartino\Http\Resources\OrderApproval;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderApprovalCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = OrderApprovalResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'pending_count' => $this->collection->where('status', 'pending')->count(),
                'approved_count' => $this->collection->where('status', 'approved')->count(),
                'rejected_count' => $this->collection->where('status', 'rejected')->count(),
                'expired_count' => $this->collection->filter(fn ($item) => $item->status === 'pending' &&
                    $item->expires_at &&
                    now()->isAfter($item->expires_at)
                )->count(),
                'total_amount_pending' => $this->collection
                    ->where('status', 'pending')
                    ->sum('order_total'),
                'average_approval_time_hours' => $this->getAverageApprovalTime(),
            ],
        ];
    }

    /**
     * Get average approval time in hours
     */
    protected function getAverageApprovalTime(): ?float
    {
        $approvedItems = $this->collection->filter(fn ($item) => $item->status === 'approved' && $item->approved_at
        );

        if ($approvedItems->isEmpty()) {
            return null;
        }

        $totalHours = $approvedItems->sum(fn ($item) => $item->created_at->diffInHours($item->approved_at)
        );

        return round($totalHours / $approvedItems->count(), 2);
    }
}
