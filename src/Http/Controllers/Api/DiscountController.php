<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\DiscountRequest;
use Cartino\Models\Discount;
use Cartino\Models\DiscountApplication;
use Cartino\Services\DiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends ApiController
{
    public function __construct(
        private readonly DiscountService $discountService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Discount::with(['applications'])->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search by name or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $discounts = $query->paginate($perPage);

        // Add statistics to each discount
        $discounts
            ->getCollection()
            ->transform(function ($discount) {
                $discount->statistics = $this->discountService->getDiscountStatistics($discount);

                return $discount;
            });

        return response()->json($discounts);
    }

    public function store(DiscountRequest $request): JsonResponse
    {
        $discount = $this->discountService->createDiscount($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('discount.messages.created_successfully'),
            'data' => $discount->load('applications'),
        ], 201);
    }

    public function show(Discount $discount): JsonResponse
    {
        $discount->load(['applications.applicable']);
        $discount->statistics = $this->discountService->getDiscountStatistics($discount);

        return response()->json([
            'success' => true,
            'data' => $discount,
        ]);
    }

    public function update(DiscountRequest $request, Discount $discount): JsonResponse
    {
        $discount = $this->discountService->updateDiscount($discount, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('discount.messages.updated_successfully'),
            'data' => $discount->load('applications'),
        ]);
    }

    public function destroy(Discount $discount): JsonResponse
    {
        $deleted = $this->discountService->deleteDiscount($discount);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => __('discount.messages.deleted_successfully'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('discount.messages.delete_failed'),
        ], 422);
    }

    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'customer_id' => 'nullable|integer|exists:customers,id',
        ]);

        $validation = $this->discountService->validateDiscountCode($request->code, $request->customer_id);

        return response()->json($validation);
    }

    public function toggle(Discount $discount): JsonResponse
    {
        $status = $discount->is_enabled ? 'enabled' : 'disabled';

        return response()->json([
            'success' => true,
            'message' => __("discount.messages.{$status}_successfully"),
            'data' => $discount,
        ]);
    }

    public function duplicate(Discount $discount): JsonResponse
    {
        $data = $discount->toArray();

        // Remove unique fields and modify name
        unset($data['id'], $data['code'], $data['created_at'], $data['updated_at']);
        $data['name'] = $data['name'].' (Copy)';
        $data['usage_count'] = 0;

        $newDiscount = $this->discountService->createDiscount($data);

        return response()->json([
            'success' => true,
            'message' => __('discount.messages.duplicated_successfully'),
            'data' => $newDiscount,
        ], 201);
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'total_discounts' => Discount::count(),
            'active_discounts' => Discount::where('is_enabled', true)->count(),
            'total_applications' => DiscountApplication::count(),
            'total_discount_amount' => DiscountApplication::sum('discount_amount'),
            'by_type' => Discount::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type'),
            'recent_activity' => DiscountApplication::with(['discount', 'applicable'])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
