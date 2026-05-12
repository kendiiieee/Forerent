<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('terms_and_policy_accepted')->default(false)->after('email_verified_at');
            $table->timestamp('terms_and_policy_accepted_at')->nullable()->after('terms_and_policy_accepted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('terms_and_policy_accepted');
            $table->dropColumn('terms_and_policy_accepted_at');
        });
    }
};
