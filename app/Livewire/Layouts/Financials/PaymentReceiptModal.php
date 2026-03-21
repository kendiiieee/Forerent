<?php

namespace App\Livewire\Layouts\Financials;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentReceiptModal extends Component
{
    public $isOpen = false;
    public $data = [];


    #[On('open-payment-receipt')]
    public function open($billingId)
    {
        $record = DB::table('billings')
            ->join('leases', 'billings.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users as tenant', 'leases.tenant_id', '=', 'tenant.user_id')
            ->join('users as manager', 'units.manager_id', '=', 'manager.user_id')
            ->leftJoin('transactions', function ($join) {          // ← leftJoin so unpaid billings still load
                $join->on('transactions.billing_id', '=', 'billings.billing_id')
                    ->where('transactions.category', '=', 'Rent Payment');
            })
            ->where('billings.billing_id', $billingId)
            ->select(
                'billings.*',
                'units.unit_number',
                'properties.building_name',
                'properties.address',
                'tenant.first_name  as tenant_first_name',
                'tenant.last_name   as tenant_last_name',
                'tenant.contact     as tenant_contact',
                'manager.first_name as manager_first_name',
                'manager.last_name  as manager_last_name',
                'manager.contact    as manager_contact',
                'transactions.transaction_date as txn_date',       // ← from transactions
                'transactions.reference_number as txn_reference',  // ← from transactions
            )
            ->first();

        if ($record) {
            $this->data = $this->formatReceiptData($record);
            $this->isOpen = true;
        } else {
            logger("Modal Error: No record found for Billing ID " . $billingId);
        }
    }


    private function formatReceiptData($record)
    {
        $billingDate = \Carbon\Carbon::parse($record->billing_date);

        return [
            'invoice_no'  => '20250825-' . str_pad($record->billing_id, 3, '0', STR_PAD_LEFT),
            'issued_date' => $billingDate->format('F d, Y'),
            'due_date'    => $billingDate->copy()->addDays(20)->format('F d, Y'),

            'tenant' => [
                'name'     => $record->tenant_first_name . ' ' . $record->tenant_last_name,
                'unit'     => 'Unit ' . $record->unit_number,
                'building' => $record->building_name,
                'address'  => $record->address,
                'contact'  => $record->tenant_contact ?? 'N/A',
            ],

            'payment' => [
                'date_paid'  => $record->txn_date
                    ? Carbon::parse($record->txn_date)->format('F d, Y')
                    : 'Pending',
                'txn_id'     => $record->txn_reference ?? 'Pending',
                'lease_type' => 'Monthly',
                'period'     => $billingDate->format('F Y'),
            ],

            'recipient' => [
                'name'     => $record->manager_first_name . ' ' . $record->manager_last_name,
                'position' => 'Property Manager',
                'contact'  =>  $record->manager_contact ?? 'N/A',
            ],

            'financials' => [
                'description' => 'Unit ' . $record->unit_number . ' - Monthly Rent',
                'amount'      => $record->to_pay,
            ]
        ];
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.layouts.financials.payment-receipt-modal');
    }
}
