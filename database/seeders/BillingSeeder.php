<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\UtilityBill;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BillingSeeder extends Seeder
{
    protected Generator $faker;

    // Cache utility bills to avoid repeated DB queries
    private array $utilityCache = [];

    public function run(): void
    {
        $this->faker = app(Generator::class);

        // Pre-load all utility bills keyed by unit_id + billing_period + utility_type
        UtilityBill::all()->each(function ($bill) {
            $key = "{$bill->unit_id}_{$bill->billing_period}_{$bill->utility_type}";
            $this->utilityCache[$key] = $bill->per_tenant_amount;
        });

        $leases = Lease::orderBy('start_date')->get();
        $firstLeasePerTenant = [];

        foreach ($leases as $lease) {
            $tenantId     = $lease->tenant_id;
            $isFirstLease = !isset($firstLeasePerTenant[$tenantId]);

            if ($isFirstLease) {
                $firstLeasePerTenant[$tenantId] = $lease->lease_id;
            }

            // ── Move-In Billing (only for tenant's very first lease) ──
            if ($isFirstLease) {
                $moveInBilling = Billing::factory()->create([
                    'lease_id'     => $lease->lease_id,
                    'billing_type' => 'move_in',
                    'billing_date' => Carbon::parse($lease->move_in)->format('Y-m-d'),
                    'next_billing' => Carbon::parse($lease->move_in)->addMonth()->format('Y-m-d'),
                    'due_date'     => Carbon::parse($lease->move_in)->format('Y-m-d'),
                    'to_pay'       => $lease->contract_rate * 2,
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
            }

            // ── Monthly Billings ──
            $this->createMonthlyBillings($lease);
        }
    }

    private function createMonthlyBillings(Lease $lease): void
    {
        $today         = Carbon::now();
        $contractPrice = $lease->contract_rate;
        $leaseTerm     = $lease->term;

        // Resolve the unit_id through bed → unit
        $unitId = $lease->bed->unit_id;

        $billingDate = Carbon::parse($lease->start_date)->startOfMonth()->addMonth();
        $leaseEnd    = Carbon::parse($lease->end_date)->startOfMonth();
        $ceiling     = $leaseEnd->lt($today->copy()->startOfMonth())
            ? $leaseEnd
            : $today->copy()->startOfMonth();

        while ($billingDate->lte($ceiling)) {
            $nextBilling = $billingDate->copy()->addMonth();
            $dueDate     = $billingDate->copy()->addDays(5);
            $isPast      = $billingDate->lt($today->copy()->startOfMonth());
            $isLastPast  = $billingDate->eq($today->copy()->startOfMonth()->subMonth());

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
                'to_pay'       => $contractPrice,
                'amount'       => $contractPrice,
                'status'       => $status,
            ]);

            $totalCharges = 0;
            $period       = $billingDate->format('Y-m-d');

            // Recurring: Monthly Rent (always)
            BillingItem::create([
                'billing_id'      => $billing->billing_id,
                'charge_category' => 'recurring',
                'charge_type'     => 'rent',
                'description'     => 'Monthly Rent',
                'amount'          => $contractPrice,
            ]);
            $totalCharges += $contractPrice;

            // Recurring: Electricity Share (from utility bill, fallback to random)
            $electricityShare = $this->utilityCache["{$unitId}_{$period}_electricity"]
                ?? $this->faker->randomFloat(2, 300, 600);

            BillingItem::create([
                'billing_id'      => $billing->billing_id,
                'charge_category' => 'recurring',
                'charge_type'     => 'electricity_share',
                'description'     => 'Electricity Share (Meralco split)',
                'amount'          => $electricityShare,
            ]);
            $totalCharges += $electricityShare;

            // Recurring: Water Share (from utility bill, fallback to random)
            $waterShare = $this->utilityCache["{$unitId}_{$period}_water"]
                ?? $this->faker->randomFloat(2, 50, 150);

            BillingItem::create([
                'billing_id'      => $billing->billing_id,
                'charge_category' => 'recurring',
                'charge_type'     => 'water_share',
                'description'     => 'Water Share (split)',
                'amount'          => $waterShare,
            ]);
            $totalCharges += $waterShare;

            // Conditional: Short-Term Premium
            if ($leaseTerm < 6) {
                BillingItem::create([
                    'billing_id'      => $billing->billing_id,
                    'charge_category' => 'conditional',
                    'charge_type'     => 'short_term_premium',
                    'description'     => 'Short-Term Premium (contract under 6 months)',
                    'amount'          => 500.00,
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

            // Conditional: Late Payment Fee (~10% chance, past months only)
            if ($isPast && $this->faker->boolean(10)) {
                $lateFee = $this->faker->randomElement([100, 200, 300]);
                BillingItem::create([
                    'billing_id'      => $billing->billing_id,
                    'charge_category' => 'conditional',
                    'charge_type'     => 'late_fee',
                    'description'     => 'Late Payment Fee',
                    'amount'          => $lateFee,
                ]);
                $totalCharges += $lateFee;
            }

            $billing->update([
                'to_pay' => $totalCharges,
                'amount' => $totalCharges,
            ]);

            $billingDate->addMonth();
        }
    }
}
