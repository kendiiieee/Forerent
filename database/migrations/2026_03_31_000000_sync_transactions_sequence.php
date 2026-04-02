<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(
            "SELECT setval(pg_get_serial_sequence('transactions', 'transaction_id'), GREATEST(COALESCE(MAX(transaction_id), 0) + 1, 1), false) FROM transactions"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this migration only normalizes sequence state.
    }
};
