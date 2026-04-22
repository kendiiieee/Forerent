<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rental_eligibility', ['eligible', 'blocked'])
                ->default('eligible')
                ->after('terms_accepted_at');
            $table->text('eligibility_notes')->nullable()->after('rental_eligibility');
            $table->timestamp('eligibility_changed_at')->nullable()->after('eligibility_notes');
            $table->unsignedBigInteger('eligibility_changed_by')->nullable()->after('eligibility_changed_at');

            $table->foreign('eligibility_changed_by')
                ->references('user_id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['eligibility_changed_by']);
            $table->dropColumn([
                'rental_eligibility',
                'eligibility_notes',
                'eligibility_changed_at',
                'eligibility_changed_by',
            ]);
        });
    }
};
