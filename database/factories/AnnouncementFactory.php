<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    private ?string $authorRole = null;

    public function definition(): array
    {
        $authorId = $this->getAuthorId();
        $author = User::find($authorId);
        $property = $this->getProperty();
        $propertyName = $property->building_name;

        // Generate title and description based on context
        [$headline, $details] = $this->generateHeadlineAndDetails($propertyName);

        $date = $this->faker->dateTimeBetween('-6 months', 'now');


        return [
            'author_id'      => $authorId,
            'property_id'    => $property->property_id,
            'headline'       => $headline,
            'details'        => $details,
            'sender_role'    => $author->role,
            'recipient_role' => $this->determineRecipientRole($author->role),
            'notification_date' => $date->format('Y-m-d'),
            'created_at'     => $date,
            'updated_at'     => $date,
        ];
    }

    public function authorRole(string $role): self
    {
        $factory = clone $this;
        $factory->authorRole = $role;
        return $factory;
    }

    private function getAuthorId(): int
    {
        $query = User::query();
        if ($this->authorRole) {
            $query->where('role', $this->authorRole);
        } else {
            $query->whereIn('role', ['landlord', 'manager']);
        }

        $author = $query->inRandomOrder()->first();

        if (!$author) {
            $author = User::factory()->create([
                'role' => $this->authorRole ?? 'landlord',
            ]);
        }

        return $author->user_id;
    }

    private function determineRecipientRole(string $authorRole): string
    {
        return match ($authorRole) {
            'landlord' => 'manager',
            'manager'  => 'tenant',
            default    => 'tenant',
        };
    }

    private function getProperty(): Property
    {
        $property = Property::inRandomOrder()->first();

        if (!$property) {
            $property = Property::factory()->create();
        }

        return $property;
    }

    private function generateHeadlineAndDetails(string $propertyName): array
    {
        $headline = [
            'Maintenance' => [
                'Scheduled Maintenance Notice',
                'Laundry Room Maintenance',
                'Pool Area Cleaning',
                'Electrical System Upgrade',
                'HVAC Inspection Notice'
            ],
            'Alerts' => [
                'Water Outage Alert',
                'Parking Lot Closure',
                'Security Upgrade Announcement',
                'Fire Drill Reminder',
                'Pest Control Schedule'
            ],
            'Community' => [
                'New Community Rules',
                'Tenant Meeting Reminder',
                'Recycling Program Update',
                'Community Event Announcement',
                'Welcome New Tenants'
            ]
        ];

        $category = $this->faker->randomElement(array_keys($headline));
        $headline = $this->faker->randomElement($headline[$category]);

        $details = match ($headline) {
            'Scheduled Maintenance Notice' => "$propertyName: General maintenance will be conducted on all units on " . $this->faker->dateTimeBetween('now', '+2 weeks')->format('F j, Y') . ". Expect temporary service interruptions.",
            'Laundry Room Maintenance' => "$propertyName: The laundry room will be unavailable from " . $this->faker->time('H:i') . " to " . $this->faker->time('H:i') . " on " . $this->faker->dateTimeBetween('now', '+10 days')->format('F j, Y') . " for equipment servicing.",
            'Pool Area Cleaning' => "$propertyName: Pool maintenance is scheduled for " . $this->faker->dayOfWeek . ". Please refrain from using the pool area during this time.",
            'Electrical System Upgrade' => "$propertyName: Electrical system upgrade on " . $this->faker->dateTimeBetween('now', '+3 weeks')->format('F j, Y') . ". Expect brief power outages.",
            'HVAC Inspection Notice' => "$propertyName: Routine HVAC inspection scheduled on " . $this->faker->dateTimeBetween('now', '+1 month')->format('F j, Y') . ". Please ensure access to units.",
            'Water Outage Alert' => "$propertyName: Temporary water outage affecting certain floors on " . $this->faker->dateTimeBetween('now', '+7 days')->format('F j, Y') . " from " . $this->faker->time('H:i') . " to " . $this->faker->time('H:i') . ".",
            'Parking Lot Closure' => "$propertyName: Parking lot closed on " . $this->faker->dateTimeBetween('now', '+14 days')->format('F j, Y') . " for resurfacing.",
            'Security Upgrade Announcement' => "$propertyName: Security upgrades on " . $this->faker->dateTimeBetween('now', '+2 weeks')->format('F j, Y') . ". Minor access delays expected.",
            'Fire Drill Reminder' => "$propertyName: Fire drill scheduled for " . $this->faker->dayOfWeek . " at " . $this->faker->time('H:i') . ". Follow safety instructions.",
            'Pest Control Schedule' => "$propertyName: Pest control treatment in common areas on " . $this->faker->dateTimeBetween('now', '+10 days')->format('F j, Y') . ". Keep areas accessible.",
            'New Community Rules' => "$propertyName: New community rules effective immediately. All tenants must comply.",
            'Tenant Meeting Reminder' => "$propertyName: Tenant meeting scheduled for " . $this->faker->dateTimeBetween('now', '+2 weeks')->format('F j, Y') . " at " . $this->faker->time('H:i') . ". Attendance recommended.",
            'Recycling Program Update' => "$propertyName: Recycling program updates effective " . $this->faker->dateTimeBetween('now', '+5 days')->format('F j, Y') . ". Follow new guidelines.",
            'Community Event Announcement' => "$propertyName: Community event on " . $this->faker->dateTimeBetween('now', '+1 month')->format('F j, Y') . " at the common hall. Fun activities planned!",
            'Welcome New Tenants' => "$propertyName: Welcome to our new tenants joining this month. Make them feel at home!",
            default => $this->faker->paragraph(3)
        };

        return [$headline, $details];
    }
}
