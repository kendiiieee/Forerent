<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Pre-hashed password shared across all seeded users.
     */
    protected string $hashedPassword;

    public function run(): void
    {
        $this->hashedPassword = Hash::make('password');

        $this->seedNamedUsers();
        $this->seedBulkUsers();
    }

    private function seedNamedUsers(): void
    {
        $namedUsers = [
            [
                'first_name'            => 'Tricia',
                'last_name'             => 'Tenant',
                'email'                 => 'tenant@example.com',
                'role'                  => 'tenant',
                'government_id_type'    => 'National ID',
                'government_id_number'  => 'PSN-20241234567',
                'government_id_image'   => 'government-ids/sample-id-tricia.jpg',
            ],
            [
                'first_name'            => 'Tanya',
                'last_name'             => 'Torres',
                'email'                 => 'tenant2@example.com',
                'role'                  => 'tenant',
                'government_id_type'    => 'Passport',
                'government_id_number'  => 'P-987654321',
                'government_id_image'   => 'government-ids/sample-id-tanya.jpg',
            ],
            [
                'first_name'            => 'Marcus',
                'last_name'             => 'Manager',
                'email'                 => 'manager@example.com',
                'role'                  => 'manager',
                'government_id_type'    => "Driver's License",
                'government_id_number'  => 'N01-12-345678',
                'government_id_image'   => 'government-ids/sample-id-marcus.jpg',
            ],
            [
                'first_name'            => 'Mia',
                'last_name'             => 'Martinez',
                'email'                 => 'manager2@example.com',
                'role'                  => 'manager',
                'government_id_type'    => 'UMID',
                'government_id_number'  => 'CRN-0012-3456789',
                'government_id_image'   => 'government-ids/sample-id-mia.jpg',
            ],
            [
                'first_name'            => 'Liam',
                'last_name'             => 'Landlord',
                'email'                 => 'landlord@example.com',
                'role'                  => 'landlord',
                'company_school'        => 'ABC COMPANY',
                'government_id_type'    => 'Passport',
                'government_id_number'  => 'EB-1234567',
                'government_id_image'   => 'government-ids/sample-id-liam.jpg',
            ],
        ];

        foreach ($namedUsers as $userData) {
            User::factory()->create(array_merge(
                ['password' => $this->hashedPassword],
                $userData
            ));
        }
    }

    /**
     * Pool of government IDs drawn from the named users above.
     * Each entry is [type, number, image].
     */
    private const GOVERNMENT_IDS = [
        ['National ID',      'PSN-20241234567',  'government-ids/sample-id-tricia.jpg'],
        ['Passport',         'P-987654321',       'government-ids/sample-id-tanya.jpg'],
        ["Driver's License", 'N01-12-345678',     'government-ids/sample-id-marcus.jpg'],
        ['UMID',             'CRN-0012-3456789',  'government-ids/sample-id-mia.jpg'],
        ['Passport',         'EB-1234567',        'government-ids/sample-id-liam.jpg'],
    ];

    private function seedBulkUsers(): void
    {
        $bulkGroups = [
            ['role' => 'tenant',  'count' => 36],
            ['role' => 'manager', 'count' => 3],
        ];

        foreach ($bulkGroups as ['role' => $role, 'count' => $count]) {
            for ($i = 0; $i < $count; $i++) {
                [$idType, $idNumber, $idImage] = self::GOVERNMENT_IDS[array_rand(self::GOVERNMENT_IDS)];

                User::factory()->create([
                    'role'                  => $role,
                    'password'              => $this->hashedPassword,
                    'government_id_type'    => $idType,
                    'government_id_number'  => $idNumber,
                    'government_id_image'   => $idImage,
                ]);
            }
        }
    }
}
