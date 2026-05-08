<?php

use App\Livewire\Layouts\Tenants\TenantDetail;
use App\Models\Bed;
use App\Models\Lease;
use App\Models\MoveOutInspection;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

/**
 * Verifies the early-termination penalty (Contract Section 7, Civil Code Art. 1306):
 *   - Full deposit forfeiture (refund = 0).
 *   - Lease marked Expired with termination_reason = 'early_termination'.
 *   - Bed flipped to 'Vacant' so the slot becomes rentable again.
 */

function makeMoveOutScenario(Carbon $today, ?Carbon $endDate = null): Lease
{
    $manager  = User::factory()->create(['role' => 'manager']);
    $landlord = User::factory()->create(['role' => 'landlord']);
    $tenant   = User::factory()->create(['role' => 'tenant']);

    // Property is owned by a landlord; manager attribution lives on the Unit
    $property = Property::factory()->create(['owner_id' => $landlord->user_id]);

    // Build unit directly (skip the factory's price-API call to keep tests fast/offline)
    $unit = Unit::create([
        'property_id'  => $property->property_id,
        'manager_id'   => $manager->user_id,
        'floor_number' => 1,
        'unit_number'  => '0101',
        'occupants'    => 'Co-ed',
        'living_area'  => 100,
        'furnishing'   => 'Bare',
        'bed_type'     => 'Single',
        'room_cap'     => 1,
        'price'        => 5000,
        'amenities'    => '[]',
    ]);

    $bed = Bed::create([
        'unit_id'    => $unit->unit_id,
        'bed_number' => 'B1',
        'status'     => 'Occupied',
    ]);

    return Lease::factory()->create([
        'tenant_id'        => $tenant->user_id,
        'bed_id'           => $bed->bed_id,
        'status'           => 'Active',
        'approval_status'  => 'approved',
        'term'             => 6,
        'start_date'       => $today->copy()->subMonths(2),
        'end_date'         => $endDate ?? $today->copy()->addMonths(4), // 4 months remaining = early
        'security_deposit' => 5000,
        'advance_amount'   => 0,
        'move_in'          => $today->copy()->subMonths(2),
        'move_out_initiated_at'     => $today->copy()->subDays(31), // 30-day notice satisfied
        'moveout_tenant_signature'  => 'data:image/png;base64,X',
        'moveout_owner_signature'   => 'data:image/png;base64,X',
        'moveout_manager_signature' => 'data:image/png;base64,X',
        'moveout_contract_agreed'   => true,
    ]);
}

function seedMoveOutPrerequisites(int $leaseId): void
{
    MoveOutInspection::create([
        'lease_id'  => $leaseId,
        'type'      => 'checklist',
        'item_name' => 'Walls',
        'condition' => 'good',
    ]);
    MoveOutInspection::create([
        'lease_id'          => $leaseId,
        'type'              => 'item_returned',
        'item_name'         => 'Key',
        'is_returned'       => true,
        'quantity'          => 1,
        'quantity_returned' => 1,
    ]);
}

it('forfeits the full deposit and frees the bed when a tenant leaves before contract end', function () {
    Carbon::setTestNow('2026-05-08');
    $today = Carbon::today();

    $lease = makeMoveOutScenario($today);
    seedMoveOutPrerequisites($lease->lease_id);

    $manager = User::where('role', 'manager')->first();

    Livewire::actingAs($manager)
        ->test(TenantDetail::class)
        ->set('currentTenantId', $lease->tenant_id)
        ->set('currentLeaseId', $lease->lease_id)
        ->call('confirmMoveOut');

    $lease->refresh();

    expect($lease->status)->toBe('Expired')
        ->and($lease->termination_reason)->toBe('early_termination')
        ->and((float) $lease->deposit_refund_amount)->toBe(0.0)
        ->and($lease->move_out->toDateString())->toBe($today->toDateString());

    expect(Bed::find($lease->bed_id)->status)->toBe('Vacant');

    $deductions = collect($lease->deposit_deductions);
    expect($deductions->pluck('label'))->toContain('Early Termination — Deposit Forfeiture');
});

it('refunds the full deposit when the tenant moves out on the contract end date', function () {
    Carbon::setTestNow('2026-05-08');
    $today = Carbon::today();

    // end_date = today → not early termination
    $lease = makeMoveOutScenario($today, endDate: $today->copy());
    seedMoveOutPrerequisites($lease->lease_id);

    $manager = User::where('role', 'manager')->first();

    Livewire::actingAs($manager)
        ->test(TenantDetail::class)
        ->set('currentTenantId', $lease->tenant_id)
        ->set('currentLeaseId', $lease->lease_id)
        ->call('confirmMoveOut');

    $lease->refresh();

    expect($lease->termination_reason)->toBe('normal_expiry')
        ->and((float) $lease->deposit_refund_amount)->toBeGreaterThanOrEqual(5000.0); // deposit + interest
});
