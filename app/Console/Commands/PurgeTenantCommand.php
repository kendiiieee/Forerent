<?php

namespace App\Console\Commands;

use App\Models\Bed;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeTenantCommand extends Command
{
    protected $signature = 'tenant:purge {email : Email of the tenant to purge}';

    protected $description = 'Force-delete a tenant and all related leases, billings, transactions, and free their bed.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $tenant = User::withTrashed()
            ->where('email', $email)
            ->where('role', 'tenant')
            ->first();

        if (!$tenant) {
            $this->error("No tenant found with email: {$email}");
            return self::FAILURE;
        }

        $leases = $tenant->leases()->withTrashed()->with(['billings.items', 'billings.transactions'])->get();

        $this->info("About to permanently delete:");
        $this->line(" - User #{$tenant->user_id} ({$tenant->first_name} {$tenant->last_name} <{$tenant->email}>)");
        foreach ($leases as $lease) {
            $this->line("   - Lease #{$lease->lease_id} (bed_id={$lease->bed_id}, status={$lease->status})");
            foreach ($lease->billings as $billing) {
                $this->line("       - Billing #{$billing->billing_id} ({$billing->billing_type}, {$billing->status}, ₱{$billing->amount}) — items: {$billing->items->count()}, txns: {$billing->transactions->count()}");
            }
        }

        if (!$this->confirm('Proceed with permanent deletion? This cannot be undone.', false)) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($tenant, $leases) {
            foreach ($leases as $lease) {
                foreach ($lease->billings as $billing) {
                    $billing->items()->withTrashed()->forceDelete();
                    $billing->transactions()->withTrashed()->forceDelete();
                    $billing->forceDelete();
                }

                if ($lease->bed_id) {
                    Bed::where('bed_id', $lease->bed_id)->update(['status' => 'Vacant']);
                }

                $lease->forceDelete();
            }

            $tenant->forceDelete();
        });

        $this->info("Tenant {$email} and all related records have been purged. Beds freed.");
        return self::SUCCESS;
    }
}
