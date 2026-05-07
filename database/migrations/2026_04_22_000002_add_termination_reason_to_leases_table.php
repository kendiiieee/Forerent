<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->enum('termination_reason', [
                'normal_expiry',
                'non_payment',
                'early_termination',
                'violation',
            ])->nullable()->after('move_out');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('termination_reason');
        });
    }
};
