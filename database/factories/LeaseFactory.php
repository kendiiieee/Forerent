<?php

namespace Database\Factories;

use App\Models\Lease;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaseFactory extends Factory
{
    protected $model = Lease::class;

    public function definition(): array
    {
        // Lease term (months)
        $term = $this->faker->numberBetween(3, 12);

        // Start date: sometime in the past term months so end_date is always >= today
        $startDate = $this->faker->dateTimeBetween("-{$term} months", 'now');

        // Compute end date
        $endDate = (clone $startDate)->modify("+{$term} months");

        $isShortTerm = $term < 6;

        return [
            // These will be set in LeaseSeeder
            'tenant_id'        => null,
            'bed_id'           => null,

            'status'           => 'Active',
            'term'             => $term,
            'shift'            => $this->faker->randomElement(['Night', 'Morning']),
            'auto_renew'       => $this->faker->boolean(),

            'start_date'       => $startDate->format('Y-m-d'),
            'end_date'         => $endDate->format('Y-m-d'),
            'move_in'          => $startDate->format('Y-m-d'),

            'contract_rate'    => $this->faker->randomFloat(2, 3000, 15000),
            'advance_amount'   => $this->faker->randomFloat(2, 500, 2000),
            'security_deposit' => $this->faker->randomFloat(2, 500, 5000),

            'monthly_due_date'      => $this->faker->randomElement([1, 5, 15]),
            'late_payment_penalty'  => $this->faker->randomElement([1, 1.5, 2]),
            'short_term_premium'    => $isShortTerm ? 500.00 : 0.00,
            'reservation_fee_paid'  => $this->faker->randomElement([0, 500, 1000, 1500]),
            'early_termination_fee' => $this->faker->randomElement([1000, 2000, 3000, 5000]),

            'created_at'       => now(),
            'updated_at'       => now(),
        ];
    }
}
