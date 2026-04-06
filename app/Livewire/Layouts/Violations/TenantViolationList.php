<?php

namespace App\Livewire\Layouts\Violations;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantViolationList extends Component
{
    public $activeViolationId = null;
    public $activeTab = 'all';
    public $sortOrder = 'newest';
    public $search = '';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->activeViolationId = null;
        $this->dispatch('tenantViolationSelected', violationId: null);
    }

    public function selectViolation($id)
    {
        $this->activeViolationId = $id;
        $this->dispatch('tenantViolationSelected', violationId: $id);
    }

    #[On('refresh-violation-list')]
    public function refreshList() {}

    public function updatedSearch()
    {
        $this->activeViolationId = null;
    }

    public function render()
    {
        $tenantLeaseIds = DB::table('leases')
            ->where('tenant_id', Auth::id())
            ->pluck('lease_id');

        $baseQuery = DB::table('violations')
            ->whereIn('lease_id', $tenantLeaseIds)
            ->whereNull('deleted_at');

        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $baseQuery->where(function ($q) use ($search) {
                $q->where('violation_number', 'like', $search)
                  ->orWhere('category', 'like', $search)
                  ->orWhere('status', 'like', $search);
            });
        }

        $statusCountsRaw = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $counts = [
            'all'          => (clone $baseQuery)->count(),
            'issued'       => $statusCountsRaw['Issued'] ?? 0,
            'acknowledged' => $statusCountsRaw['Acknowledged'] ?? 0,
            'resolved'     => $statusCountsRaw['Resolved'] ?? 0,
        ];

        $query = (clone $baseQuery)->select(
            'violation_id', 'violation_number', 'status', 'category',
            'severity', 'offense_number', 'penalty_type', 'fine_amount',
            'violation_date', 'created_at'
        );

        switch ($this->activeTab) {
            case 'issued':
                $query->where('status', 'Issued');
                break;
            case 'acknowledged':
                $query->where('status', 'Acknowledged');
                break;
            case 'resolved':
                $query->where('status', 'Resolved');
                break;
        }

        $direction = $this->sortOrder === 'newest' ? 'desc' : 'asc';
        $violations = $query->orderBy('created_at', $direction)->get();

        if ($this->activeViolationId === null && $violations->isNotEmpty()) {
            $this->selectViolation($violations->first()->violation_id);
        }

        $suggestions = collect()
            ->merge($violations->pluck('violation_number')->filter())
            ->merge($violations->pluck('category')->filter())
            ->unique()
            ->values()
            ->toArray();

        return view('livewire.layouts.violations.tenant-violation-list', [
            'violations' => $violations,
            'counts' => $counts,
            'activeTab' => $this->activeTab,
            'activeViolationId' => $this->activeViolationId,
            'sortOrder' => $this->sortOrder,
            'suggestions' => $suggestions,
        ]);
    }
}
