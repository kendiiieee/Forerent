<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('units', 'living_area')) {
            Schema::table('units', function (Blueprint $table) {
                $table->double('living_area')->nullable()->after('occupants');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('units', 'living_area')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('living_area');
            });
        }
    }
};
