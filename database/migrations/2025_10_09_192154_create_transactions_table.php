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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id')->primary();
            $table->foreignId('billing_id')
                ->nullable()
                ->constrained('billings', 'billing_id');
            $table->string('name')->nullable();
            $table->string('reference_number');
            // For forecasting training feature, check if value is cash inflow or outflow //
            $table->enum('transaction_type', ['Debit', 'Credit'])->default('Credit');
            $table->enum('category', ['Rent Payment', 'Deposit', 'Advance', 'Maintenance', 'Vendor Payment']);
            $table->date('transaction_date');
            $table->decimal('amount', 12, 2);
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
