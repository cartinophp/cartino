<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Currency;
use Cartino\Models\Customer;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Cartino\Models\Site;
use Cartino\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $billingInterval = $this->faker->randomElement(['day', 'week', 'month', 'year']);
        $billingIntervalCount = match ($billingInterval) {
            'day' => $this->faker->randomElement([1, 7, 14]),
            'week' => $this->faker->randomElement([1, 2, 4]),
            'month' => $this->faker->randomElement([1, 3, 6, 12]),
            'year' => 1,
        };

        $startedAt = $this->faker->dateTimeBetween('-6 months', '-1 day');
        $currentPeriodStart = $this->faker->dateTimeBetween($startedAt, 'now');

        $currentPeriodEnd = match ($billingInterval) {
            'day' => (clone $currentPeriodStart)->modify("+{$billingIntervalCount} days"),
            'week' => (clone $currentPeriodStart)->modify("+{$billingIntervalCount} weeks"),
            'month' => (clone $currentPeriodStart)->modify("+{$billingIntervalCount} months"),
            'year' => (clone $currentPeriodStart)->modify("+{$billingIntervalCount} years"),
        };

        $nextBillingDate = match ($billingInterval) {
            'day' => (clone $currentPeriodEnd)->modify("+{$billingIntervalCount} days"),
            'week' => (clone $currentPeriodEnd)->modify("+{$billingIntervalCount} weeks"),
            'month' => (clone $currentPeriodEnd)->modify("+{$billingIntervalCount} months"),
            'year' => (clone $currentPeriodEnd)->modify("+{$billingIntervalCount} years"),
        };

        $price = $this->faker->randomFloat(2, 9.99, 299.99);
        $billingCycleCount = $this->faker->numberBetween(1, 12);
        $totalBilled = round($price * $billingCycleCount, 2);

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'customer_id' => Customer::query()->inRandomOrder()->value('id'),
            'product_id' => Product::query()->inRandomOrder()->value('id'),
            'product_variant_id' => ProductVariant::query()->inRandomOrder()->value('id'),
            'subscription_number' => 'SUB-'.$this->faker->unique()->numerify('######'),
            'status' => 'active',
            'billing_interval' => $billingInterval,
            'billing_interval_count' => $billingIntervalCount,
            'price' => $price,
            'currency_id' => Currency::query()->inRandomOrder()->value('id'),
            'started_at' => $startedAt,
            'trial_end_at' => null,
            'current_period_start' => $currentPeriodStart,
            'current_period_end' => $currentPeriodEnd,
            'next_billing_date' => $nextBillingDate,
            'paused_at' => null,
            'cancelled_at' => null,
            'ended_at' => null,
            'payment_method' => $this->faker->randomElement(['card', 'paypal', 'bank_transfer']),
            'payment_details' => ['method' => 'card', 'last4' => $this->faker->numerify('####')],
            'billing_cycle_count' => $billingCycleCount,
            'total_billed' => $totalBilled,
            'cancel_reason' => null,
            'cancel_comment' => null,
            'cancel_at_period_end' => false,
            'pause_reason' => null,
            'pause_resumes_at' => null,
            'metadata' => null,
            'notes' => null,
            'data' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active']);
    }

    public function paused(): static
    {
        return $this->state(fn () => [
            'status' => 'paused',
            'paused_at' => now()->subDays(rand(1, 30)),
            'pause_reason' => $this->faker->randomElement(['customer_request', 'payment_failed', 'other']),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'cancelled_at' => now()->subDays(rand(1, 60)),
            'ended_at' => now()->subDays(rand(1, 60)),
            'cancel_reason' => $this->faker->randomElement([
                'customer_request',
                'payment_failed',
                'too_expensive',
                'not_using',
                'other',
            ]),
        ]);
    }

    public function withTrial(int $days = 14): static
    {
        return $this->state(fn () => [
            'trial_end_at' => now()->addDays($days),
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn () => [
            'billing_interval' => 'month',
            'billing_interval_count' => 1,
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn () => [
            'billing_interval' => 'year',
            'billing_interval_count' => 1,
        ]);
    }
}
