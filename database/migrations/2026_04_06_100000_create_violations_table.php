<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id('violation_id');
            $table->unsignedBigInteger('lease_id');
            $table->unsignedBigInteger('reported_by');
            $table->string('violation_number')->unique();
            $table->tinyInteger('offense_number');
            $table->string('category');
            $table->text('description');
            $table->text('evidence_path')->nullable();
            $table->string('severity'); // minor, major, serious
            $table->string('penalty_type'); // written_warning, fine, lease_termination
            $table->decimal('fine_amount', 10, 2)->nullable();
            $table->string('status')->default('Issued'); // Issued, Acknowledged, Resolved
            $table->date('violation_date');
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('tenant_acknowledged_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('billing_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lease_id')->references('lease_id')->on('leases')->onDelete('cascade');
            $table->foreign('reported_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('billing_item_id')->references('billing_item_id')->on('billing_items')->onDelete('set null');

            $table->index(['lease_id', 'violation_date']);
            $table->index(['lease_id', 'offense_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
