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
        // Add this check to prevent the "Duplicate column" error on Render
        if (!Schema::hasColumn('units', 'room_type')) {
            Schema::table('units', function (Blueprint $table) {
                $table->string('room_type')->nullable()->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('units', 'room_type')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('room_type');
            });
        }
    }
};