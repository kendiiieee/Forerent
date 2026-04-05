<?php

use App\Models\Billing;
use App\Models\BillingItem;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('billings:backfill-credit-transactions {--dry-run : Show what would be created without writing data}', function () {
    $dryRun = (bool) $this->option('dry-run');

    $this->info('Scanning paid billings for missing credit transactions...');
    if ($dryRun) {
        $this->comment('Dry run mode enabled. No data will be written.');
    }

    $scanned = 0;
    $missing = 0;
    $created = 0;
    $skippedNoAmount = 0;

    Billing::query()
        ->where('status', 'Paid')
        ->orderBy('billing_id')
        ->chunkById(200, function ($billings) use (&$scanned, &$missing, &$created, &$skippedNoAmount, $dryRun) {
            foreach ($billings as $billing) {
                $scanned++;

                $hasCredit = $billing->transactions()
                    ->where('transaction_type', 'Credit')
                    ->where('category', 'Rent Payment')
                    ->exists();

                if ($hasCredit) {
                    continue;
                }

                $missing++;

                $amount = (float) ($billing->amount ?? 0);
                if ($amount <= 0) {
                    $amount = (float) ($billing->to_pay ?? 0);
                }

                if ($amount <= 0) {
                    $skippedNoAmount++;
                    continue;
                }

                if (!$dryRun) {
                    $billing->ensureCreditTransaction();
                    $created++;
                }
            }
        }, 'billing_id', 'billing_id');

    $this->newLine();
    $this->line("Paid billings scanned: {$scanned}");
    $this->line("Missing credit transactions: {$missing}");
    if ($dryRun) {
        $this->line('Would be created: ' . ($missing - $skippedNoAmount));
    } else {
        $this->line("Created: {$created}");
    }
    $this->line("Skipped (no billable amount): {$skippedNoAmount}");

    if ($dryRun) {
        $this->comment('Run without --dry-run to apply changes.');
    } else {
        $this->info('Backfill completed.');
    }
})->purpose('Backfill missing credit transactions for already-paid billings');

Artisan::command('billings:realign-credit-transaction-dates {--dry-run : Show affected rows without writing data}', function () {
    $dryRun = (bool) $this->option('dry-run');

    $this->info('Checking rent-payment credit transactions against billing dates...');
    if ($dryRun) {
        $this->comment('Dry run mode enabled. No data will be written.');
    }

    $query = \App\Models\Transaction::query()
        ->join('billings', 'billings.billing_id', '=', 'transactions.billing_id')
        ->where('transactions.transaction_type', 'Credit')
        ->where('transactions.category', 'Rent Payment')
        ->whereNotNull('billings.billing_date')
        ->whereColumn('transactions.transaction_date', '!=', 'billings.billing_date');

    $affected = (clone $query)->count();
    $this->line("Rows needing realignment: {$affected}");

    if (!$dryRun && $affected > 0) {
        $updated = $query->update([
            'transactions.transaction_date' => \Illuminate\Support\Facades\DB::raw('billings.billing_date'),
        ]);

        $this->line("Rows updated: {$updated}");
        $this->info('Date realignment completed.');
    }

    if ($dryRun) {
        $this->comment('Run without --dry-run to apply changes.');
    }
})->purpose('Realign rent-payment credit transaction dates to billing dates');

