<?php

namespace App\Livewire\Layouts\Tenants;

use Livewire\Component;
use Livewire\Attributes\On;

class TenantSortControls extends Component
{
    public $activeTab = 'current';
    public $sortOrder = 'newest';
    public $counts = [
        'current'     => 0,
        'transferred' => 0,
        'moved_out'   => 0,
    ];

    public function setTab($tab): void
    {
        $this->activeTab = $tab;
        $this->dispatch('tenantTabChanged', tab: $tab);
    }

    public function setSortOrder($order): void
    {
        $this->sortOrder = $order;
        $this->dispatch('tenantSortChanged', sortOrder: $order);
    }

    #[On('tenantCountsUpdated')]
    public function updateCounts(array $counts): void
    {
        $this->counts = $counts;
    }

    #[On('tenantTabReset')]
    public function resetTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[On('buildingSelected')]
    public function onBuildingChanged($buildingId): void
    {
        $this->activeTab = 'current';
    }

    public function render()
    {
        return view('livewire.layouts.tenants.tenant-sort-controls');
    }
}
