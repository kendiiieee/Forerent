<?php

/**
 * Stages an early-move-out demo using Tricia (tenant@example.com).
 * Marcus (manager@example.com) will perform the move-out.
 *
 * Run BEFORE the panel demo:
 *   docker compose -f compose.dev.yaml exec workspace php artisan tinker \
 *     --execute="require 'database/scripts/demo_prep_early_moveout.php';"
 *
 * Re-runnable / idempotent.
 */

use App\Models\Lease;
use App\Models\MoveOutInspection;
use App\Models\User;
use Carbon\Carbon;

$today = Carbon::today();

$tenant = User::where('email', 'tenant@example.com')->first();
$manager = User::where('email', 'manager@example.com')->first();

if (!$tenant) {
    echo "❌ tenant@example.com not found. Run `php artisan migrate:fresh --seed` first.\n";
    return;
}
if (!$manager) {
    echo "❌ manager@example.com not found. Run `php artisan migrate:fresh --seed` first.\n";
    return;
}

// Find Tricia's most recent lease (any status). The seeder may leave it Expired —
// we'll reactivate it for the demo.
$lease = Lease::where('tenant_id', $tenant->user_id)
    ->with('bed.unit.property')
    ->orderByDesc('lease_id')
    ->first();

if (!$lease) {
    echo "❌ No lease found for {$tenant->email}. Run `php artisan migrate:fresh --seed` first.\n";
    return;
}

// Reactivate if seeder left it Expired
if ($lease->status !== 'Active') {
    $lease->update([
        'status'             => 'Active',
        'move_out'           => null,
        'termination_reason' => null,
    ]);
}

// Make sure the bed is Occupied (in case a previous demo run flipped it Vacant)
if ($lease->bed && $lease->bed->status !== 'Occupied') {
    $lease->bed->update(['status' => 'Occupied']);
}

// Reassign Tricia's unit to Marcus so the demo uses the manager@example.com login
if ($lease->bed?->unit && $lease->bed->unit->manager_id !== $manager->user_id) {
    $lease->bed->unit->update(['manager_id' => $manager->user_id]);
}

// Push end_date out so it's UNAMBIGUOUSLY early termination (4 months remaining)
$newEndDate = $today->copy()->addMonths(4);
if ($lease->end_date->lt($newEndDate)) {
    $lease->update(['end_date' => $newEndDate]);
}

// Mark all bills paid so termination_reason = early_termination (not non_payment)
$lease->billings()->whereIn('status', ['Unpaid', 'Overdue'])->update(['status' => 'Paid']);

// Satisfy every prerequisite the move-out modal checks
$lease->update([
    'move_out_initiated_at'     => $today->copy()->subDays(31), // 30-day notice cleared
    'moveout_tenant_signature'  => 'data:image/png;base64,DEMO_T',
    'moveout_tenant_signed_at'  => $today->copy()->subDays(1),
    'moveout_owner_signature'   => 'data:image/png;base64,DEMO_O',
    'moveout_owner_signed_at'   => $today->copy()->subDays(1),
    'moveout_manager_signature' => 'data:image/png;base64,DEMO_M',
    'moveout_manager_signed_at' => $today->copy()->subDays(1),
    'moveout_contract_agreed'   => true,
    'moveout_contract_status'   => 'signed',
    'reason_for_vacating'       => 'Job relocation to another city',
    'deposit_refund_method'     => 'GCash',
    'deposit_refund_account'    => '09171234567',
]);

MoveOutInspection::firstOrCreate(
    ['lease_id' => $lease->lease_id, 'type' => 'checklist', 'item_name' => 'Walls'],
    ['condition' => 'good', 'tenant_confirmed' => true]
);
MoveOutInspection::firstOrCreate(
    ['lease_id' => $lease->lease_id, 'type' => 'checklist', 'item_name' => 'Floor'],
    ['condition' => 'good', 'tenant_confirmed' => true]
);
MoveOutInspection::firstOrCreate(
    ['lease_id' => $lease->lease_id, 'type' => 'item_returned', 'item_name' => 'Room Key'],
    ['is_returned' => true, 'quantity' => 1, 'quantity_returned' => 1, 'tenant_confirmed' => true]
);
MoveOutInspection::firstOrCreate(
    ['lease_id' => $lease->lease_id, 'type' => 'item_returned', 'item_name' => 'Locker Key'],
    ['is_returned' => true, 'quantity' => 1, 'quantity_returned' => 1, 'tenant_confirmed' => true]
);

$lease->refresh();

echo "✅ Demo lease ready.\n\n";
echo "  Tenant:           {$tenant->first_name} {$tenant->last_name} ({$tenant->email})\n";
echo "  Lease ID:         {$lease->lease_id}\n";
echo "  Property:         " . ($lease->bed?->unit?->property?->building_name ?? 'n/a') . "\n";
echo "  Unit:             " . ($lease->bed?->unit?->unit_number ?? 'n/a') . "\n";
echo "  Bed ID / status:  {$lease->bed_id} / {$lease->bed?->status}\n";
echo "  End date:         {$lease->end_date->toDateString()} (today is {$today->toDateString()})\n";
echo "  Months remaining: " . (int) $today->diffInMonths($lease->end_date) . "\n";
echo "  Security deposit: ₱" . number_format($lease->security_deposit, 2) . "\n\n";
echo "DEMO LOGIN:  manager@example.com / password\n";
echo "DEMO PATH:   http://localhost:8000/manager/tenant   →  search 'Tricia'\n";
echo "DEMO CLICK:  the 'Finalize Move Out' button\n";
