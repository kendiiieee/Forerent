<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->string('move_in_payment_method')->nullable()->after('reservation_fee_paid');
            $table->string('move_in_or_number')->nullable()->after('move_in_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn(['move_in_payment_method', 'move_in_or_number']);
        });
    }
};
