<?php

namespace App\Livewire\Concerns;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\ContractAuditLog;
use App\Models\Lease;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

trait CreatesMoveInBilling
{
    /**
     * Create the move-in billing (advance + deposit + optional short-term premium)
     * for a freshly approved/created lease. Idempotent: skips if a billing already exists.
     *
     * Defaults to 'Paid': RA 9653 Section 6 requires advance + deposit before move-in,
     * so the operator is recording cash already collected. Pass 'Unpaid' explicitly only
     * if you intend to defer collection.
     *
     * When Paid, a Transaction is recorded with the supplied payment_method/or_number
     * (falling back to the values stored on the lease) so there's audit-trail proof.
     */
    protected function createMoveInBilling(
        Lease $lease,
        string $paymentStatus = 'Paid',
        ?string $paymentMethod = null,
        ?string $orNumber = null,
        ?string $receiptImage = null
    ): ?Billing {
        if ($lease->billings()->exists()) {
            return $lease->billings()->first();
        }

        $rate = (float) $lease->contract_rate;
        $deposit = (float) $lease->security_deposit;
        $premium = (float) ($lease->short_term_premium ?? 0);
        $startDate = Carbon::parse($lease->start_date ?? now());
        $total = $rate + $deposit + $premium;
        $isPaid = $paymentStatus === 'Paid';

        $resolvedMethod = $paymentMethod ?: $lease->move_in_payment_method ?: 'Cash';
        $resolvedOrNumber = $orNumber ?: $lease->move_in_or_number;
        $resolvedReceipt = $receiptImage ?: $lease->move_in_receipt_image;

        $billing = Billing::create([
            'lease_id' => $lease->lease_id,
            'billing_type' => 'move_in',
            'billing_date' => $startDate->format('Y-m-d'),
            'next_billing' => $startDate->copy()->addMonth()->format('Y-m-d'),
            'due_date' => $startDate->format('Y-m-d'),
            'to_pay' => $total,
            'amount' => $total,
            'status' => $isPaid ? 'Paid' : 'Unpaid',
        ]);

        BillingItem::create([
            'billing_id' => $billing->billing_id,
            'charge_category' => 'move_in',
            'charge_type' => 'advance',
            'description' => '1 Month Advance — First Month Rent',
            'amount' => $rate,
        ]);

        BillingItem::create([
            'billing_id' => $billing->billing_id,
            'charge_category' => 'move_in',
            'charge_type' => 'security_deposit',
            'description' => '1 Month Security Deposit',
            'amount' => $deposit,
        ]);

        if ($premium > 0) {
            BillingItem::create([
                'billing_id' => $billing->billing_id,
                'charge_category' => 'move_in',
                'charge_type' => 'short_term_premium',
                'description' => 'Short-Term Premium (contract under 6 months)',
                'amount' => $premium,
            ]);
        }

        if ($isPaid) {
            // Create separate transactions for advance (rent) and security deposit
            $advanceTxn = Transaction::createWithSequenceRetry([
                'billing_id' => $billing->billing_id,
                'reference_number' => 'placeholder',
                'or_number' => $resolvedOrNumber,
                'transaction_type' => 'Debit',
                'category' => 'Rent Payment',
                'payment_method' => $resolvedMethod,
                'transaction_date' => today(),
                'amount' => $rate,
            ]);

            ContractAuditLog::log($lease->lease_id, 'move_in_payment_recorded', [
                'metadata' => [
                    'amount' => $billing->amount,
                    'payment_method' => $resolvedMethod,
                    'or_number' => $resolvedOrNumber,
                    'receipt_image' => $resolvedReceipt,
                    'reference' => $advanceTxn->reference_number,
                    'recorded_by' => Auth::id(),
                ],
            ]);
        }

        return $billing;
    }
}
