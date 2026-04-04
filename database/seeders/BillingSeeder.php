<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BillingSeeder extends Seeder
{
    protected Generator $faker;

    public function run(): void
    {
        $this->faker = app(Generator::class);

        $leases = Lease::all();
        $today  = Carbon::now();

        foreach ($leases as $lease) {
            // ── Move-In Billing (one-time, always Paid since tenant already moved in) ──
            $moveInBilling = Billing::factory()->create([
                'lease_id'     => $lease->lease_id,
                'billing_type' => 'move_in',
                'billing_date' => Carbon::parse($lease->move_in)->format('Y-m-d'),
                'next_billing' => Carbon::parse($lease->move_in)->addMonth()->format('Y-m-d'),
                'due_date'     => Carbon::parse($lease->move_in)->format('Y-m-d'),
                'to_pay'       => $lease->contract_rate * 2, // advance + deposit
                'amount'       => $lease->contract_rate * 2,
                'status'       => 'Paid',
            ]);

            BillingItem::create([
                'billing_id'      => $moveInBilling->billing_id,
                'charge_category' => 'move_in',
                'charge_type'     => 'advance',
                'description'     => '1 Month Advance — First Month Rent',
                'amount'          => $lease->contract_rate,
            ]);

            BillingItem::create([
                'billing_id'      => $moveInBilling->billing_id,
                'charge_category' => 'move_in',
                'charge_type'     => 'security_deposit',
                'description'     => '1 Month Security Deposit',
                'amount'          => $lease->contract_rate,
            ]);

            // ── Monthly Billings ──
            $billingDate   = Carbon::parse($lease->move_in)->startOfMonth();
            $contractPrice = $lease->contract_rate;
            $lastPastMonth = $today->copy()->startOfMonth()->subMonth();
            $leaseTerm     = $lease->term;

            while ($billingDate->lte($today->copy()->startOfMonth())) {
                $nextBilling = $billingDate->copy()->addMonth();
                $dueDate     = $billingDate->copy()->addDays(5);
                $isPast      = $billingDate->lt($today->copy()->startOfMonth());
                $isLastPast  = $billingDate->eq($lastPastMonth);

                if ($isPast) {
                    $status = $isLastPast
                        ? $this->faker->randomElement(['Overdue', 'Paid'])
                        : 'Paid';
                } else {
                    $status = $this->faker->randomElement(['Paid', 'Unpaid']);
                }

                $billing = Billing::factory()->create([
                    'lease_id'     => $lease->lease_id,
                    'billing_type' => 'monthly',
                    'billing_date' => $billingDate->format('Y-m-d'),
                    'next_billing' => $nextBilling->format('Y-m-d'),
                    'due_date'     => $dueDate->format('Y-m-d'),
                    'to_pay'       => $contractPrice, // will be recalculated below
                    'amount'       => $contractPrice,
                    'status'       => $status,
                ]);

                // ── Create Billing Items ──
                $totalCharges = 0;

                // A. Recurring: Monthly Rent (always)
                BillingItem::create([
                    'billing_id'      => $billing->billing_id,
                    'charge_category' => 'recurring',
                    'charge_type'     => 'rent',
                    'description'     => 'Monthly Rent',
                    'amount'          => $contractPrice,
                ]);
                $totalCharges += $contractPrice;

                // NOTE: Utility shares (electricity_share, water_share) are created
                // by UtilityBillSeeder which also creates the matching UtilityBill records.
                // This keeps seeded data consistent with the production flow.

                // B. Conditional: Short-Term Premium (if lease term < 6 months)
                if ($leaseTerm < 6) {
                    BillingItem::create([
                        'billing_id'      => $billing->billing_id,
                        'charge_category' => 'conditional',
                        'charge_type'     => 'short_term_premium',
                        'description'     => 'Short-Term Premium (contract under 6 months)',
                        'amount'          => 500.00,
                    ]);
                    $totalCharges += 500.00;
                }

                // B. Conditional: Late Payment Fee (~10% chance, only for past months)
                // Penalty = (late_payment_penalty% of contract_rate) × days late
                if ($isPast && $this->faker->boolean(10)) {
                    $penaltyRate = $lease->late_payment_penalty ?? 1; // percentage
                    $daysLate = $this->faker->numberBetween(1, 10);
                    $dailyPenalty = round(($penaltyRate / 100) * $lease->contract_rate, 2);
                    $lateFee = $dailyPenalty * $daysLate;
                    BillingItem::create([
                        'billing_id'      => $billing->billing_id,
                        'charge_category' => 'conditional',
                        'charge_type'     => 'late_fee',
                        'description'     => "Late Payment Fee ({$daysLate} day(s) × ₱" . number_format($dailyPenalty, 2) . "/day)",
                        'amount'          => $lateFee,
                    ]);
                    $totalCharges += $lateFee;
                }

                // Update billing totals
                $billing->update([
                    'to_pay' => $totalCharges,
                    'amount' => $totalCharges,
                ]);

                $billingDate->addMonth();
            }
        }
    }

    private function resolveStatus(Carbon $billingDate): string
    {
        $now = Carbon::now();

        if ($billingDate->lt($now->copy()->subMonths(2)->startOfMonth())) {
            return $this->faker->randomElement(['Paid', 'Paid', 'Paid', 'Paid', 'Paid', 'Paid', 'Paid', 'Paid', 'Paid', 'Overdue']);
        }

        if ($billingDate->lt($now->startOfMonth())) {
            return $this->faker->randomElement(['Paid', 'Paid', 'Paid', 'Paid', 'Paid', 'Paid', 'Overdue', 'Unpaid']);
        }

        return $this->faker->randomElement(['Paid', 'Paid', 'Unpaid', 'Overdue']);
    }
}
