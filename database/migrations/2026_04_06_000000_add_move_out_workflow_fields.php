<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->timestamp('move_out_initiated_at')->nullable()->after('move_out');
            $table->string('moveout_contract_status', 30)->default('draft')->after('moveout_contract_agreed');
        });

        Schema::table('move_out_inspections', function (Blueprint $table) {
            $table->decimal('repair_cost', 10, 2)->nullable()->after('remarks');
            $table->decimal('replacement_cost', 10, 2)->nullable()->after('repair_cost');
            $table->boolean('is_returned')->default(false)->after('tenant_confirmed');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn(['move_out_initiated_at', 'moveout_contract_status']);
        });

        Schema::table('move_out_inspections', function (Blueprint $table) {
            $table->dropColumn(['repair_cost', 'replacement_cost', 'is_returned']);
        });
    }
};
