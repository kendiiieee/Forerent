<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Early-vacate request flow on top of a Notice of Termination.
 *
 * After a Notice is issued the tenant has the full notice period to vacate.
 * If they want to leave earlier, the manager files an early-vacate request
 * and the tenant must accept (or decline) before the move-out gate unlocks
 * ahead of the original vacate-by date.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->timestamp('early_vacate_requested_at')->nullable()->after('vacate_by_date');
            $table->date('early_vacate_proposed_date')->nullable()->after('early_vacate_requested_at');
            $table->text('early_vacate_request_reason')->nullable()->after('early_vacate_proposed_date');
            // pending_tenant | accepted | declined  (null = no request on file)
            $table->string('early_vacate_status', 32)->nullable()->after('early_vacate_request_reason');
            $table->unsignedBigInteger('early_vacate_requested_by')->nullable()->after('early_vacate_status');
            $table->timestamp('early_vacate_responded_at')->nullable()->after('early_vacate_requested_by');
            $table->text('early_vacate_response_note')->nullable()->after('early_vacate_responded_at');

            $table->foreign('early_vacate_requested_by')
                ->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropForeign(['early_vacate_requested_by']);
            $table->dropColumn([
                'early_vacate_requested_at',
                'early_vacate_proposed_date',
                'early_vacate_request_reason',
                'early_vacate_status',
                'early_vacate_requested_by',
                'early_vacate_responded_at',
                'early_vacate_response_note',
            ]);
        });
    }
};
