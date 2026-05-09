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
        DB::statement('ALTER TABLE billings DROP CONSTRAINT IF EXISTS billings_billing_type_check');
        DB::statement("ALTER TABLE billings ADD CONSTRAINT billings_billing_type_check
            CHECK (billing_type IN ('monthly', 'move_in', 'move_out', 'charges'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE billings DROP CONSTRAINT IF EXISTS billings_billing_type_check');
        DB::statement("ALTER TABLE billings ADD CONSTRAINT billings_billing_type_check
            CHECK (billing_type IN ('monthly', 'move_in', 'move_out'))");
    }
};
