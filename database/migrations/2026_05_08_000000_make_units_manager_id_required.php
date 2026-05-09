<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $orphans = DB::table('units')
            ->whereNull('manager_id')
            ->orderBy('property_id')
            ->orderBy('unit_id')
            ->get(['unit_id', 'property_id']);

        $managerIds = DB::table('users')
            ->where('role', 'manager')
            ->whereNull('deleted_at')
            ->orderBy('user_id')
            ->pluck('user_id')
            ->all();

        // Only require managers when there are orphan units to backfill.
        // On a fresh install (empty units), seeders run after migrations, so
        // there's nothing to assign — just enforce NOT NULL and exit.
        if ($orphans->isNotEmpty() && empty($managerIds)) {
            throw new RuntimeException(
                'Cannot make units.manager_id required: orphan units exist but no users with role=manager. '
                .'Seed or create at least one manager before running this migration.'
            );
        }

        $rrIndex = 0;
        $managerCount = count($managerIds);

        foreach ($orphans as $orphan) {
            $localManagers = DB::table('units')
                ->where('property_id', $orphan->property_id)
                ->whereNotNull('manager_id')
                ->pluck('manager_id')
                ->unique()
                ->values()
                ->all();

            if (! empty($localManagers)) {
                $loadCounts = DB::table('units')
                    ->where('property_id', $orphan->property_id)
                    ->whereIn('manager_id', $localManagers)
                    ->select('manager_id', DB::raw('COUNT(*) AS load'))
                    ->groupBy('manager_id')
                    ->pluck('load', 'manager_id')
                    ->all();

                foreach ($localManagers as $mgrId) {
                    $loadCounts[$mgrId] = $loadCounts[$mgrId] ?? 0;
                }
                asort($loadCounts);
                $managerId = (int) array_key_first($loadCounts);
            } else {
                $managerId = $managerIds[$rrIndex % $managerCount];
                $rrIndex++;
            }

            DB::table('units')
                ->where('unit_id', $orphan->unit_id)
                ->update(['manager_id' => $managerId, 'updated_at' => now()]);
        }

        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        DB::statement('ALTER TABLE units MODIFY COLUMN manager_id BIGINT UNSIGNED NOT NULL');

        Schema::table('units', function (Blueprint $table) {
            $table->foreign('manager_id')
                ->references('user_id')->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        DB::statement('ALTER TABLE units MODIFY COLUMN manager_id BIGINT UNSIGNED NULL');

        Schema::table('units', function (Blueprint $table) {
            $table->foreign('manager_id')
                ->references('user_id')->on('users')
                ->nullOnDelete();
        });
    }
};
