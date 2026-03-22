<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_bills', function (Blueprint $table) {
            $table->id('utility_bill_id')->primary();
            $table->foreignId('unit_id')
                ->constrained('units', 'unit_id')
                ->onDelete('cascade');
            $table->enum('utility_type', ['electricity', 'water']);
            $table->date('billing_period'); // first day of the month the bill covers
            $table->decimal('total_amount', 10, 2);
            $table->unsignedInteger('tenant_count');
            $table->decimal('per_tenant_amount', 10, 2);
            $table->foreignId('entered_by')
                ->constrained('users', 'user_id')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_bills');
    }
};
