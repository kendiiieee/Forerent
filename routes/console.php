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
        ->whereNull('move_out')
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

/*
|--------------------------------------------------------------------------
| test:tricia-move-in
|--------------------------------------------------------------------------
| Simulates the full move-in flow for Tricia Tenant.
| Run: php artisan test:tricia-move-in
| Add --reset to clear her existing lease first.
*/
Artisan::command('test:tricia-move-in {--reset : Remove existing active lease first}', function () {
    $reset = (bool) $this->option('reset');

    $this->newLine();
    $this->info('========================================');
    $this->info('  TRICIA\'S MOVE-IN FLOW TEST');
    $this->info('========================================');

    // STEP 1: Find or create Tricia
    $this->newLine();
    $this->comment('--- STEP 1: Find/Create Tenant ---');

    $tricia = \App\Models\User::where('email', 'tenant@example.com')->first();

    if (!$tricia) {
        $tricia = \App\Models\User::create([
            'first_name' => 'Tricia', 'last_name' => 'Tenant', 'gender' => 'Female',
            'email' => 'tenant@example.com', 'contact' => '9171234567', 'role' => 'tenant',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'permanent_address' => '123 Main St, Quezon City',
            'government_id_type' => 'National ID', 'government_id_number' => 'NID-2024-00001',
            'company_school' => 'UP Diliman', 'position_course' => 'BS Computer Science',
            'emergency_contact_name' => 'Maria Tenant',
            'emergency_contact_relationship' => 'Mother',
            'emergency_contact_number' => '9179876543',
        ]);
        $this->line("  [CREATED] Tricia Tenant (user_id: {$tricia->user_id})");
    } else {
        $tricia->update(array_filter([
            'gender' => $tricia->gender ?: 'Female',
            'contact' => $tricia->contact ?: '9171234567',
            'permanent_address' => $tricia->permanent_address ?: '123 Main St, Quezon City',
            'government_id_type' => $tricia->government_id_type ?: 'National ID',
            'government_id_number' => $tricia->government_id_number ?: 'NID-2024-00001',
            'company_school' => $tricia->company_school ?: 'UP Diliman',
            'position_course' => $tricia->position_course ?: 'BS Computer Science',
            'emergency_contact_name' => $tricia->emergency_contact_name ?: 'Maria Tenant',
            'emergency_contact_relationship' => $tricia->emergency_contact_relationship ?: 'Mother',
            'emergency_contact_number' => $tricia->emergency_contact_number ?: '9179876543',
        ]));
        $this->line("  [FOUND] Tricia Tenant (user_id: {$tricia->user_id})");
    }

    // Reset if requested
    if ($reset) {
        $existing = \App\Models\Lease::where('tenant_id', $tricia->user_id)->where('status', 'Active')->first();
        if ($existing) {
            \App\Models\MoveInInspection::where('lease_id', $existing->lease_id)->delete();
            \App\Models\Bed::where('bed_id', $existing->bed_id)->update(['status' => 'Vacant']);
            $existing->update(['status' => 'Expired']);
            $this->warn("  [RESET] Expired lease #{$existing->lease_id}, freed bed #{$existing->bed_id}");
        }
    }

    // STEP 2: Find vacant bed
    $this->newLine();
    $this->comment('--- STEP 2: Find Vacant Bed ---');

    $existingLease = \App\Models\Lease::where('tenant_id', $tricia->user_id)->where('status', 'Active')->first();

    if ($existingLease) {
        $this->warn("  [!] Already has active lease #{$existingLease->lease_id} (contract: {$existingLease->contract_status})");
        $this->line("  Skipping to inspection step...");
        $lease = $existingLease;
        $bed = \App\Models\Bed::find($lease->bed_id);
    } else {
        $bed = \App\Models\Bed::where('status', 'Vacant')
            ->whereHas('unit', fn($q) => $q->whereIn('occupants', ['Female', 'Co-ed']))
            ->with('unit.property')->first();
        if (!$bed) $bed = \App\Models\Bed::where('status', 'Vacant')->with('unit.property')->first();
        if (!$bed) {
            $this->error('  No vacant beds available!');
            return 1;
        }

        $unit = $bed->unit;
        $property = $unit->property;
        $this->line("  [FOUND] Bed #{$bed->bed_number} in Unit {$unit->unit_number}, {$property->name}");

        // STEP 3: Create lease + billings
        $this->newLine();
        $this->comment('--- STEP 3: Create Lease ---');

        $startDate = Carbon::today();
        $term = 12;
        $monthlyRate = $unit->price ?? 5500;
        $securityDeposit = $monthlyRate;
        $lease = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($tricia, $bed, $startDate, $term, $monthlyRate, $securityDeposit, &$lease) {
            $lease = \App\Models\Lease::create([
                'tenant_id' => $tricia->user_id, 'bed_id' => $bed->bed_id,
                'status' => 'Active', 'contract_status' => 'draft', 'term' => $term,
                'auto_renew' => false, 'start_date' => $startDate,
                'end_date' => $startDate->copy()->addMonths($term),
                'contract_rate' => $monthlyRate, 'advance_amount' => $monthlyRate,
                'security_deposit' => $securityDeposit, 'move_in' => $startDate,
                'shift' => 'Morning', 'monthly_due_date' => 5, 'late_payment_penalty' => 1,
                'short_term_premium' => 0, 'reservation_fee_paid' => 0, 'early_termination_fee' => 0,
            ]);

            $billing = \App\Models\Billing::create([
                'lease_id' => $lease->lease_id, 'billing_date' => $startDate,
                'next_billing' => $startDate->copy()->addMonth(),
                'to_pay' => $monthlyRate, 'amount' => $monthlyRate, 'status' => 'Paid',
            ]);

            $depBilling = \App\Models\Billing::create([
                'lease_id' => $lease->lease_id, 'billing_date' => $startDate,
                'next_billing' => $startDate, 'to_pay' => $securityDeposit,
                'amount' => $securityDeposit, 'status' => 'Unpaid',
            ]);

            $advTx = \App\Models\Transaction::createWithSequenceRetry([
                'billing_id' => $billing->billing_id, 'reference_number' => 'placeholder',
                'transaction_type' => 'Debit', 'category' => 'Advance',
                'transaction_date' => today(), 'amount' => $monthlyRate,
            ]);
            $advTx->update(['reference_number' => 'ADV' . now()->format('Ymd') . '-' . str_pad($advTx->transaction_id, 6, '0', STR_PAD_LEFT)]);

            $depTx = \App\Models\Transaction::createWithSequenceRetry([
                'billing_id' => $depBilling->billing_id, 'reference_number' => 'placeholder',
                'transaction_type' => 'Debit', 'category' => 'Deposit',
                'transaction_date' => today(), 'amount' => $securityDeposit,
            ]);
            $depTx->update(['reference_number' => 'DEP' . now()->format('Ymd') . '-' . str_pad($depTx->transaction_id, 6, '0', STR_PAD_LEFT)]);

            $bed->update(['status' => 'Occupied']);
        });

        $this->line("  [CREATED] Lease #{$lease->lease_id}");
        $this->line("    Rate: PHP {$monthlyRate}/mo | Deposit: PHP {$securityDeposit}");
        $this->line("    Start: {$startDate->toDateString()} | Contract: {$lease->contract_status}");
    }

    // STEP 4: Move-in inspection
    $this->newLine();
    $this->comment('--- STEP 4: Move-In Inspection ---');

    $checklistItems = ['Bed Frame', 'Cabinet', 'AC Unit', 'Bathroom Fixtures', 'Electrical', 'Windows', 'Walls', 'Floor', 'Door Lock'];
    $receivedItems = ['Unit Key(s)', 'Building Access Card', 'Wi-Fi Credentials', 'AC Remote', 'Cabinet Key'];

    foreach ($checklistItems as $item) {
        \App\Models\MoveInInspection::updateOrCreate(
            ['lease_id' => $lease->lease_id, 'type' => 'checklist', 'item_name' => $item],
            ['condition' => 'good', 'remarks' => null, 'tenant_confirmed' => true]
        );
    }
    $this->line('  [OK] ' . count($checklistItems) . ' checklist items (all good)');

    foreach ($receivedItems as $item) {
        \App\Models\MoveInInspection::updateOrCreate(
            ['lease_id' => $lease->lease_id, 'type' => 'item_received', 'item_name' => $item],
            ['condition' => 'good', 'quantity' => 1, 'remarks' => null, 'tenant_confirmed' => true]
        );
    }
    $this->line('  [OK] ' . count($receivedItems) . ' items received');

    // STEP 5: Contract signing
    $this->newLine();
    $this->comment('--- STEP 5: Contract Signing ---');

    $lease->update([
        'contract_status' => 'pending_tenant',
        'owner_signature' => 'data:image/png;base64,OWNER_SIG_PLACEHOLDER',
        'owner_signed_at' => now(), 'owner_signed_ip' => '127.0.0.1',
    ]);
    $this->line('  [OK] Owner signed -> pending_tenant');

    $lease->update([
        'contract_status' => 'executed',
        'tenant_signature' => 'data:image/png;base64,TENANT_SIG_PLACEHOLDER',
        'tenant_signed_at' => now(), 'tenant_signed_ip' => '127.0.0.1',
        'contract_agreed' => true,
    ]);
    $this->line('  [OK] Tenant signed -> executed');

    // SUMMARY
    $lease->refresh();
    $billings = \App\Models\Billing::where('lease_id', $lease->lease_id)->get();
    $inspCount = \App\Models\MoveInInspection::where('lease_id', $lease->lease_id)->count();

    $this->newLine();
    $this->info('========================================');
    $this->info('  MOVE-IN COMPLETE');
    $this->info('========================================');
    $this->line("  Tenant:    {$tricia->first_name} {$tricia->last_name} ({$tricia->email})");
    $this->line("  Lease:     #{$lease->lease_id}");
    $this->line("  Bed:       #{$lease->bed_id} (" . \App\Models\Bed::find($lease->bed_id)->status . ')');
    $this->line("  Status:    {$lease->status} | Contract: {$lease->contract_status}");
    $this->line("  Move-In:   {$lease->move_in}");
    $this->line("  Billings:  {$billings->count()}");
    foreach ($billings as $b) {
        $this->line("    - #{$b->billing_id}: PHP {$b->amount} ({$b->status})");
    }
    $this->line("  Inspections: {$inspCount} items");
    $this->line("  Signed:    Owner=" . ($lease->owner_signed_at ? 'Yes' : 'No') . " | Tenant=" . ($lease->tenant_signed_at ? 'Yes' : 'No'));
    $this->newLine();
    $this->info('  Flow complete. You can view Tricia in the dashboard.');
    $this->newLine();

    return 0;
})->purpose('Test: Simulate Tricia\'s full move-in flow');

