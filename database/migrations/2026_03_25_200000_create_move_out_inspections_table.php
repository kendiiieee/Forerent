<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('move_out_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id');
            $table->enum('type', ['checklist', 'item_returned']);
            $table->string('item_name');
            $table->enum('condition', ['good', 'damaged', 'missing'])->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('tenant_confirmed')->default(false);
            $table->timestamps();

            $table->foreign('lease_id')->references('lease_id')->on('leases')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('move_out_inspections');
    }
};
