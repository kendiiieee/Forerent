<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\BillingItem;
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
                        ? fake()->randomElement(['Overdue', 'Paid'])
                        : 'Paid';
                } else {
                    $status = fake()->randomElement(['Paid', 'Unpaid']);
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

                // A. Recurring: Electricity Share (~70% chance)
                if (fake()->boolean(70)) {
                    $electricityShare = fake()->randomFloat(2, 300, 600);
                    BillingItem::create([
                        'billing_id'      => $billing->billing_id,
                        'charge_category' => 'recurring',
                        'charge_type'     => 'electricity_share',
                        'description'     => 'Electricity Share (Meralco split)',
                        'amount'          => $electricityShare,
                    ]);
                    $totalCharges += $electricityShare;
                }

                // A. Recurring: Water Share (~40% chance)
                if (fake()->boolean(40)) {
                    $waterShare = fake()->randomFloat(2, 50, 150);
                    BillingItem::create([
                        'billing_id'      => $billing->billing_id,
                        'charge_category' => 'recurring',
                        'charge_type'     => 'water_share',
                        'description'     => 'Water Share (split)',
                        'amount'          => $waterShare,
                    ]);
                    $totalCharges += $waterShare;
                }

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
                if ($isPast && fake()->boolean(10)) {
                    $lateFee = fake()->randomElement([100, 200, 300]);
                    BillingItem::create([
                        'billing_id'      => $billing->billing_id,
                        'charge_category' => 'conditional',
                        'charge_type'     => 'late_fee',
                        'description'     => 'Late Payment Fee',
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
}
