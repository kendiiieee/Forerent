Place real property document PDFs here to have the seeder copy them into public storage during seeding.

Naming convention:
- {propertyId}-{category}.pdf
- category should use hyphens for separators (e.g., business-permit, bir-2303, inspection-report, barangay-clearance, occupancy-permit)

Example filenames:
- 1-business-permit.pdf
- 2-occupancy-permit.pdf

After adding files run:

```bash
# inside the PHP container
php artisan db:seed --class=Database\\Seeders\\PropertyDocumentSeeder
```

This will copy any matching files into `storage/app/public/property_documents/` and update the seeded records to point to them.