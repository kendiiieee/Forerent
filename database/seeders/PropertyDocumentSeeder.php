<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PropertyDocumentSeeder extends Seeder
{
    private const PHOTO_DIRECTORY = 'property_photos';

    private const DOCUMENT_DIRECTORY = 'property_documents';

    private const PHOTO_SETS = [
        'set-a' => [
            'property-a.png',
        ],
        'set-b' => [
            'property-b.png',
            'property-b-1.png',
        ],
        'set-c' => [
            'property-c.png',
        ],
    ];

    private const DOCUMENT_SETS = [
        'set-a' => [
            'business_permit' => ['business-permit.pdf',    'owner_manager'],
            'bir_2303' => ['bir-2303.pdf',           'owner_manager'],
            'inspection_report' => ['inspection-report.pdf', 'owner_manager'],
            'barangay_clearance' => ['barangay-clearance.pdf', 'owner_manager'],
            'occupancy_permit' => ['occupancy-permit.pdf',  'all'],
        ],
        'set-b' => [
            'business_permit' => ['business-permit.pdf',    'owner_manager'],
            'bir_2303' => ['bir-2303.pdf',           'owner_manager'],
            'inspection_report' => ['inspection-report.pdf', 'owner_manager'],
            'barangay_clearance' => ['barangay-clearance.pdf', 'owner_manager'],
            'occupancy_permit' => ['occupancy-permit.pdf',  'all'],
        ],
        'set-c' => [
            'business_permit' => ['business-permit.pdf',    'owner_manager'],
            'bir_2303' => ['bir-2303.pdf',           'owner_manager'],
            'inspection_report' => ['inspection-report.pdf', 'owner_manager'],
            'barangay_clearance' => ['barangay-clearance.pdf', 'owner_manager'],
            'occupancy_permit' => ['occupancy-permit.pdf',  'all'],
        ],
    ];

    public function run(): void
    {
        $properties = Property::all();
        $fallbackPhoto = public_path('office-building.png');

        if ($properties->isEmpty()) {
            $this->command->error('No properties found. Run PropertySeeder first.');

            return;
        }

        $photoSetKeys = array_keys(self::PHOTO_SETS);
        $documentSetKeys = array_keys(self::DOCUMENT_SETS);
        $documents = [];

        foreach ($properties as $index => $property) {
            // Cycle through sets if there are more properties than sets
            $photoSetKey = $photoSetKeys[$index % count($photoSetKeys)];
            $documentSetKey = $documentSetKeys[$index % count($documentSetKeys)];

            // --- Photos ---
            foreach (self::PHOTO_SETS[$photoSetKey] as $filename) {
                $storedPath = $this->seedPhotoFile($property->property_id, $filename, $fallbackPhoto);

                $documents[] = [
                    'property_id' => $property->property_id,
                    'category' => 'property_photo',
                    'file_path' => $storedPath,
                    'original_name' => $filename,
                    'visibility' => 'all',
                    'is_seed' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // --- Documents ---
            foreach (self::DOCUMENT_SETS[$documentSetKey] as $category => [$originalName, $visibility]) {
                $storedPath = $this->seedDocumentFile($property->property_id, $category, $originalName);

                $documents[] = [
                    'property_id' => $property->property_id,
                    'category' => $category,
                    'file_path' => $storedPath,
                    'original_name' => $originalName,
                    'visibility' => $visibility,
                    'is_seed' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($documents, 500) as $chunk) {
            PropertyDocument::insert($chunk);
        }

        $this->command->info('✅ Property documents seeded successfully!');
    }

    private function seedPhotoFile(int $propertyId, string $filename, string $fallbackPhoto): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $path = self::PHOTO_DIRECTORY.'/'.$propertyId.'-'.$base.'.png';

        if (File::exists($fallbackPhoto)) {
            Storage::disk('public')->put($path, File::get($fallbackPhoto));
        } else {
            Storage::disk('public')->put($path, '');
        }

        return $path;
    }

    private function seedDocumentFile(int $propertyId, string $category, string $originalName): string
    {
        $safeCategory = str_replace('_', '-', $category);
        $path = self::DOCUMENT_DIRECTORY.'/'.$propertyId.'-'.$safeCategory.'.pdf';

        // If a real document exists in the repository under database/data/property_documents/
        // use that file. Naming convention: {propertyId}-{category}.pdf (category uses hyphens)
        $sourcePath = database_path('data/property_documents/'.$propertyId.'-'.$safeCategory.'.pdf');

        if (File::exists($sourcePath)) {
            Storage::disk('public')->put($path, File::get($sourcePath));
        } else {
            $pdfText = ucfirst(str_replace('_', ' ', $category)).' - '.$originalName;
            Storage::disk('public')->put($path, $this->buildSimplePdf($pdfText));
        }

        return $path;
    }

    private function buildSimplePdf(string $text): string
    {
        $escapedText = str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', ' ', ' '],
            $text
        );

        $stream = "BT\n/F1 16 Tf\n72 720 Td\n({$escapedText}) Tj\nET";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n",
            "4 0 obj\n<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream\nendobj\n",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
