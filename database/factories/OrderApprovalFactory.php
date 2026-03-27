<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Company;
use Cartino\Models\Order;
use Cartino\Models\OrderApproval;
use Cartino\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderApprovalFactory extends Factory
{
    protected $model = OrderApproval::class;

    public function definition(): array
    {
        $orderTotal = $this->faker->randomFloat(2, 100, 50000);

        return [
            'order_id' => fn () => Order::factory(),
            'company_id' => fn () => Company::factory(),
            'requested_by_id' => fn () => User::factory(),
            'approver_id' => null,
            'status' => 'pending',
            'order_total' => $orderTotal,
            'approval_threshold' => $this->faker->randomFloat(2, 1000, 10000),
            'threshold_exceeded' => $this->faker->boolean(70),
            'approval_reason' => null,
            'rejection_reason' => null,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'approved_at' => null,
            'rejected_at' => null,
            'expires_at' => now()->addDays(7),
            'data' => [],
        ];
    }

    /**
     * State: Pending approval
     */
    public function pending(): self
    {
        return $this->state([
            'status' => 'pending',
            'approver_id' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * State: Approved
     */
    public function approved(): self
    {
        return $this->state(function () {
            return [
                'status' => 'approved',
                'approver_id' => fn () => User::factory(),
                'approval_reason' => $this->faker->sentence(),
                'approved_at' => $this->faker->dateTimeBetween('-7 days'),
                'rejected_at' => null,
            ];
        });
    }

    /**
     * State: Rejected
     */
    public function rejected(): self
    {
        return $this->state(function () {
            return [
                'status' => 'rejected',
                'approver_id' => fn () => User::factory(),
                'rejection_reason' => $this->faker->sentence(),
                'approved_at' => null,
                'rejected_at' => $this->faker->dateTimeBetween('-7 days'),
            ];
        });
    }

    /**
     * State: Expired
     */
    public function expired(): self
    {
        return $this->state([
            'status' => 'pending',
            'expires_at' => $this->faker->dateTimeBetween('-7 days', '-1 day'),
        ]);
    }

    /**
     * State: High value order
     */
    public function highValue(): self
    {
        return $this->state([
            'order_total' => $this->faker->randomFloat(2, 25000, 100000),
            'approval_threshold' => 10000,
            'threshold_exceeded' => true,
        ]);
    }
}
