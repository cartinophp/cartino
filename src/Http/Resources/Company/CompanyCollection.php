<?php

declare(strict_types=1);

namespace Cartino\Http\Resources\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CompanyCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CompanyResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'active_count' => $this->collection->where('status', 'active')->count(),
                'suspended_count' => $this->collection->where('status', 'suspended')->count(),
                'high_risk_count' => $this->collection->where('risk_level', 'high')->count(),
                'total_credit_limit' => $this->collection->sum('credit_limit'),
                'total_outstanding' => $this->collection->sum('outstanding_balance'),
            ],
        ];
    }
}
