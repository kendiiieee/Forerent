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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id('request_id')->primary();
            $table->foreignId('lease_id')
                ->constrained('leases', 'lease_id')
                ->onDelete('cascade');
            $table->enum('status', ['Pending', 'Ongoing', 'Completed']);
            $table->string('logged_by');
            $table->string('ticket_number');
            $table->date('log_date');
            $table->string('problem');
            $table->enum('urgency', ['Level 1', 'Level 2', 'Level 3', 'Level 4']);
            $table->enum('category', [
                'Plumbing',
                'Electrical',
                'Structural',
                'Appliance',
                'Pest Control'
            ]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
