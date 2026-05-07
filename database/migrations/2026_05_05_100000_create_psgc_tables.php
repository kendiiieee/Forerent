<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('psgc_code', 20)->unique();
            $table->string('name', 120);
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('psgc_code', 20)->unique();
            $table->string('name', 120);
            $table->foreignId('province_id')
                ->constrained('provinces')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->index(['province_id', 'name']);
        });

        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('psgc_code', 20)->unique();
            $table->string('name', 120);
            $table->foreignId('city_id')
                ->constrained('cities')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->index(['city_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
    }
};
