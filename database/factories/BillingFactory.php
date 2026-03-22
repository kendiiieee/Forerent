<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Billing>
 */
class BillingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lease_id'         => null,
            'billing_type'     => 'monthly',
            'billing_date'     => now(),
            'next_billing'     => now()->addMonth(),
            'due_date'         => now()->addDays(5),
            'to_pay'           => 0,
            'amount'           => 0,
            'previous_balance' => 0,
            'status'           => 'Pending',
        ];
    }
}
