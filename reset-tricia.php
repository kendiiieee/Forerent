<!-- <?php

$user = App\Models\User::where('email', 'tenant@example.com')->first();

if (!$user) {
    echo "User tenant@example.com not found.\n";
    return;
}

$leases = App\Models\Lease::where('tenant_id', $user->user_id)->get();

foreach ($leases as $lease) {
    $lease->fill([
        'tenant_signature' => null,
        'tenant_signed_at' => null,
        'tenant_signed_ip' => null,
        'owner_signature' => null,
        'owner_signed_at' => null,
        'owner_signed_ip' => null,
        'manager_signature' => null,
        'manager_signed_at' => null,
        'manager_signed_ip' => null,
        'signed_contract_path' => null,
        'contract_agreed' => false,
        'contract_status' => 'pending_tenant',
        'moveout_tenant_signature' => null,
        'moveout_tenant_signed_at' => null,
        'moveout_tenant_signed_ip' => null,
        'moveout_owner_signature' => null,
        'moveout_owner_signed_at' => null,
        'moveout_owner_signed_ip' => null,
        'moveout_manager_signature' => null,
        'moveout_manager_signed_at' => null,
        'moveout_manager_signed_ip' => null,
        'moveout_contract_agreed' => false,
        'moveout_contract_status' => 'draft',
        'moveout_signed_contract_path' => null,
    ])->save();
}

echo "Cleared signatures on {$leases->count()} lease(s) for {$user->email} (id {$user->user_id}).\n"; ?>
