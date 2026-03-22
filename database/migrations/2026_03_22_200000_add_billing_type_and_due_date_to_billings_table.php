<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->enum('billing_type', ['monthly', 'move_in', 'move_out'])->default('monthly')->after('lease_id');
            $table->date('due_date')->nullable()->after('next_billing');
            $table->decimal('previous_balance', 10, 2)->default(0.00)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn(['billing_type', 'due_date', 'previous_balance']);
        });
    }
};
