<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Extend the billings.billing_type enum to allow 'charges' for one-off
 * standalone billings (e.g. violation fines created when a tenant has no
 * active monthly billing to attach the fine to).
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE billings DROP CONSTRAINT IF EXISTS billings_billing_type_check');
            DB::statement("ALTER TABLE billings ADD CONSTRAINT billings_billing_type_check
                CHECK (billing_type IN ('monthly', 'move_in', 'move_out', 'charges'))");

            return;
        }

        DB::statement("ALTER TABLE billings MODIFY COLUMN billing_type ENUM('monthly', 'move_in', 'move_out', 'charges') NOT NULL DEFAULT 'monthly'");
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE billings DROP CONSTRAINT IF EXISTS billings_billing_type_check');
            DB::statement("ALTER TABLE billings ADD CONSTRAINT billings_billing_type_check
                CHECK (billing_type IN ('monthly', 'move_in', 'move_out'))");

            return;
        }

        DB::statement("ALTER TABLE billings MODIFY COLUMN billing_type ENUM('monthly', 'move_in', 'move_out') NOT NULL DEFAULT 'monthly'");
    }
};
