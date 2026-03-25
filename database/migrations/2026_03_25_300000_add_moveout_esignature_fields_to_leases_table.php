<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->string('moveout_tenant_signature')->nullable()->after('deposit_refund_account');
            $table->timestamp('moveout_tenant_signed_at')->nullable()->after('moveout_tenant_signature');
            $table->string('moveout_tenant_signed_ip')->nullable()->after('moveout_tenant_signed_at');
            $table->string('moveout_owner_signature')->nullable()->after('moveout_tenant_signed_ip');
            $table->timestamp('moveout_owner_signed_at')->nullable()->after('moveout_owner_signature');
            $table->string('moveout_owner_signed_ip')->nullable()->after('moveout_owner_signed_at');
            $table->boolean('moveout_contract_agreed')->default(false)->after('moveout_owner_signed_ip');
            $table->string('moveout_signed_contract_path')->nullable()->after('moveout_contract_agreed');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn([
                'moveout_tenant_signature',
                'moveout_tenant_signed_at',
                'moveout_tenant_signed_ip',
                'moveout_owner_signature',
                'moveout_owner_signed_at',
                'moveout_owner_signed_ip',
                'moveout_contract_agreed',
                'moveout_signed_contract_path',
            ]);
        });
    }
};