/*
|--------------------------------------------------------------------------
| billings:apply-late-fees
|--------------------------------------------------------------------------
| Runs daily. For every unpaid billing past its due date:
|   1. Marks the billing status as "Overdue".
|   2. Creates or updates a late_fee BillingItem based on days overdue.
|      Formula: (lease.late_payment_penalty% / 100) × contract_rate × days_late
|   3. Recalculates the billing total (to_pay) to include the late fee.
*/
Artisan::command('billings:apply-late-fees {--dry-run : Show what would change without writing data}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $today = Carbon::today();

    $this->info('Scanning for overdue billings...');
    if ($dryRun) {
        $this->comment('Dry run mode enabled. No data will be written.');
    }

    $billings = Billing::with(['lease', 'items'])
        ->whereIn('status', ['Unpaid', 'Overdue'])
        ->whereNotNull('due_date')
        ->where('due_date', '<', $today)
        ->get();

    $processed = 0;
    $statusUpdated = 0;
    $feesCreated = 0;
    $feesUpdated = 0;
    $skipped = 0;

    foreach ($billings as $billing) {
        $lease = $billing->lease;
        if (!$lease || !$lease->late_payment_penalty || !$lease->contract_rate) {
            $skipped++;
            continue;
        }

        $processed++;
        $daysLate = Carbon::parse($billing->due_date)->startOfDay()->diffInDays($today);
        if ($daysLate < 1) {
            $skipped++;
            continue;
        }

        $penaltyRate = (float) $lease->late_payment_penalty; // percentage
        $dailyPenalty = round(($penaltyRate / 100) * (float) $lease->contract_rate, 2);
        $totalLateFee = round($dailyPenalty * $daysLate, 2);

        $description = "Late Payment Fee ({$daysLate} day(s) × ₱" . number_format($dailyPenalty, 2) . "/day)";

        $this->line("  Billing #{$billing->billing_id}: {$daysLate} day(s) late → ₱" . number_format($totalLateFee, 2));

        if (!$dryRun) {
            // Mark as Overdue if still Unpaid
            if ($billing->status === 'Unpaid') {
                $billing->update(['status' => 'Overdue']);
                $statusUpdated++;
            }

            // Find existing late_fee item for this billing
            $existingFee = BillingItem::where('billing_id', $billing->billing_id)
                ->where('charge_type', 'late_fee')
                ->first();

            if ($existingFee) {
                $existingFee->update([
                    'amount' => $totalLateFee,
                    'description' => $description,
                ]);
                $feesUpdated++;
            } else {
                BillingItem::create([
                    'billing_id' => $billing->billing_id,
                    'charge_category' => 'conditional',
                    'charge_type' => 'late_fee',
                    'description' => $description,
                    'amount' => $totalLateFee,
                ]);
                $feesCreated++;
            }

            // Recalculate billing total from all items
            $newTotal = BillingItem::where('billing_id', $billing->billing_id)->sum('amount');
            $billing->update(['to_pay' => $newTotal]);
        }
    }

    $this->newLine();
    $this->line("Overdue billings found: " . $billings->count());
    $this->line("Processed: {$processed}");
    $this->line("Skipped (no penalty config): {$skipped}");
    if ($dryRun) {
        $this->comment('Run without --dry-run to apply changes.');
    } else {
        $this->line("Status → Overdue: {$statusUpdated}");
        $this->line("Late fees created: {$feesCreated}");
        $this->line("Late fees updated: {$feesUpdated}");
        $this->info('Late fee processing completed.');
    }
})->purpose('Mark overdue billings and apply percentage-based late payment fees');

