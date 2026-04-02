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
        Schema::create('billings', function (Blueprint $table) {
            $table->id('billing_id')->primary();
            $table->foreignId('lease_id')
                ->constrained('leases', 'lease_id')
                ->onDelete('cascade');
            $table->date('billing_date');
            $table->date('next_billing');
            $table->decimal('to_pay', 8, 2);
            $table->decimal('amount', 8, 2);
            // Only Visible to manager or tenant when status is unpaid or overdue
            // Chech daily using job-schedule to update status based on current date
            $table->enum('status', ['Unpaid', 'Overdue', 'Paid']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
