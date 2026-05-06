<?php

namespace App\Livewire\Concerns;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

trait CreatesMoveInBilling
{
    /**
     * Create the move-in billing (advance + deposit + optional short-term premium)
     * for a freshly approved/created lease. Idempotent: skips if a billing already exists.
     *
     * When $paymentStatus = 'Paid', also creates the matching debit Transaction.
     */
    protected function createMoveInBilling(Lease $lease, string $paymentStatus = 'Unpaid'): ?Billing
    {
        if ($lease->billings()->exists()) {
            return $lease->billings()->first();
        }

        $rate = (float) $lease->contract_rate;
        $deposit = (float) $lease->security_deposit;
        $premium = (float) ($lease->short_term_premium ?? 0);
        $startDate = Carbon::parse($lease->start_date ?? now());
        $total = $rate + $deposit + $premium;
        $isPaid = $paymentStatus === 'Paid';

        $billing = Billing::create([
            'lease_id'     => $lease->lease_id,
            'billing_type' => 'move_in',
            'billing_date' => $startDate->format('Y-m-d'),
            'next_billing' => $startDate->copy()->addMonth()->format('Y-m-d'),
            'due_date'     => $startDate->format('Y-m-d'),
            'to_pay'       => $total,
            'amount'       => $total,
            'status'       => $isPaid ? 'Paid' : 'Unpaid',
        ]);

        BillingItem::create([
            'billing_id'      => $billing->billing_id,
            'charge_category' => 'move_in',
            'charge_type'     => 'advance',
            'description'     => '1 Month Advance — First Month Rent',
            'amount'          => $rate,
        ]);

        BillingItem::create([
            'billing_id'      => $billing->billing_id,
            'charge_category' => 'move_in',
            'charge_type'     => 'security_deposit',
            'description'     => '1 Month Security Deposit',
            'amount'          => $deposit,
        ]);

        if ($premium > 0) {
            BillingItem::create([
                'billing_id'      => $billing->billing_id,
                'charge_category' => 'move_in',
                'charge_type'     => 'short_term_premium',
                'description'     => 'Short-Term Premium (contract under 6 months)',
                'amount'          => $premium,
            ]);
        }

        if ($isPaid) {
            $txn = Transaction::create([
                'billing_id'       => $billing->billing_id,
                'reference_number' => 'placeholder',
                'transaction_type' => 'Debit',
                'category'         => 'Rent Payment',
                'transaction_date' => today(),
                'amount'           => $billing->amount,
            ]);
            $txn->update([
                'reference_number' => 'MOVEIN-' . now()->format('Ymd') . '-'
                    . str_pad((string) $txn->transaction_id, 6, '0', STR_PAD_LEFT),
            ]);
        }

        return $billing;
    }
}
