<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Notification;
use App\Models\PaymentRequest;
use App\Models\Transaction;
use App\Models\Property;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Carbon\Carbon;

class PaymentReceipts extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $selectedMonth = null;
    public $selectedBuilding = null;
    public $billingIdToMarkPaid = null;
    public $search = '';

    public function setTab($tab) { $this->activeTab = $tab; $this->resetPage(); }
    public function updatedActiveTab()   { $this->resetPage(); }
    public function updatedSelectedMonth() { $this->resetPage(); }
    public function updatedSelectedBuilding() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function confirmPayment($id)
    {
        $this->billingIdToMarkPaid = $id;
        $this->dispatch('open-modal', 'mark-as-paid-confirmation');
    }

    public function viewReceipt($billingId)
    {
        $record = DB::table('billings')
            ->join('leases', 'billings.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users as tenant', 'leases.tenant_id', '=', 'tenant.user_id')
            ->join('users as manager', 'units.manager_id', '=', 'manager.user_id')
            ->where('billings.billing_id', $billingId)
            ->select(
                'billings.billing_id',
                'billings.billing_date',
                'billings.billing_type',
                'billings.due_date',
                'billings.to_pay',
                'billings.previous_balance',
                'billings.status',
                'units.unit_number',
                'units.room_cap',
                'units.occupants',
                'units.bed_type',
                'beds.bed_number',
                'properties.building_name',
                'properties.address',
                'leases.start_date',
                'leases.end_date',
                'leases.term',
                'tenant.first_name as tenant_first_name',
                'tenant.last_name as tenant_last_name',
                'tenant.contact as tenant_contact',
                'manager.first_name as manager_first_name',
                'manager.last_name as manager_last_name',
                'manager.contact as manager_contact'
            )
            ->first();

        if (!$record) return;

        // Always 'Rent Payment' in seeder/markAsPaid regardless of billing_type,
        // filter by transaction_type Credit to avoid pulling wrong records
        $txn = Transaction::where('billing_id', $billingId)
            ->where('transaction_type', 'Credit')
            ->where('category', 'Rent Payment')
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->first();

        $billingDate = Carbon::parse($record->billing_date);
        $dueDate = $record->due_date
            ? Carbon::parse($record->due_date)->format('F d, Y')
            : $billingDate->copy()->addDays(20)->format('F d, Y');

        // Fetch billing items
        $billingItems = BillingItem::where('billing_id', $billingId)
            ->whereNull('deleted_at')
            ->get()
            ->map(fn ($item) => [
                'description' => $item->description,
                'amount'      => $item->amount,
                'category'    => $item->charge_category,
                'type'        => $item->charge_type,
            ])
            ->toArray();

        // Fallback if no billing items exist (legacy data)
        if (empty($billingItems)) {
            $billingItems = [[
                'description' => 'Unit ' . $record->unit_number . ' - Monthly Rent',
                'amount'      => $record->to_pay,
                'category'    => 'recurring',
                'type'        => 'rent',
            ]];
        }

        // Derive txn_id from payment_method + or_number to keep it stable across reloads
        $txnId = 'Pending';
        if ($txn?->payment_method && $txn?->or_number && $txn->or_number !== 'Pending') {
            $prefix = ['GCash' => 'GC', 'Maya' => 'MY', 'Bank Transfer' => 'BT', 'Cash' => 'CS'][$txn->payment_method] ?? 'XX';
            $txnId  = $prefix . '-' . $txn->or_number;
        }

        $data = [
            'invoice_no'       => 'FRNT-' . strtoupper($billingDate->format('M')) . $billingDate->format('Y') . '-' . $billingId,
            'issued_date'      => $billingDate->format('F d, Y'),
            'due_date'         => $dueDate,
            'status'           => $record->status,
            'billing_type'     => $record->billing_type ?? 'monthly',
            'previous_balance' => $record->previous_balance ?? 0,
            'tenant' => [
                'name'         => $record->tenant_first_name . ' ' . $record->tenant_last_name,
                'unit_bed'     => 'Unit ' . $record->unit_number . ' — ' . $record->bed_number,
                'room_type'    => $record->room_cap . '-in-a-Room Bedspace (' . $record->occupants . ')',
                'building'     => $record->building_name,
                'location'     => $record->address,
                'lease_period' => Carbon::parse($record->start_date)->format('M d') . ' — ' . Carbon::parse($record->end_date)->format('M d, Y'),
                'lease_type'   => $record->term . '-Month Contract',
            ],
            'payment' => [
                'date_paid'      => $txn?->transaction_date
                    ? Carbon::parse($txn->transaction_date)->format('F d, Y')
                    : 'Pending',
                'payment_method' => $txn?->payment_method ?? 'Pending',
                'txn_id'         => $txnId,
                'reference_no'   => $txn?->reference_number ?? 'Pending',
                'or_number'      => $txn?->or_number ?? 'Pending',
                'period'         => $billingDate->format('F Y'),
            ],
            'recipient' => [
                'name'     => $record->manager_first_name . ' ' . $record->manager_last_name,
                'position' => 'Property Manager',
                'contact'  => $record->manager_contact ?? 'N/A',
            ],
            'items'      => $billingItems,
            'total'      => $record->to_pay,
            'financials' => [
                'description' => 'Unit ' . $record->unit_number . ' - Monthly Rent',
                'amount'      => $record->to_pay,
            ],
        ];

        $this->dispatch('open-payment-receipt', data: $data);
    }
    public function markAsPaid()
    {
        if (!$this->billingIdToMarkPaid) {
            return;
        }

        $billingId = $this->billingIdToMarkPaid;

        DB::transaction(function () use ($billingId) {
            $billing = DB::table('billings')
                ->where('billing_id', $billingId)
                ->lockForUpdate()
                ->first();

            if (!$billing) {
                return;
            }

            // Get lease info for the PaymentRequest record
            $lease = DB::table('leases')
                ->where('lease_id', $billing->lease_id)
                ->first();

            DB::table('billings')
                ->where('billing_id', $billingId)
                ->update([
                    'status'     => 'Paid',
                    'amount'     => DB::raw('to_pay'),
                    'updated_at' => now(),
                ]);

            $category = match ($billing->billing_type ?? 'monthly') {
                'move_in'  => 'Advance',
                'move_out' => 'Deposit',
                default    => 'Rent Payment',
            };

            $existing = Transaction::where('billing_id', $billingId)
                ->where('transaction_type', 'Credit')
                ->where('category', $category)
                ->first();

            if ($existing) {
                return;
            }

            $transaction = null;
            $date        = Carbon::parse($billing->billing_date);

            for ($attempt = 1; $attempt <= 3; $attempt++) {
                try {
                    Transaction::syncPrimaryKeySequence();

                    $name = match ($billing->billing_type ?? 'monthly') {
                        'move_in'  => "Move-In Payment - Billing #{$billingId}",
                        'move_out' => "Move-Out Settlement - Billing #{$billingId}",
                        default    => "Rent Payment - Billing #{$billingId}",
                    };

                    $transaction = Transaction::createWithSequenceRetry([
                        'billing_id'       => $billingId,
                        'name'             => $name,
                        'reference_number' => 'placeholder',
                        'or_number'        => 'placeholder',
                        'transaction_type' => 'Credit',
                        'category'         => $category,
                        'payment_method'   => 'Cash',
                        'transaction_date' => today(),
                        'amount'           => $billing->to_pay ?? 0,
                    ]);

                    break;
                } catch (UniqueConstraintViolationException|QueryException $exception) {
                    $isPgPkeyConflict = DB::getDriverName() === 'pgsql'
                        && str_contains(strtolower($exception->getMessage()), 'transactions_pkey');

                    if (!$isPgPkeyConflict || $attempt === 3) {
                        throw $exception;
                    }

                    $alreadyInserted = Transaction::where('billing_id', $billingId)
                        ->where('transaction_type', 'Credit')
                        ->where('category', $category)
                        ->first();

                    if ($alreadyInserted) {
                        return;
                    }
                }
            }

            if (!$transaction) {
                return;
            }

            $sequenceId = str_pad($transaction->transaction_id, 4, '0', STR_PAD_LEFT);

            $refNumber = $prefix . now()->format('Ymd') . '-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);

            $transaction->update([
                'reference_number' => $refNumber,
            ]);

            // Create a confirmed PaymentRequest so it appears in tenant's payment history
            if ($lease) {
                PaymentRequest::create([
                    'billing_id' => $billingId,
                    'lease_id' => $lease->lease_id,
                    'tenant_id' => $lease->tenant_id,
                    'payment_method' => 'Cash',
                    'reference_number' => $refNumber,
                    'amount_paid' => $billing->to_pay ?? 0,
                    'proof_image' => null,
                    'status' => 'Confirmed',
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);

                // Notify tenant
                $billingPeriod = $billing->billing_date
                    ? \Carbon\Carbon::parse($billing->billing_date)->format('F Y')
                    : '';

                Notification::create([
                    'user_id' => $lease->tenant_id,
                    'type' => 'payment_confirmed',
                    'title' => 'Cash Payment Recorded',
                    'message' => 'Your cash payment of ₱' . number_format($billing->to_pay, 2) . ' for ' . $billingPeriod . ' billing has been recorded and confirmed by the manager.',
                ]);
            }
        });

        $this->dispatch('notify', type: 'success', title: 'Payment Updated', description: 'Billing marked as paid successfully.');
        $this->dispatch('close-modal', 'mark-as-paid-confirmation');
        $this->billingIdToMarkPaid = null;
    }
    // ─── helpers ────────────────────────────────────────────────────────────

    private function isManager(): bool
    {
        return Auth::user()?->role === 'manager';
    }

    private function isLandlord(): bool
    {
        return Auth::user()?->role === 'landlord';
    }

    private function isTenant(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    private function baseQuery()
    {
        $query = DB::table('billings')
            ->join('leases',  'billings.lease_id',  '=', 'leases.lease_id')
            ->join('beds',    'leases.bed_id',       '=', 'beds.bed_id')
            ->join('units',   'beds.unit_id',        '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users',   'leases.tenant_id',    '=', 'users.user_id')
            ->select('billings.*', 'users.first_name', 'users.last_name', 'properties.building_name');

        if ($this->isManager()) {
            $query->where('units.manager_id', Auth::id());
        }

        if ($this->isLandlord()) {
            $query->where('properties.owner_id', Auth::id());
        }

        if ($this->isTenant()) {
            $query->where('leases.tenant_id', Auth::id());
        }

        return $query;
    }

    // ─── render ─────────────────────────────────────────────────────────────

    public function render()
    {
        $monthOptions = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $buildingOptions = [];
        try {
            $buildingOptions = Property::distinct()->pluck('building_name', 'building_name')->toArray();
        } catch (\Exception $e) { $buildingOptions = []; }

        $baseQuery = $this->baseQuery();

        // Apply search filter
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $baseQuery->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', $search)
                  ->orWhere('billings.status', 'like', $search)
                  ->orWhere('properties.building_name', 'like', $search);
            });
        }

        $counts = [
            'all'      => (clone $baseQuery)->count(),
            'upcoming' => (clone $baseQuery)->where('billings.status', 'Unpaid')->count(),
            'paid'     => (clone $baseQuery)->where('billings.status', 'Paid')->count(),
            'unpaid'   => (clone $baseQuery)->where('billings.status', 'Overdue')->count(),
        ];

        $query = clone $baseQuery;

        match ($this->activeTab) {
            'upcoming' => $query->where('billings.status', 'Unpaid'),
            'paid'     => $query->where('billings.status', 'Paid'),
            'unpaid'   => $query->where('billings.status', 'Overdue'),
            default    => null,
        };

        if ($this->selectedMonth) {
            $query->whereMonth('billings.billing_date', $this->selectedMonth);
        }

        if ($this->selectedBuilding) {
            $query->where('properties.building_name', $this->selectedBuilding);
        }

        $payments = $query->orderBy('billings.billing_date', 'desc')->paginate(10);

        // Build suggestions from unfiltered data
        $allRecords = $this->baseQuery()->select(
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name"),
            'properties.building_name'
        )->get();

        $suggestions = collect()
            ->merge($allRecords->pluck('tenant_name')->filter())
            ->merge($allRecords->pluck('building_name')->filter())
            ->unique()
            ->values()
            ->toArray();

        return view('livewire.layouts.financials.payment-receipts', [
            'payments'        => $payments,
            'counts'          => $counts,
            'monthOptions'    => $monthOptions,
            'buildingOptions' => $buildingOptions,
            'suggestions'     => $suggestions,
        ]);
    }
}
