<?php

namespace App\Livewire\Layouts\Properties;
use App\Models\Property;
use Livewire\Component;

class Buildings extends Component
{
    public Property $property; // <-- Accept the Property model
    public bool $compact = false;
    // public $image; // You might need this if you add images
    public function render()
    {
        // Access data like $this->property->building_name in the view
        return view('livewire.layouts.properties.buildingcard');
    }
}
