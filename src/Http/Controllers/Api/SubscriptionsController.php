<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Controllers\Controller;
use Cartino\Http\Requests\Api\StoreSubscriptionRequest;
use Cartino\Http\Requests\Api\UpdateSubscriptionRequest;
use Cartino\Http\Resources\SubscriptionResource;
use Cartino\Models\Subscription;
use Cartino\Repositories\SubscriptionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionsController extends Controller
{
    public function __construct(
        protected SubscriptionRepository $repository,
    ) {}

    /**
     * Display a listing of subscriptions
     */
    final public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $subscriptions = $this->repository->findAll($request->all(), $perPage);

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Display the specified subscription
     */
    final public function show(Subscription $subscription): SubscriptionResource
    {
        $subscription = $this->repository->findOne($subscription->id);

        return new SubscriptionResource($subscription);
    }

    /**
     * Store a newly created subscription
     */
    final public function store(StoreSubscriptionRequest $request): SubscriptionResource
    {
        $data = $request->validated();

        // Generate subscription number
        $data['subscription_number'] = 'SUB-'.strtoupper(uniqid());
        $data['status'] = 'active';
        $data['started_at'] = now();

        // Set trial period if provided
        if (isset($data['trial_end_at'])) {
            $data['current_period_start'] = now();
            $data['current_period_end'] = $data['trial_end_at'];
        } else {
            $data['current_period_start'] = now();
            // Calculate period end based on billing interval
            $data['current_period_end'] = match ($data['billing_interval']) {
                'day' => now()->addDays($data['billing_interval_count']),
                'week' => now()->addWeeks($data['billing_interval_count']),
                'month' => now()->addMonths($data['billing_interval_count']),
                'year' => now()->addYears($data['billing_interval_count']),
            };
        }

        // Calculate next billing date
        $data['next_billing_date'] = match ($data['billing_interval']) {
            'day' => $data['current_period_end']->copy()->addDays($data['billing_interval_count']),
            'week' => $data['current_period_end']->copy()->addWeeks($data['billing_interval_count']),
            'month' => $data['current_period_end']->copy()->addMonths($data['billing_interval_count']),
            'year' => $data['current_period_end']->copy()->addYears($data['billing_interval_count']),
        };

        $subscription = $this->repository->createOne($data);

        return new SubscriptionResource($subscription);
    }

    /**
     * Update the specified subscription
     */
    final public function update(UpdateSubscriptionRequest $request, Subscription $subscription): SubscriptionResource
    {
        $data = $request->validated();

        $subscription = $this->repository->updateOne($subscription->id, $data);

        return new SubscriptionResource($subscription);
    }

    /**
     * Remove the specified subscription
     */
    final public function destroy(Subscription $subscription): JsonResponse
    {
        if (! $this->repository->canDelete($subscription->id)) {
            return response()->json([
                'message' => 'Cannot delete an active subscription. Please cancel it first.',
            ], 422);
        }

        $this->repository->deleteOne($subscription->id);

        return response()->json(null, 204);
    }

    /**
     * Pause a subscription
     */
    final public function pause(Request $request, Subscription $subscription): SubscriptionResource
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'resumes_at' => ['nullable', 'date', 'after:now'],
        ]);

        $resumesAt = $request->input('resumes_at') ? new \DateTime($request->input('resumes_at')) : null;

        $subscription = $this->repository->pause($subscription->id, $request->input('reason'), $resumesAt);

        return new SubscriptionResource($subscription);
    }

    /**
     * Resume a paused subscription
     */
    final public function resume(Subscription $subscription): SubscriptionResource
    {
        $subscription = $this->repository->resume($subscription->id);

        return new SubscriptionResource($subscription);
    }

    /**
     * Cancel a subscription
     */
    final public function cancel(Request $request, Subscription $subscription): SubscriptionResource
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'immediately' => ['nullable', 'boolean'],
        ]);

        $subscription = $this->repository->cancel(
            $subscription->id,
            $request->input('reason'),
            $request->input('comment'),
            $request->boolean('immediately', false),
        );

        return new SubscriptionResource($subscription);
    }

    /**
     * Get all active subscriptions
     */
    final public function active(): AnonymousResourceCollection
    {
        $subscriptions = $this->repository->getActive();

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Get subscriptions due for billing
     */
    final public function dueForBilling(): AnonymousResourceCollection
    {
        $subscriptions = $this->repository->getDueForBilling();

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Get orders for a subscription
     */
    final public function orders(Subscription $subscription): JsonResponse
    {
        $orders = $this->repository->getSubscriptionOrders($subscription->id);

        return response()->json([
            'data' => $orders,
        ]);
    }
}
