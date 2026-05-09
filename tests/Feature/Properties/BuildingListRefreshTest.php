<?php

use App\Models\Property;
use App\Models\User;
use App\Livewire\Layouts\Properties\BuildingCardsSection;
use Livewire\Livewire;

// Ensure we're using Pest's test helper functions

beforeEach(function () {
    // run database migrations and start fresh state if not already handled globally
});

it('reloads the building list when a property-created event is emitted', function () {
    $user = User::factory()->create(['role' => 'landlord']);
    $this->actingAs($user);

    // start with a single property tied to the user
    $existing = Property::factory()->create(['owner_id' => $user->user_id]);

    $component = Livewire::test(BuildingCardsSection::class);

    // initial load should contain the existing record
    $component->assertCount('properties', 1)
              ->assertSet('selectedBuilding', $existing->property_id);

    // create a second property and simulate the event
    $new = Property::factory()->create(['owner_id' => $user->user_id]);

    $component->emit('propertyCreated', $new->property_id);

    // after the event the livewire instance should re-fetch and select the new building
    $component->assertCount('properties', 2)
              ->assertSet('selectedBuilding', $new->property_id);
});
