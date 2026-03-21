<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Lease;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $leases = Lease::all();
        $today  = Carbon::now();

        foreach ($leases as $lease) {
            $billingDate   = Carbon::parse($lease->move_in)->startOfMonth();
            $contractPrice = $lease->contract_rate;
            $lastPastMonth = $today->copy()->startOfMonth()->subMonth();

            while ($billingDate->lte($today->copy()->startOfMonth())) {
                $nextBilling = $billingDate->copy()->addMonth();
                $isPast      = $billingDate->lt($today->copy()->startOfMonth());
                $isLastPast  = $billingDate->eq($lastPastMonth);

                if ($isPast) {
                    // Only the most recent past month can be Overdue, older ones are always Paid
                    $status = $isLastPast
                        ? fake()->randomElement(['Overdue', 'Paid'])
                        : 'Paid';
                } else {
                    // Current month: Paid or Unpaid
                    $status = fake()->randomElement(['Paid', 'Unpaid']);
                }

                Billing::factory()->create([
                    'lease_id'     => $lease->lease_id,
                    'billing_date' => $billingDate->format('Y-m-d'),
                    'next_billing' => $nextBilling->format('Y-m-d'),
                    'to_pay'       => $contractPrice,
                    'amount'       => $contractPrice,
                    'status'       => $status,
                ]);

                $billingDate->addMonth();
            }
        }
    }
}
