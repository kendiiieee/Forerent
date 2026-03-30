<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE maintenance_requests ALTER COLUMN image_path TYPE TEXT');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE maintenance_requests MODIFY image_path TEXT NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE maintenance_requests ALTER COLUMN image_path TYPE VARCHAR(255)');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE maintenance_requests MODIFY image_path VARCHAR(255) NULL');
        }
    }
};
