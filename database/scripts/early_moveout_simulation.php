<?php

/**
 * Early move-out simulation. Run with:
 *
 *   php artisan tinker --execute="require 'database/scripts/early_moveout_simulation.php';"
 *
 * It picks an existing tenant + active lease (or seeds a quick one), forces an early
 * move-out, and prints the resulting deposit calculation + bed status. Re-runnable.
 */

use App\Livewire\Layouts\Tenants\TenantDetail;
use App\Models\Bed;
use App\Models\Lease;
use App\Models\MoveOutInspection;
use App\Models\User;
use Carbon\Carbon;

$today = Carbon::today();

// 1. Find a tenant with an active lease (the seeded "tenant@example.com" works)
$lease = Lease::where('status', 'Active')
    ->whereDate('end_date', '>', $today)
    ->with('tenant', 'bed')
    ->first();

if (!$lease) {
    echo "No active future-dated lease found. Run `php artisan db:seed` first.\n";
    return;
}

echo "Using lease #{$lease->lease_id} for tenant {$lease->tenant->email}\n";
echo "  Original end_date:    {$lease->end_date->toDateString()}\n";
echo "  Security deposit:     " . number_format($lease->security_deposit, 2) . "\n";

// 2. Seed the prerequisites the move-out flow checks for
$lease->update([
    'move_out_initiated_at'     => $today->copy()->subDays(31), // satisfy 30-day notice
    'moveout_tenant_signature'  => 'data:image/png;base64,SIM',
    'moveout_owner_signature'   => 'data:image/png;base64,SIM',
    'moveout_manager_signature' => 'data:image/png;base64,SIM',
    'moveout_contract_agreed'   => true,
]);

MoveOutInspection::firstOrCreate(
    ['lease_id' => $lease->lease_id, 'type' => 'checklist', 'item_name' => 'Walls'],
    ['condition' => 'good']
);
MoveOutInspection::firstOrCreate(
    ['lease_id' => $lease->lease_id, 'type' => 'item_returned', 'item_name' => 'Key'],
    ['is_returned' => true, 'quantity' => 1, 'quantity_returned' => 1]
);

// 3. Drive the Livewire confirmMoveOut() method directly
$manager = User::where('role', 'manager')->first();
auth()->login($manager);

\Livewire\Livewire::actingAs($manager)
    ->test(TenantDetail::class)
    ->set('currentTenantId', $lease->tenant_id)
    ->set('currentLeaseId', $lease->lease_id)
    ->call('confirmMoveOut');

// 4. Inspect the result
$lease->refresh();
$bed = Bed::find($lease->bed_id);

echo "\n--- After move-out ---\n";
echo "  Status:               {$lease->status}\n";
echo "  Termination reason:   {$lease->termination_reason}\n";
echo "  move_out:             {$lease->move_out->toDateString()}\n";
echo "  Deposit refund:       " . number_format($lease->deposit_refund_amount, 2) . "\n";
echo "  Deductions:           " . json_encode($lease->deposit_deductions) . "\n";
echo "  Bed #{$bed->bed_id} status: {$bed->status}\n";
