<?php

namespace App\Livewire\Layouts\Tenants;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class PaymentHistory extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $selectedMonth = null;
    public $selectedBuilding = null;

    public function updatedActiveTab() { $this->resetPage(); }
    public function updatedSelectedMonth() { $this->resetPage(); }
    public function updatedSelectedBuilding() { $this->resetPage(); }

    public function render()
    {
        $user = Auth::user();

        $monthOptions = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $buildingOptions = [];
        try {
            $buildingOptions = Property::distinct()->pluck('building_name', 'building_name')->toArray();
        } catch (\Exception $e) { $buildingOptions = []; }

        // Scope to only this tenant's transactions via billing → lease
        $query = Transaction::with('billing')
            ->whereHas('billing.lease', fn($q) =>
            $q->where('tenant_id', $user->user_id)
            );

        if ($this->activeTab === 'paid') {
            $query->where('transaction_type', 'Credit');
        } elseif ($this->activeTab === 'unpaid') {
            $query->where('transaction_type', 'Debit');
        } elseif ($this->activeTab === 'upcoming') {
            $query->where('transaction_id', 0);
        }

        if ($this->selectedMonth) {
            $query->whereMonth('transaction_date', $this->selectedMonth);
        }

        $payments = $query->orderBy('transaction_date', 'desc')->paginate(10);

        return view('livewire.layouts.tenants.payment-history', [
            'payments'        => $payments,
            'monthOptions'    => $monthOptions,
            'buildingOptions' => $buildingOptions,
        ]);
    }
}