/*
|--------------------------------------------------------------------------
| leases:handle-expiration
|--------------------------------------------------------------------------
| Runs daily. Handles three scenarios:
|   1. Expiry warnings — Notify tenants 30, 15, and 7 days before lease end.
|   2. Auto-renew — If auto_renew is true, extend the lease by the same term.
|   3. Auto-expire — If end_date has passed, mark the lease as Expired.
*/
Artisan::command('leases:handle-expiration {--dry-run : Show what would change without writing data}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $today = Carbon::today();

    $this->info('Processing lease expirations...');
    if ($dryRun) {
        $this->comment('Dry run mode enabled. No data will be written.');
    }

    $warned = 0;
    $renewed = 0;
    $expired = 0;

    // 1. Expiry warnings (30, 15, 7 days before end_date)
    foreach ([30, 15, 7] as $days) {
        $targetDate = $today->copy()->addDays($days);

        $expiringLeases = \App\Models\Lease::where('status', 'Active')
            ->whereDate('end_date', $targetDate)
            ->with('tenant')
            ->get();

        foreach ($expiringLeases as $lease) {
            if (!$lease->tenant) continue;

            $this->line("  Warning ({$days}d): Lease #{$lease->lease_id} — {$lease->tenant->first_name} {$lease->tenant->last_name}");

            if (!$dryRun) {
                \App\Models\Notification::create([
                    'user_id' => $lease->tenant_id,
                    'type' => 'lease_expiring',
                    'title' => "Lease Expiring in {$days} Days",
                    'message' => "Your lease ends on " . $lease->end_date->format('M d, Y') . ". " .
                        ($lease->auto_renew ? 'Your lease is set to auto-renew.' : 'Please contact management if you wish to renew.'),
                    'link' => '/tenant',
                ]);
                $warned++;
            }
        }
    }

    // 2. Auto-renew leases past their end_date with auto_renew = true
    $autoRenewLeases = \App\Models\Lease::where('status', 'Active')
        ->where('auto_renew', true)
        ->whereDate('end_date', '<', $today)
        ->get();

    foreach ($autoRenewLeases as $lease) {
        $newEndDate = Carbon::parse($lease->end_date)->addMonths($lease->term ?: 6);

        $this->line("  Auto-renew: Lease #{$lease->lease_id} → new end {$newEndDate->format('Y-m-d')}");

        if (!$dryRun) {
            $oldEndDate = $lease->end_date->format('Y-m-d');
            $lease->update([
                'end_date' => $newEndDate,
                'contract_status' => 'draft',
                'contract_agreed' => false,
                'tenant_signature' => null,
                'owner_signature' => null,
                'tenant_signed_at' => null,
                'owner_signed_at' => null,
                'signed_contract_path' => null,
            ]);

            \App\Models\ContractAuditLog::log($lease->lease_id, 'lease_auto_renewed', [
                'field_changed' => 'end_date',
                'old_value' => $oldEndDate,
                'new_value' => $newEndDate->format('Y-m-d'),
                'metadata' => ['term' => $lease->term],
            ]);

            \App\Models\Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'lease_renewed',
                'title' => 'Lease Auto-Renewed',
                'message' => "Your lease has been automatically renewed until " . $newEndDate->format('M d, Y') . ". A new contract will need to be signed.",
                'link' => '/tenant?tab=inspection',
            ]);

            // Notify manager
            $managerId = \Illuminate\Support\Facades\DB::table('beds')
                ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                ->where('beds.bed_id', $lease->bed_id)
                ->value('units.manager_id');

            if ($managerId) {
                \App\Models\Notification::create([
                    'user_id' => $managerId,
                    'type' => 'lease_renewed',
                    'title' => 'Lease Auto-Renewed',
                    'message' => "Lease #{$lease->lease_id} has been auto-renewed until " . $newEndDate->format('M d, Y') . ". Please prepare a new contract.",
                    'link' => '/manager/tenant',
                ]);
            }

            $renewed++;
        }
    }

    // 3. Auto-initiate move-out for leases past their end_date (non auto-renew)
    //    Instead of immediately expiring, we initiate the move-out workflow
    //    so the full inspection + signing process can be completed.
    $expiredLeases = \App\Models\Lease::where('status', 'Active')
        ->where('auto_renew', false)
        ->whereDate('end_date', '<', $today)
        ->whereNull('move_out_initiated_at')
        ->get();

    foreach ($expiredLeases as $lease) {
        $this->line("  Initiate move-out: Lease #{$lease->lease_id}");

        if (!$dryRun) {
            $lease->update([
                'move_out_initiated_at' => $today,
            ]);

            \App\Models\ContractAuditLog::log($lease->lease_id, 'lease_expired_moveout_auto_initiated', [
                'metadata' => [
                    'end_date' => $lease->end_date->format('Y-m-d'),
                ],
            ]);

            \App\Models\Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'lease_expired',
                'title' => 'Lease Expired — Move-Out Required',
                'message' => 'Your lease has expired. The move-out process has been initiated. Please coordinate with management for the move-out inspection and clearance.',
                'link' => '/tenant?tab=inspection',
            ]);

            // Notify manager
            $managerId = \Illuminate\Support\Facades\DB::table('beds')
                ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                ->where('beds.bed_id', $lease->bed_id)
                ->value('units.manager_id');

            if ($managerId) {
                \App\Models\Notification::create([
                    'user_id' => $managerId,
                    'type' => 'lease_expired',
                    'title' => 'Lease Expired — Move-Out Pending',
                    'message' => "Lease #{$lease->lease_id} has expired. Move-out process initiated. Please complete the inspection and clearance.",
                    'link' => '/manager/tenant',
                ]);
            }

            $expired++;
        }
    }

    $this->newLine();
    $this->line("Warnings sent: {$warned}");
    $this->line("Auto-renewed: {$renewed}");
    $this->line("Auto-expired: {$expired}");

    if ($dryRun) {
        $this->comment('Run without --dry-run to apply changes.');
    } else {
        $this->info('Lease expiration processing completed.');
    }
})->purpose('Handle lease expiry warnings, auto-renewals, and auto-expiration');

// Schedule: run daily at midnight
Schedule::command('billings:apply-late-fees')->daily();
Schedule::command('leases:handle-expiration')->daily();
