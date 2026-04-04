<?php

namespace App\Livewire\Layouts\Managers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class ManagerNavigation extends Component
{
    public $managers = [];
    public $activeManagerId = null;
    public $search = '';

    public function mount(): void
    {
        $this->loadManagers();
        $this->autoSelectFirst();
    }

    public function booted(): void
    {
        if ($this->activeManagerId) {
            $this->dispatch('managerSelected', managerId: $this->activeManagerId);
        }
    }

    #[On('refresh-manager-list')]
    public function refreshManagerList(): void
    {
        $this->loadManagers();
        if ($this->activeManagerId) {
            $this->dispatch('managerSelected', managerId: $this->activeManagerId);
        }
    }

    #[On('managerActivated')]
    public function activateManager(int $managerId): void
    {
        $this->activeManagerId = $managerId;
    }

    private function loadManagers(): void
    {
        $allManagerIds = $this->getAllManagerIds();

        $query = User::whereIn('user_id', $allManagerIds)
            ->where('role', 'manager');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });
        }

        $this->managers = $query->get();
    }

    private function getAllManagerIds()
    {
        $landlordId = Auth::id();

        $assignedManagerIds = Unit::whereHas('property', function ($query) use ($landlordId) {
            $query->where('owner_id', $landlordId);
        })->whereNotNull('manager_id')->pluck('manager_id')->unique();

        $unassignedManagerIds = User::where('role', 'manager')
            ->whereDoesntHave('unitsManaged')
            ->pluck('user_id');

        return $assignedManagerIds->merge($unassignedManagerIds)->unique();
    }

    public function selectManager(int $managerId): void
    {
        $this->activeManagerId = $managerId;
        $this->dispatch('managerSelected', managerId: $managerId);
    }

    public function updatedSearch(): void
    {
        $this->loadManagers();
        $this->autoSelectFirst();
    }

    private function autoSelectFirst(): void
    {
        if (count($this->managers) > 0) {
            $first = $this->managers[0];
            $this->selectManager($first->user_id);
        }
    }

    public function render()
    {
        return view('livewire.layouts.managers.manager-navigation');
    }
}
