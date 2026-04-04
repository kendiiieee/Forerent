<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_id');
            $table->unsignedBigInteger('lease_id');
            $table->unsignedBigInteger('tenant_id');
            $table->enum('payment_method', ['GCash', 'Maya', 'Bank Transfer', 'Cash']);
            $table->string('reference_number')->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->string('proof_image');
            $table->enum('status', ['Pending', 'Confirmed', 'Rejected'])->default('Pending');
            $table->text('reject_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('billing_id')->references('billing_id')->on('billings')->onDelete('cascade');
            $table->foreign('lease_id')->references('lease_id')->on('leases')->onDelete('cascade');
            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
