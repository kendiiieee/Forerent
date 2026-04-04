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

// Schedule: run daily at midnight
Schedule::command('billings:apply-late-fees')->daily();
