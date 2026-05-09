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
        Schema::create('leases', function (Blueprint $table) {
            $table->id('lease_id')->primary();
            $table->foreignId('tenant_id')
                ->constrained('users', 'user_id')
                ->onDelete('cascade');
            $table->foreignId('bed_id')
                ->constrained('beds', 'bed_id')
                ->onDelete('cascade');
            $table->enum('status', ['Active', 'Expired']);
            $table->integer('term');
            $table->enum('shift', ['Night', 'Morning']);
            $table->boolean('auto_renew');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('contract_rate', 8, 2);
            $table->decimal('advance_amount', 8, 2); //
            $table->decimal('security_deposit', 8, 2); // <-- Auto-generate receipt on Lease Creation
            $table->date('move_in');
            $table->date('move_out')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
