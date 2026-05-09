<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->timestamp('termination_notice_issued_at')->nullable()->after('move_out_initiated_at');
            $table->date('vacate_by_date')->nullable()->after('termination_notice_issued_at');
            $table->unsignedBigInteger('termination_notice_violation_id')->nullable()->after('vacate_by_date');
            $table->string('termination_notice_path')->nullable()->after('termination_notice_violation_id');

            $table->foreign('termination_notice_violation_id')
                ->references('violation_id')->on('violations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropForeign(['termination_notice_violation_id']);
            $table->dropColumn([
                'termination_notice_issued_at',
                'vacate_by_date',
                'termination_notice_violation_id',
                'termination_notice_path',
            ]);
        });
    }
};