/*
|--------------------------------------------------------------------------
| test:tricia-move-out
|--------------------------------------------------------------------------
| Simulates the full move-out flow for Tricia Tenant.
| Requires Tricia to have an active, fully-signed lease (run test:tricia-move-in first).
| Walks through: initiate → inspection → items returned → sign → finalize.
| Run: php artisan test:tricia-move-out
*/
Artisan::command('test:tricia-move-out', function () {
    $this->newLine();
    $this->info('========================================');
    $this->info('  TRICIA\'S MOVE-OUT FLOW TEST');
    $this->info('========================================');

    // STEP 1: Find Tricia + active lease
    $this->newLine();
    $this->comment('--- STEP 1: Find Tenant & Active Lease ---');

    $tricia = \App\Models\User::where('email', 'tenant@example.com')->first();
    if (!$tricia) {
        $this->error('  Tricia not found! Run test:tricia-move-in first.');
        return 1;
    }
    $this->line("  [FOUND] {$tricia->first_name} {$tricia->last_name} (user_id: {$tricia->user_id})");

    $lease = \App\Models\Lease::where('tenant_id', $tricia->user_id)
        ->where('status', 'Active')
        ->with(['bed.unit.property', 'moveInInspections', 'billings'])
        ->latest()
        ->first();

    if (!$lease) {
        $this->error('  No active lease found! Run test:tricia-move-in first.');
        return 1;
    }
    $this->line("  [FOUND] Lease #{$lease->lease_id} | Bed #{$lease->bed_id} | Contract: {$lease->contract_status}");

    // Settle all unpaid billings so the prerequisite passes
    $unpaid = $lease->billings()->whereIn('status', ['Unpaid', 'Overdue'])->get();
    foreach ($unpaid as $billing) {
        $billing->update(['status' => 'Paid']);
    }
    if ($unpaid->count() > 0) {
        $this->line("  [SETTLED] {$unpaid->count()} billing(s) marked as Paid");
    }

    // STEP 2: Initiate move-out
    $this->newLine();
    $this->comment('--- STEP 2: Initiate Move-Out ---');

    $lease->update([
        'move_out_initiated_at' => now(),
        'forwarding_address' => '456 New Address, Makati City',
        'reason_for_vacating' => 'End of lease term (contract expired)',
        'deposit_refund_method' => 'GCash',
        'deposit_refund_account' => '0917-123-4567',
    ]);

    \App\Models\ContractAuditLog::log($lease->lease_id, 'move_out_initiated', [
        'metadata' => ['reason' => 'End of lease term (contract expired)', 'source' => 'test_command'],
    ]);

    $this->line('  [OK] move_out_initiated_at = ' . now()->format('Y-m-d H:i'));
    $this->line('  [OK] Forwarding: 456 New Address, Makati City');
    $this->line('  [OK] Refund: GCash → 0917-123-4567');

    // STEP 3: Move-out room inspection
    $this->newLine();
    $this->comment('--- STEP 3: Move-Out Room Inspection ---');

    $checklistItems = \App\Livewire\Concerns\InspectionConfig::CHECKLIST_ITEMS;
    $conditions = ['good', 'good', 'good', 'good', 'good', 'good', 'damaged', 'good', 'good'];
    $repairCosts = [null, null, null, null, null, null, 500.00, null, null]; // Walls damaged = ₱500

    foreach ($checklistItems as $i => $item) {
        \App\Models\MoveOutInspection::updateOrCreate(
            ['lease_id' => $lease->lease_id, 'type' => 'checklist', 'item_name' => $item],
            [
                'condition' => $conditions[$i],
                'remarks' => $conditions[$i] === 'damaged' ? 'Scuff marks and small holes' : null,
                'repair_cost' => $repairCosts[$i],
            ]
        );
    }
    $this->line('  [OK] ' . count($checklistItems) . ' checklist items recorded');
    $this->line('  [!] "Walls (stains, cracks, holes)" marked DAMAGED — repair cost: PHP 500');

    // STEP 4: Items returned
    $this->newLine();
    $this->comment('--- STEP 4: Items Returned ---');

    $returnItems = \App\Livewire\Concerns\InspectionConfig::RETURNED_ITEMS;
    $returnStatus = [true, true, true, false]; // Cabinet Key NOT returned
    $replacementCosts = [null, null, null, 150.00]; // Cabinet Key = ₱150

    foreach ($returnItems as $i => $item) {
        \App\Models\MoveOutInspection::updateOrCreate(
            ['lease_id' => $lease->lease_id, 'type' => 'item_returned', 'item_name' => $item],
            [
                'quantity' => 1,
                'remarks' => $returnStatus[$i] ? 'Good condition' : 'Not returned',
                'is_returned' => $returnStatus[$i],
                'tenant_confirmed' => $returnStatus[$i],
                'replacement_cost' => $replacementCosts[$i],
            ]
        );
    }
    $this->line('  [OK] ' . count($returnItems) . ' items processed');
    $this->line('  [!] "Cabinet Key" NOT returned — replacement cost: PHP 150');

    // STEP 5: Sign move-out contract (both parties)
    $this->newLine();
    $this->comment('--- STEP 5: Move-Out Contract Signing ---');

    $lease->update([
        'moveout_owner_signature' => 'signatures/test_moveout_owner.png',
        'moveout_owner_signed_at' => now(),
        'moveout_owner_signed_ip' => '127.0.0.1',
        'moveout_contract_status' => 'pending_tenant',
    ]);
    $this->line('  [OK] Owner signed → pending_tenant');

    $lease->update([
        'moveout_tenant_signature' => 'signatures/test_moveout_tenant.png',
        'moveout_tenant_signed_at' => now(),
        'moveout_tenant_signed_ip' => '127.0.0.1',
        'moveout_contract_agreed' => true,
        'moveout_contract_status' => 'executed',
    ]);
    $this->line('  [OK] Tenant signed → executed (both signed)');

    // STEP 6: Calculate deposit refund (preview — not finalized yet)
    $this->newLine();
    $this->comment('--- STEP 6: Deposit Refund Preview ---');

    $refundData = $lease->calculateDepositRefund($lease->end_date);
    $this->line("  Deposit:         PHP " . number_format($refundData['deposit'], 2));
    foreach ($refundData['deductions'] as $d) {
        $this->line("  (-) {$d['label']}: PHP " . number_format($d['amount'], 2));
    }
    $this->line("  Total Deductions: PHP " . number_format($refundData['total_deductions'], 2));
    $this->info("  NET REFUND:       PHP " . number_format($refundData['refund_amount'], 2));

    // STEP 7: Finalize move-out
    $this->newLine();
    $this->comment('--- STEP 7: Finalize Move-Out ---');

    $originalEndDate = $lease->end_date;
    $today = Carbon::today();

    $lease->update([
        'status' => 'Expired',
        'move_out' => $today,
        'end_date' => $today,
    ]);

    $refundData = $lease->calculateDepositRefund($originalEndDate);
    $lease->update([
        'deposit_refund_amount' => $refundData['refund_amount'],
        'deposit_deductions' => $refundData['deductions'],
    ]);

    \App\Models\Bed::where('bed_id', $lease->bed_id)->update(['status' => 'Vacant']);

    \App\Models\ContractAuditLog::log($lease->lease_id, 'move_out_completed', [
        'metadata' => [
            'deposit_refund' => $refundData['refund_amount'],
            'total_deductions' => $refundData['total_deductions'],
            'original_end_date' => $originalEndDate?->format('Y-m-d'),
            'source' => 'test_command',
        ],
    ]);

    $this->line('  [OK] Lease → Expired');
    $this->line('  [OK] Bed → Vacant');
    $this->line('  [OK] Deposit refund calculated & saved');

    // SUMMARY
    $lease->refresh();

    $this->newLine();
    $this->info('========================================');
    $this->info('  MOVE-OUT COMPLETE');
    $this->info('========================================');
    $this->line("  Tenant:     {$tricia->first_name} {$tricia->last_name}");
    $this->line("  Lease:      #{$lease->lease_id} → {$lease->status}");
    $this->line("  Bed:        #{$lease->bed_id} → " . \App\Models\Bed::find($lease->bed_id)->status);
    $this->line("  Move-Out:   {$lease->move_out}");
    $this->line("  Initiated:  {$lease->move_out_initiated_at}");
    $this->line("  Reason:     {$lease->reason_for_vacating}");
    $this->line("  Forwarding: {$lease->forwarding_address}");
    $this->line("  Refund:     PHP " . number_format($lease->deposit_refund_amount, 2) . " via {$lease->deposit_refund_method}");
    $this->line("  Contract:   moveout_contract_status = {$lease->moveout_contract_status}");
    $this->line("  Signed:     Owner=" . ($lease->moveout_owner_signed_at ? 'Yes' : 'No') . " | Tenant=" . ($lease->moveout_tenant_signed_at ? 'Yes' : 'No'));

    $deductions = $lease->deposit_deductions ?? [];
    if (!empty($deductions)) {
        $this->line("  Deductions:");
        foreach ($deductions as $d) {
            $this->line("    - {$d['label']}: PHP " . number_format($d['amount'], 2));
        }
    }

    $this->newLine();
    $this->info('  Flow complete. You can view the expired lease in the dashboard.');
    $this->newLine();

    return 0;
})->purpose('Test: Simulate Tricia\'s full move-out flow');

// Schedule: run daily at midnight
Schedule::command('billings:apply-late-fees')->daily();
Schedule::command('leases:handle-expiration')->daily();
