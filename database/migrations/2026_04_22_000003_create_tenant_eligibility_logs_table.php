<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_eligibility_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->unsignedBigInteger('lease_id')->nullable();
            $table->enum('old_status', ['eligible', 'blocked']);
            $table->enum('new_status', ['eligible', 'blocked']);
            $table->text('reason');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('changed_by')->references('user_id')->on('users')->nullOnDelete();
            $table->foreign('lease_id')->references('lease_id')->on('leases')->nullOnDelete();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_eligibility_logs');
    }
};
