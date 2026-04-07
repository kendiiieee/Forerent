<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\Transaction;
use App\Models\UtilityBill;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        $leases = Lease::with('bed.unit')->orderBy('start_date')->get();
        $firstLeasePerTenant = [];

        // Disable Billing model events during seeding to avoid automatic
        // credit-transaction creation inside a huge transaction. We create
        // transactions manually in bulk afterwards (fix #5).
        Billing::withoutEvents(function () use ($leases, &$firstLeasePerTenant) {
            DB::transaction(function () use ($leases, &$firstLeasePerTenant) {
                foreach ($leases as $lease) {
                    $tenantId     = $lease->tenant_id;
                    $isFirstLease = !isset($firstLeasePerTenant[$tenantId]);

                    if ($isFirstLease) {
                        $firstLeasePerTenant[$tenantId] = $lease->lease_id;
                    }

                    // ── Fix #8: Sync lease advance_amount & security_deposit ──
                    if ($isFirstLease) {
                        $lease->update([
                            'advance_amount'   => $lease->contract_rate,
                            'security_deposit' => $lease->contract_rate,
                        ]);
                    }

                    // ── Move-In Billing (only for tenant's very first lease) ──
                    if ($isFirstLease) {
                        $moveInBilling = Billing::factory()->create([
                            'lease_id'     => $lease->lease_id,
                            'tenant_id'    => $tenantId, // Fix #1
                            'billing_type' => 'move_in',
                            'billing_date' => Carbon::parse($lease->move_in)->format('Y-m-d'),
                            'next_billing' => Carbon::parse($lease->move_in)->addMonth()->format('Y-m-d'),
                            'due_date'     => Carbon::parse($lease->move_in)->format('Y-m-d'),
                            'to_pay'       => 0, // Fix #4: move-in is always paid
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

                    // ── Fix #3: Move-Out Billing (for expired leases) ──
                    if ($lease->status === 'Expired') {
                        $this->createMoveOutBilling($lease);
                    }
                }
            });
        });

        // ── Fix #5: Bulk-create credit transactions for all Paid billings ──
        $this->createCreditTransactions();
    }

    private function createMonthlyBillings(Lease $lease): void
    {
        $today         = Carbon::now();
        $contractPrice = $lease->contract_rate;
        $leaseTerm     = $lease->term;
        $tenantId      = $lease->tenant_id;

        // Resolve the unit_id through bed → unit
        $unitId = $lease->bed->unit_id;

        $billingDate = Carbon::parse($lease->start_date)->startOfMonth()->addMonth();
        $leaseEnd    = Carbon::parse($lease->end_date)->startOfMonth();
        $ceiling     = $leaseEnd->lt($today->copy()->startOfMonth())
            ? $leaseEnd
            : $today->copy()->startOfMonth();

        // Fix #2: Track running unpaid balance per lease
        $runningUnpaidBalance = 0;

        while ($billingDate->lte($ceiling)) {
            $nextBilling = $billingDate->copy()->addMonth();
            $dueDate     = $billingDate->copy()->addDays(5);
            $isPast      = $billingDate->lt($today->copy()->startOfMonth());
            $isLastPast  = $billingDate->eq($today->copy()->startOfMonth()->subMonth());
            $isCurrent   = $billingDate->eq($today->copy()->startOfMonth());

            // Fix #6: Improved status logic
            if ($isPast) {
                $status = $isLastPast
                    ? $this->faker->randomElement(['Overdue', 'Paid'])
                    : 'Paid';
            } elseif ($isCurrent) {
                // Current month: check if due date has passed
                if ($dueDate->lt($today)) {
                    $status = $this->faker->randomElement(['Paid', 'Overdue', 'Unpaid']);
                } else {
                    $status = $this->faker->randomElement(['Paid', 'Unpaid']);
                }
            } else {
                $status = 'Unpaid';
            }

            $totalCharges = 0;
            $period       = $billingDate->format('Y-m-d');

            // Build items first, then create billing with correct totals
            $items = [];

            // Recurring: Monthly Rent (always)
            $items[] = [
                'charge_category' => 'recurring',
                'charge_type'     => 'rent',
                'description'     => 'Monthly Rent',
                'amount'          => $contractPrice,
            ];
            $totalCharges += $contractPrice;

            // Recurring: Electricity Share (from utility bill, fallback to random)
            $electricityShare = $this->utilityCache["{$unitId}_{$period}_electricity"]
                ?? $this->faker->randomFloat(2, 300, 600);

            $items[] = [
                'charge_category' => 'recurring',
                'charge_type'     => 'electricity_share',
                'description'     => 'Electricity Share (Meralco split)',
                'amount'          => $electricityShare,
            ];
            $totalCharges += $electricityShare;

            // Recurring: Water Share (from utility bill, fallback to random)
            $waterShare = $this->utilityCache["{$unitId}_{$period}_water"]
                ?? $this->faker->randomFloat(2, 50, 150);

            $items[] = [
                'charge_category' => 'recurring',
                'charge_type'     => 'water_share',
                'description'     => 'Water Share (split)',
                'amount'          => $waterShare,
            ];
            $totalCharges += $waterShare;

            // Conditional: Short-Term Premium (if lease term < 6 months)
            if ($leaseTerm < 6) {
                $items[] = [
                    'charge_category' => 'conditional',
                    'charge_type'     => 'short_term_premium',
                    'description'     => 'Short-Term Premium (contract under 6 months)',
                    'amount'          => 500.00,
                ];
                $totalCharges += 500.00;
            }

            // Conditional: Late Payment Fee (~10% chance, past months only)
            $hasLateFee = false;
            $lateFee = 0;
            if ($isPast && $this->faker->boolean(10)) {
                $penaltyRate = $lease->late_payment_penalty ?? 1;
                $daysLate = $this->faker->numberBetween(1, 10);
                $dailyPenalty = round(($penaltyRate / 100) * $lease->contract_rate, 2);
                $lateFee = $dailyPenalty * $daysLate;
                $hasLateFee = true;
                $items[] = [
                    'charge_category' => 'conditional',
                    'charge_type'     => 'late_fee',
                    'description'     => "Late Payment Fee ({$daysLate} day(s) × ₱" . number_format($dailyPenalty, 2) . "/day)",
                    'amount'          => $lateFee,
                ];
                $totalCharges += $lateFee;
            }

            // Fix #4: Differentiate to_pay vs amount based on status
            $amount = $totalCharges;
            $previousBalance = $runningUnpaidBalance;

            switch ($status) {
                case 'Paid':
                    $toPay = 0; // Fully paid
                    break;
                case 'Overdue':
                    $toPay = $totalCharges + $previousBalance; // Full amount still owed + carryover
                    break;
                case 'Unpaid':
                default:
                    $toPay = $totalCharges + $previousBalance;
                    break;
            }

            $billing = Billing::factory()->create([
                'lease_id'         => $lease->lease_id,
                'tenant_id'        => $tenantId, // Fix #1
                'billing_type'     => 'monthly',
                'billing_date'     => $billingDate->format('Y-m-d'),
                'next_billing'     => $nextBilling->format('Y-m-d'),
                'due_date'         => $dueDate->format('Y-m-d'),
                'to_pay'           => round($toPay, 2),
                'amount'           => round($amount, 2),
                'previous_balance' => round($previousBalance, 2), // Fix #2
                'status'           => $status,
            ]);

            // Create billing items
            foreach ($items as $item) {
                BillingItem::create(array_merge($item, [
                    'billing_id' => $billing->billing_id,
                ]));
            }

            // Fix #2: Update running unpaid balance for next month
            if ($status === 'Paid') {
                $runningUnpaidBalance = 0; // Paid clears all
            } else {
                $runningUnpaidBalance = $toPay; // Carry forward full unpaid amount
            }

            $billingDate->addMonth();
        }
    }

    /**
     * Fix #3: Create move-out billing for expired leases.
     * Includes deposit refund calculation, final utility charges, etc.
     */
    private function createMoveOutBilling(Lease $lease): void
    {
        $tenantId    = $lease->tenant_id;
        $moveOutDate = $lease->end_date;
        $deposit     = (float) $lease->security_deposit;

        $totalCharges = 0;
        $items = [];

        // Final utility charges for the last month
        $unitId = $lease->bed->unit_id;
        $period = Carbon::parse($moveOutDate)->startOfMonth()->format('Y-m-d');

        $electricityShare = $this->utilityCache["{$unitId}_{$period}_electricity"]
            ?? $this->faker->randomFloat(2, 300, 600);
        $items[] = [
            'charge_category' => 'move_out',
            'charge_type'     => 'electricity_share',
            'description'     => 'Final Electricity Share',
            'amount'          => $electricityShare,
        ];
        $totalCharges += $electricityShare;

        $waterShare = $this->utilityCache["{$unitId}_{$period}_water"]
            ?? $this->faker->randomFloat(2, 50, 150);
        $items[] = [
            'charge_category' => 'move_out',
            'charge_type'     => 'water_share',
            'description'     => 'Final Water Share',
            'amount'          => $waterShare,
        ];
        $totalCharges += $waterShare;

        // Possible damage deductions (20% chance)
        if ($this->faker->boolean(20)) {
            $damageCost = $this->faker->randomFloat(2, 200, 2000);
            $items[] = [
                'charge_category' => 'move_out',
                'charge_type'     => 'damage_deduction',
                'description'     => 'Damage Repair Deduction from Security Deposit',
                'amount'          => $damageCost,
            ];
            $totalCharges += $damageCost;
        }

        // Security deposit return (negative charge = credit to tenant)
        $depositReturn = max(0, $deposit - $totalCharges);
        if ($depositReturn > 0) {
            $items[] = [
                'charge_category' => 'move_out',
                'charge_type'     => 'deposit_refund',
                'description'     => 'Security Deposit Refund',
                'amount'          => -$depositReturn, // Credit
            ];
            $totalCharges -= $depositReturn;
        }

        // Net amount owed (could be 0 or positive if damages exceed deposit)
        $netAmount = max(0, $totalCharges);

        $billing = Billing::factory()->create([
            'lease_id'     => $lease->lease_id,
            'tenant_id'    => $tenantId, // Fix #1
            'billing_type' => 'move_out',
            'billing_date' => Carbon::parse($moveOutDate)->format('Y-m-d'),
            'next_billing' => Carbon::parse($moveOutDate)->format('Y-m-d'),
            'due_date'     => Carbon::parse($moveOutDate)->addDays(15)->format('Y-m-d'),
            'to_pay'       => 0, // Expired leases — settled
            'amount'       => round($netAmount, 2),
            'status'       => 'Paid',
        ]);

        foreach ($items as $item) {
            BillingItem::create(array_merge($item, [
                'billing_id' => $billing->billing_id,
            ]));
        }
    }

    /**
     * Fix #5: Create credit transactions for all Paid billings.
     * Done in bulk after all billings are created (outside the main transaction)
     * to avoid event/afterCommit issues.
     */
    private function createCreditTransactions(): void
    {
        $paidBillings = Billing::where('status', 'Paid')->get();

        foreach ($paidBillings as $billing) {
            // Skip if a credit transaction already exists
            $exists = $billing->transactions()
                ->where('transaction_type', 'Credit')
                ->where('category', 'Rent Payment')
                ->exists();

            if ($exists) {
                continue;
            }

            $amount = (float) ($billing->amount ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $transactionDate = optional($billing->billing_date)->toDateString() ?? now()->toDateString();

            Transaction::createWithSequenceRetry([
                'billing_id'       => $billing->billing_id,
                'name'             => 'Billing Payment #' . $billing->billing_id,
                'reference_number' => sprintf('BILL-%d-%s', $billing->billing_id, now()->format('YmdHis')),
                'transaction_type' => 'Credit',
                'category'         => 'Rent Payment',
                'transaction_date' => $transactionDate,
                'amount'           => $amount,
                'is_recurring'     => false,
            ]);
        }
    }
}
