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
        // Create Units Table
        Schema::create('units', function (Blueprint $table) {
            $table->id('unit_id')->primary();
            $table->foreignId('property_id')
                ->constrained('properties', 'property_id')
                ->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->integer('floor_number');
            $table->string('unit_number');
            $table->enum('occupants', ['Male', 'Female', 'Co-ed'])->default('Co-ed');
            $table->double('living_area')->nullable();
            $table->enum('furnishing', ['Bare', 'Semi-furnished', 'Fully Furnished'])->nullable();
            $table->enum('bed_type', ['Single', 'Bunk'])->nullable();
            $table->integer('room_cap');
            $table->string('room_type')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('amenities')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
