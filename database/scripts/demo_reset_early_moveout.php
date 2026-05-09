<?php

/**
 * Resets Tricia's lease back to Active so the early move-out demo can be re-run.
 *
 *   docker compose -f compose.dev.yaml exec workspace php artisan tinker \
 *     --execute="require 'database/scripts/demo_reset_early_moveout.php';"
 *
 * After this, run demo_prep_early_moveout.php to re-stage the prerequisites.
 */

use App\Models\Bed;
use App\Models\Lease;
use App\Models\User;

$tenant = User::where('email', 'tenant@example.com')->first();
if (!$tenant) {
    echo "❌ tenant@example.com not found.\n";
    return;
}

// Most recent lease for Tricia (regardless of status, so we can reactivate Expired ones)
$lease = Lease::where('tenant_id', $tenant->user_id)
    ->orderByDesc('lease_id')
    ->first();

if (!$lease) {
    echo "❌ No lease found for {$tenant->email}.\n";
    return;
}

$lease->update([
    'status'                 => 'Active',
    'move_out'               => null,
    'termination_reason'     => null,
    'end_date'               => now()->addMonths(4),
    'deposit_refund_amount'  => null,
    'deposit_deductions'     => null,
    'deposit_refund_deadline'=> null,
]);

if ($lease->bed_id) {
    Bed::where('bed_id', $lease->bed_id)->update(['status' => 'Occupied']);
}

echo "✅ Lease #{$lease->lease_id} reset to Active. Bed #{$lease->bed_id} set to Occupied.\n";
echo "   Now run: php artisan tinker --execute=\"require 'database/scripts/demo_prep_early_moveout.php';\"\n";
