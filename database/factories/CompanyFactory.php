<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        $handle = Str::slug($name).'-'.Str::lower(Str::random(4));

        return [
            'company_number' => null, // Will be auto-generated
            'name' => $name,
            'handle' => $handle,
            'legal_name' => $name.' '.$this->faker->companySuffix(),
            'vat_number' => strtoupper($this->faker->countryCode()).$this->faker->numerify('########'),
            'tax_id' => $this->faker->numerify('##-#######'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),
            'parent_company_id' => null,
            'type' => $this->faker->randomElement(['standard', 'enterprise', 'wholesale', 'reseller']),
            'status' => $this->faker->randomElement(['active', 'suspended']),
            'credit_limit' => $this->faker->randomFloat(2, 5000, 100000),
            'outstanding_balance' => $this->faker->randomFloat(2, 0, 10000),
            'payment_terms_days' => $this->faker->randomElement([30, 60, 90]),
            'payment_method' => $this->faker->randomElement(['invoice', 'card', 'wire']),
            'approval_threshold' => $this->faker->randomFloat(2, 1000, 10000),
            'requires_approval' => $this->faker->boolean(60),
            'risk_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'lifetime_value' => $this->faker->randomFloat(2, 10000, 500000),
            'order_count' => $this->faker->numberBetween(0, 100),
            'last_order_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-6 months') : null,
            'tax_exempt' => $this->faker->boolean(20),
            'tax_exemptions' => $this->faker->boolean(20) ? [
                'reason' => $this->faker->randomElement(['nonprofit', 'government', 'resale']),
                'certificate_number' => $this->faker->numerify('CERT-#####'),
                'expires_at' => $this->faker->dateTimeBetween('+1 year', '+3 years')->format('Y-m-d'),
            ] : null,
            'billing_address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'zip' => $this->faker->postcode(),
                'country' => $this->faker->countryCode(),
            ],
            'shipping_address' => $this->faker->boolean(80) ? [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'zip' => $this->faker->postcode(),
                'country' => $this->faker->countryCode(),
            ] : null,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'settings' => [],
            'data' => [],
        ];
    }

    /**
     * State: Active company
     */
    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }

    /**
     * State: Suspended company
     */
    public function suspended(): self
    {
        return $this->state(['status' => 'suspended']);
    }

    /**
     * State: Enterprise type
     */
    public function enterprise(): self
    {
        return $this->state([
            'type' => 'enterprise',
            'credit_limit' => $this->faker->randomFloat(2, 50000, 500000),
            'approval_threshold' => $this->faker->randomFloat(2, 5000, 25000),
            'requires_approval' => true,
        ]);
    }

    /**
     * State: With subsidiary companies
     */
    public function withSubsidiaries(int $count = 2): self
    {
        return $this->afterCreating(function (Company $company) use ($count) {
            Company::factory()
                ->count($count)
                ->create(['parent_company_id' => $company->id]);
        });
    }

    /**
     * State: High risk
     */
    public function highRisk(): self
    {
        return $this->state([
            'risk_level' => 'high',
            'credit_limit' => $this->faker->randomFloat(2, 5000, 20000),
            'requires_approval' => true,
        ]);
    }

    /**
     * State: Tax exempt
     */
    public function taxExempt(): self
    {
        return $this->state([
            'tax_exempt' => true,
            'tax_exemptions' => [
                'reason' => 'nonprofit',
                'certificate_number' => 'CERT-'.strtoupper(Str::random(8)),
                'expires_at' => now()->addYears(2)->format('Y-m-d'),
            ],
        ]);
    }
}
