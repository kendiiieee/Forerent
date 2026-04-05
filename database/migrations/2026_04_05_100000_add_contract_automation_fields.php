<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Leases: contract status machine + deposit refund ---
        Schema::table('leases', function (Blueprint $table) {
            $table->string('contract_status', 30)->default('draft')
                ->after('status')
                ->comment('draft|pending_signatures|pending_tenant|pending_owner|executed');

            $table->decimal('deposit_refund_amount', 10, 2)->nullable()->after('deposit_refund_account');
            $table->json('deposit_deductions')->nullable()->after('deposit_refund_amount')
                ->comment('JSON breakdown of deductions from deposit');
        });

        // --- Inspections: tenant dispute workflow ---
        Schema::table('move_in_inspections', function (Blueprint $table) {
            $table->string('dispute_status', 20)->default('none')
                ->after('tenant_confirmed')
                ->comment('none|disputed|resolved');
            $table->text('dispute_remarks')->nullable()->after('dispute_status');
            $table->timestamp('disputed_at')->nullable()->after('dispute_remarks');
            $table->text('resolution_remarks')->nullable()->after('disputed_at');
            $table->timestamp('resolved_at')->nullable()->after('resolution_remarks');
        });

        Schema::table('move_out_inspections', function (Blueprint $table) {
            $table->string('dispute_status', 20)->default('none')
                ->after('tenant_confirmed')
                ->comment('none|disputed|resolved');
            $table->text('dispute_remarks')->nullable()->after('dispute_status');
            $table->timestamp('disputed_at')->nullable()->after('dispute_remarks');
            $table->text('resolution_remarks')->nullable()->after('disputed_at');
            $table->timestamp('resolved_at')->nullable()->after('resolution_remarks');
        });

        // --- Properties: per-property contract template settings ---
        Schema::table('properties', function (Blueprint $table) {
            $table->json('contract_settings')->nullable()->after('prop_description')
                ->comment('Per-property customization: house_rules, inclusions, exclusions, policies');
        });

        // --- Contract audit trail ---
        Schema::create('contract_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 50);
            $table->string('field_changed')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('lease_id')->references('lease_id')->on('leases')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
            $table->index(['lease_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_audit_logs');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('contract_settings');
        });

        Schema::table('move_out_inspections', function (Blueprint $table) {
            $table->dropColumn(['dispute_status', 'dispute_remarks', 'disputed_at', 'resolution_remarks', 'resolved_at']);
        });

        Schema::table('move_in_inspections', function (Blueprint $table) {
            $table->dropColumn(['dispute_status', 'dispute_remarks', 'disputed_at', 'resolution_remarks', 'resolved_at']);
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn(['contract_status', 'deposit_refund_amount', 'deposit_deductions']);
        });
    }
};
