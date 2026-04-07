<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Lease;
use App\Models\UtilityBill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingSeeder extends Seeder
{
    private array $utilityCache = [];
    private const CHUNK_SIZE = 200;

    public function run(): void
    {
        // ✅ Normalize billing_period to Y-m to match loop's $period format
        UtilityBill::all()->each(function ($bill) {
            $period = Carbon::parse($bill->billing_period)->format('Y-m');
            $key = "{$bill->unit_id}_{$period}_{$bill->utility_type}";
            $this->utilityCache[$key] = $bill->per_tenant_amount;
        });

        $leases = Lease::with('bed.unit')->orderBy('start_date')->get();
        $firstLeasePerTenant = [];

        Billing::withoutEvents(function () use ($leases, &$firstLeasePerTenant) {
            foreach ($leases as $lease) {
                $tenantId     = $lease->tenant_id;
                $isFirstLease = !isset($firstLeasePerTenant[$tenantId]);

                if ($isFirstLease) {
                    $firstLeasePerTenant[$tenantId] = $lease->lease_id;
                    $lease->update([
                        'advance_amount'   => $lease->contract_rate,
                        'security_deposit' => $lease->contract_rate,
                    ]);
                }

                $billings     = [];
                $billingItems = [];

                if ($isFirstLease) {
                    $billings[]   = $this->buildMoveInBilling($lease);
                    $billingItems = array_merge($billingItems, $this->buildMoveInItems($lease));
                }

                [$monthlyBillings, $monthlyItems] = $this->buildMonthlyBillings($lease);
                $billings     = array_merge($billings, $monthlyBillings);
                $billingItems = array_merge($billingItems, $monthlyItems);

                if ($lease->status === 'Expired') {
                    [$moveOutBilling, $moveOutItems] = $this->buildMoveOutBilling($lease);
                    if (!empty($moveOutBilling)) {
                        $billings[]   = $moveOutBilling;
                        $billingItems = array_merge($billingItems, $moveOutItems);
                    }
                }

                // Prevent duplicate billings
                $existingDates = DB::table('billings')
                    ->where('lease_id', $lease->lease_id)
                    ->whereIn('billing_date', collect($billings)->pluck('billing_date'))
                    ->pluck('billing_date')
                    ->all();

                $billings = array_filter($billings, fn($b) => !in_array($b['billing_date'], $existingDates));

                foreach (array_chunk($billings, self::CHUNK_SIZE) as $chunk) {
                    DB::table('billings')->insert($chunk);
                }

                // Resolve inserted billing IDs for items
                $insertedBillings = DB::table('billings')
                    ->where('lease_id', $lease->lease_id)
                    ->get(['billing_id', 'billing_date', 'billing_type'])
                    ->keyBy(fn($r) => $r->billing_type . '_' . $r->billing_date);

                $resolvedItems = [];
                foreach ($billingItems as $item) {
                    $key = $item['_billing_type'] . '_' . $item['_billing_date'];
                    $row = $insertedBillings[$key] ?? null;
                    if (!$row) continue;

                    unset($item['_billing_type'], $item['_billing_date']);
                    if ($item['amount'] != 0) {
                        $item['billing_id'] = $row->billing_id;
                        $resolvedItems[]    = $item;
                    }
                }

                foreach (array_chunk($resolvedItems, self::CHUNK_SIZE) as $chunk) {
                    DB::table('billing_items')->insert($chunk);
                }
            }
        });
    }

    // -------------------- Builder methods --------------------

    private function buildMoveInBilling(Lease $lease): array
    {
        $date   = Carbon::parse($lease->move_in)->format('Y-m-d');
        $amount = $lease->contract_rate * 2;

        return [
            'lease_id'         => $lease->lease_id,
            'tenant_id'        => $lease->tenant_id,
            'billing_type'     => 'move_in',
            'billing_date'     => $date,
            'next_billing'     => Carbon::parse($lease->move_in)->addMonth()->format('Y-m-d'),
            'due_date'         => $date,
            'to_pay'           => $amount,
            'amount'           => $amount,
            'previous_balance' => 0,
            'status'           => 'Paid',
            'created_at'       => now(),
            'updated_at'       => now(),
        ];
    }

    private function buildMoveInItems(Lease $lease): array
    {
        $date = Carbon::parse($lease->move_in)->format('Y-m-d');
        $meta = ['_billing_type' => 'move_in', '_billing_date' => $date];

        return [
            $meta + [
                'charge_category' => 'move_in',
                'charge_type'     => 'advance',
                'description'     => '1 Month Advance — First Month Rent',
                'amount'          => $lease->contract_rate,
            ],
            $meta + [
                'charge_category' => 'move_in',
                'charge_type'     => 'security_deposit',
                'description'     => '1 Month Security Deposit',
                'amount'          => $lease->contract_rate,
            ],
        ];
    }

    private function buildMonthlyBillings(Lease $lease): array
    {
        $today         = Carbon::now();
        $contractPrice = $lease->contract_rate;
        $leaseTerm     = $lease->term;
        $tenantId      = $lease->tenant_id;
        $unitId        = $lease->bed->unit_id;

        $billingDate = Carbon::parse($lease->start_date)->startOfMonth()->addMonth();
        $leaseEnd    = Carbon::parse($lease->end_date)->startOfMonth();
        $ceiling     = $leaseEnd->lt($today->copy()->startOfMonth())
            ? $leaseEnd
            : $today->copy()->startOfMonth();

        $runningUnpaidBalance = 0;
        $billings  = [];
        $items     = [];
        $lastMonth = $today->copy()->subMonth()->startOfMonth();

        while ($billingDate->lte($ceiling)) {
            $nextBilling    = $billingDate->copy()->addMonth();
            $dueDate        = $billingDate->copy()->addDays(5);
            $period         = $billingDate->format('Y-m');
            $billingType    = 'monthly';
            $billingDateStr = $billingDate->format('Y-m-d');
            $meta           = ['_billing_type' => $billingType, '_billing_date' => $billingDateStr];

            $rowItems = [];

            // Rent
            $rowItems[] = $meta + [
                    'charge_category' => 'recurring',
                    'charge_type'     => 'rent',
                    'description'     => 'Monthly Rent',
                    'amount'          => $contractPrice,
                ];

            // Electricity
            $electricityShare = $this->utilityCache["{$unitId}_{$period}_electricity"] ?? 0;
            if ($electricityShare > 0) {
                $rowItems[] = $meta + [
                        'charge_category' => 'recurring',
                        'charge_type'     => 'electricity_share',
                        'description'     => 'Electricity Share',
                        'amount'          => $electricityShare,
                    ];
            }

            // Water
            $waterShare = $this->utilityCache["{$unitId}_{$period}_water"] ?? 0;
            if ($waterShare > 0) {
                $rowItems[] = $meta + [
                        'charge_category' => 'recurring',
                        'charge_type'     => 'water_share',
                        'description'     => 'Water Share',
                        'amount'          => $waterShare,
                    ];
            }

            // Short-term premium
            if ($leaseTerm < 6) {
                $rowItems[] = $meta + [
                        'charge_category' => 'conditional',
                        'charge_type'     => 'short_term_premium',
                        'description'     => 'Short-Term Premium (contract under 6 months)',
                        'amount'          => 500.00,
                    ];
            }

            // Decide status
            if ($billingDate->eq($lastMonth)) {
                $status = (mt_rand(1, 100) <= 5) ? 'Overdue' : 'Paid';
            } elseif ($billingDate->eq($today->copy()->startOfMonth())) {
                $status = (mt_rand(1, 100) <= 20) ? 'Unpaid' : 'Paid';
            } elseif ($billingDate->lt($lastMonth)) {
                $status = 'Paid';
            } else {
                $status = 'Unpaid';
            }

            // ✅ Late fee — only when actually past due, dueDate → today
            if ($status === 'Overdue') {
                $daysLate = (int) $dueDate->diffInDays($today);

                if ($daysLate > 0) {
                    $lateFee = round($contractPrice * 0.01 * $daysLate, 2);

                    $rowItems[] = $meta + [
                            'charge_category' => 'conditional',
                            'charge_type'     => 'late_fee',
                            'description'     => "Late Fee ({$daysLate} days, 1%/day)",
                            'amount'          => $lateFee,
                        ];
                }
            }

            $total           = round(collect($rowItems)->sum('amount'), 2);
            $previousBalance = $runningUnpaidBalance;

            // Skip zero-amount billings entirely
            if ($total <= 0) {
                $billingDate->addMonth();
                continue;
            }

            $billings[] = [
                'lease_id'         => $lease->lease_id,
                'tenant_id'        => $tenantId,
                'billing_type'     => $billingType,
                'billing_date'     => $billingDateStr,
                'next_billing'     => $nextBilling->format('Y-m-d'),
                'due_date'         => $dueDate->format('Y-m-d'),
                'to_pay'           => $total,
                'amount'           => $total,
                'previous_balance' => round($previousBalance, 2),
                'status'           => $status,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $items = array_merge($items, $rowItems);
            $runningUnpaidBalance = ($status === 'Paid') ? 0 : $total;
            $billingDate->addMonth();
        }

        return [$billings, $items];
    }

    private function buildMoveOutBilling(Lease $lease): array
    {
        $moveOutDate  = Carbon::parse($lease->end_date)->format('Y-m-d');
        $deposit      = (float) $lease->security_deposit;
        $unitId       = $lease->bed->unit_id;
        $period       = Carbon::parse($moveOutDate)->format('Y-m');
        $billingType  = 'move_out';
        $meta         = ['_billing_type' => $billingType, '_billing_date' => $moveOutDate];

        $items = [];

        // 1. Add Utility Charges (Positive values)
        $electricityShare = $this->utilityCache["{$unitId}_{$period}_electricity"] ?? 0;
        if ($electricityShare > 0) {
            $items[] = $meta + [
                    'charge_category' => 'move_out',
                    'charge_type'     => 'electricity_share',
                    'description'     => 'Final Electricity Share',
                    'amount'          => $electricityShare,
                ];
        }

        $waterShare = $this->utilityCache["{$unitId}_{$period}_water"] ?? 0;
        if ($waterShare > 0) {
            $items[] = $meta + [
                    'charge_category' => 'move_out',
                    'charge_type'     => 'water_share',
                    'description'     => 'Final Water Share',
                    'amount'          => $waterShare,
                ];
        }

        // 2. Add Security Deposit Refund as a NEGATIVE amount
        // This will subtract from the total amount of this billing
        if ($deposit > 0) {
            $items[] = $meta + [
                    'charge_category' => 'move_out',
                    'charge_type'     => 'deposit_refund',
                    'description'     => 'Security Deposit Refund',
                    'amount'          => -$deposit, // Negative value
                ];
        }

        // 3. Calculate the sum of all items (Utilities - Deposit)
        $finalTotal = round(collect($items)->sum('amount'), 2);

        // If there are no items at all (no utilities and no deposit), skip
        if (empty($items)) {
            return [[], []];
        }

        return [
            [
                'lease_id'         => $lease->lease_id,
                'tenant_id'        => $lease->tenant_id,
                'billing_type'     => $billingType,
                'billing_date'     => $moveOutDate,
                'next_billing'     => $moveOutDate,
                'due_date'         => Carbon::parse($moveOutDate)->addDays(15)->format('Y-m-d'),
                'to_pay'           => $finalTotal, // This will be negative if deposit > utilities
                'amount'           => $finalTotal, // This will be negative if deposit > utilities
                'previous_balance' => 0,
                'status'           => 'Paid',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            $items,
        ];
    }
}
