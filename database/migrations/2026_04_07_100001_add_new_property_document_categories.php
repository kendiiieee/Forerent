<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $allValues = [
            'property_photo',
            'business_permit',
            'bir_2303',
            'inspection_report',
            'barangay_clearance',
            'occupancy_permit',
            'title_tct',
            'tax_declaration',
            'transfer_certificate',
        ];

        $this->syncCategoryDefinition($allValues);
    }

    public function down(): void
    {
        DB::table('property_documents')
            ->whereIn('category', ['title_tct', 'tax_declaration', 'transfer_certificate'])
            ->delete();

        $originalValues = [
            'property_photo',
            'business_permit',
            'bir_2303',
            'inspection_report',
            'barangay_clearance',
            'occupancy_permit',
        ];

        $this->syncCategoryDefinition($originalValues);
    }

    private function syncCategoryDefinition(array $values): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL emulates enum columns using a varchar + CHECK constraint.
            $valuesList = implode(', ', array_map(fn ($v) => "'{$v}'::character varying", $values));

            DB::statement('ALTER TABLE property_documents DROP CONSTRAINT IF EXISTS property_documents_category_check');
            DB::statement("ALTER TABLE property_documents ADD CONSTRAINT property_documents_category_check CHECK (category::text = ANY (ARRAY[{$valuesList}]::text[]))");

            return;
        }

        if ($driver === 'mysql') {
            $valuesList = implode(', ', array_map(fn ($v) => "'{$v}'", $values));

            DB::statement("ALTER TABLE property_documents MODIFY COLUMN category ENUM({$valuesList}) NOT NULL");
        }
    }
};
