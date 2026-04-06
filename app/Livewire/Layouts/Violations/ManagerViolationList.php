<?php

namespace App\Livewire\Layouts\Violations;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Property;

class ManagerViolationList extends Component
{
    public $activeTab = 'all';
    public $activeViolationId = null;
    public $sortOrder = 'newest';
    public $selectedBuilding = null;
    public $search = '';

    #[On('refreshDashboard')]
    public function refreshDashboard() {}

    public function updatedSearch()
    {
        $this->activeViolationId = null;
    }

    #[On('refresh-violation-list')]
    public function refreshList() {}

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->activeViolationId = null;
    }

    public function selectViolation($id)
    {
        $this->activeViolationId = $id;
        $this->dispatch('managerViolationSelected', violationId: $id);
    }

    public function render()
    {
        $managerId = Auth::id();

        $baseQuery = DB::table('violations')
            ->join('leases', 'violations.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->where('units.manager_id', $managerId)
            ->whereNull('violations.deleted_at')
            ->when($this->selectedBuilding, function ($query) {
                $query->where('properties.building_name', $this->selectedBuilding);
            })
            ->select(
                'violations.violation_id',
                'violations.violation_number',
                'violations.status',
                'violations.category',
                'violations.severity',
                'violations.offense_number',
                'violations.penalty_type',
                'violations.violation_date',
                'violations.created_at',
                'units.unit_number',
                'properties.building_name',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
            );

        if (!empty($this->search)) {
            $term = $this->search;
            $unitTerm = preg_replace('/^Unit\s+/i', '', $term);
            $search = '%' . $term . '%';
            $unitSearch = '%' . $unitTerm . '%';
            $baseQuery->where(function ($q) use ($search, $unitSearch) {
                $q->where('violations.violation_number', 'like', $search)
                  ->orWhere('violations.category', 'like', $search)
                  ->orWhere('units.unit_number', 'like', $unitSearch)
                  ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', $search);
            });
        }

        $allCount          = (clone $baseQuery)->count();
        $issuedCount       = (clone $baseQuery)->where('violations.status', 'Issued')->count();
        $acknowledgedCount = (clone $baseQuery)->where('violations.status', 'Acknowledged')->count();
        $resolvedCount     = (clone $baseQuery)->where('violations.status', 'Resolved')->count();

        $listQuery = clone $baseQuery;
        switch ($this->activeTab) {
            case 'issued':
                $listQuery->where('violations.status', 'Issued');
                break;
            case 'acknowledged':
                $listQuery->where('violations.status', 'Acknowledged');
                break;
            case 'resolved':
                $listQuery->where('violations.status', 'Resolved');
                break;
        }

        $direction = $this->sortOrder === 'newest' ? 'desc' : 'asc';
        $violations = $listQuery->orderBy('violations.created_at', $direction)->get();

        if ($this->activeViolationId === null && $violations->isNotEmpty()) {
            $this->selectViolation($violations->first()->violation_id);
        }

        $allViolations = (clone $baseQuery)->orderBy('violations.created_at', 'desc')->limit(200)->get();
        $suggestions = collect()
            ->merge($allViolations->pluck('violation_number')->filter())
            ->merge($allViolations->pluck('tenant_name')->filter())
            ->merge($allViolations->map(fn($v) => 'Unit ' . $v->unit_number)->filter())
            ->merge($allViolations->pluck('category')->filter())
            ->unique()
            ->values()
            ->toArray();

        $buildingOptions = [];
        try {
            $buildingOptions = Property::distinct()->pluck('building_name', 'building_name')->toArray();
        } catch (\Exception $e) { $buildingOptions = []; }

        return view('livewire.layouts.violations.manager-violation-list', [
            'violations' => $violations,
            'counts' => [
                'all'          => $allCount,
                'issued'       => $issuedCount,
                'acknowledged' => $acknowledgedCount,
                'resolved'     => $resolvedCount,
            ],
            'sortOrder' => $this->sortOrder,
            'suggestions' => $suggestions,
            'buildingOptions' => $buildingOptions,
        ]);
    }
}
