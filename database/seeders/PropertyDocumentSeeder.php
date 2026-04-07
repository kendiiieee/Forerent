<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Database\Seeder;

class PropertyDocumentSeeder extends Seeder
{
    private const PHOTO_SETS = [
        'set-a' => [
            'property-a.jpg',
        ],
        'set-b' => [
            'property-b.jpg',
            'property-b-1.jpg',
        ],
        'set-c' => [
            'property-c.jpg',
        ],
    ];

    private const DOCUMENT_SETS = [
        'set-a' => [
            'business_permit'    => ['business-permit.pdf',    'owner_manager'],
            'bir_2303'           => ['bir-2303.pdf',           'owner_manager'],
            'inspection_report'  => ['inspection-report.pdf', 'owner_manager'],
            'barangay_clearance' => ['barangay-clearance.pdf','owner_manager'],
            'occupancy_permit'   => ['occupancy-permit.pdf',  'all'],
        ],
        'set-b' => [
            'business_permit'    => ['business-permit.pdf',    'owner_manager'],
            'bir_2303'           => ['bir-2303.pdf',           'owner_manager'],
            'inspection_report'  => ['inspection-report.pdf', 'owner_manager'],
            'barangay_clearance' => ['barangay-clearance.pdf','owner_manager'],
            'occupancy_permit'   => ['occupancy-permit.pdf',  'all'],
        ],
        'set-c' => [
            'business_permit'    => ['business-permit.pdf',    'owner_manager'],
            'bir_2303'           => ['bir-2303.pdf',           'owner_manager'],
            'inspection_report'  => ['inspection-report.pdf', 'owner_manager'],
            'barangay_clearance' => ['barangay-clearance.pdf','owner_manager'],
            'occupancy_permit'   => ['occupancy-permit.pdf',  'all'],
        ],
    ];

    public function run(): void
    {
        $properties = Property::all();

        if ($properties->isEmpty()) {
            $this->command->error('No properties found. Run PropertySeeder first.');
            return;
        }

        $photoSetKeys    = array_keys(self::PHOTO_SETS);
        $documentSetKeys = array_keys(self::DOCUMENT_SETS);
        $documents       = [];

        foreach ($properties as $index => $property) {
            // Cycle through sets if there are more properties than sets
            $photoSetKey    = $photoSetKeys[$index % count($photoSetKeys)];
            $documentSetKey = $documentSetKeys[$index % count($documentSetKeys)];

            // --- Photos ---
            foreach (self::PHOTO_SETS[$photoSetKey] as $filename) {
                $documents[] = [
                    'property_id'   => $property->property_id,
                    'category'      => 'property_photo',
                    'file_path'     => "property-photos/{$filename}", // <-- fixed
                    'original_name' => $filename,
                    'visibility'    => 'all',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            // --- Documents ---
            foreach (self::DOCUMENT_SETS[$documentSetKey] as $category => [$originalName, $visibility]) {
                $documents[] = [
                    'property_id'   => $property->property_id,
                    'category'      => $category,
                    'file_path'     => "property-documents/{$originalName}", // <-- fixed
                    'original_name' => $originalName,
                    'visibility'    => $visibility,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        foreach (array_chunk($documents, 500) as $chunk) {
            PropertyDocument::insert($chunk);
        }

        $this->command->info('✅ Property documents seeded successfully!');
    }
}
