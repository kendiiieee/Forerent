<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    private array $paymentMethods = ['GCash', 'Maya', 'Bank Transfer', 'Cash'];

    public function run(): void
    {
        $transactions   = [];
        $sequenceNumber = Transaction::count() + 1;

        $billings = Billing::where('status', 'Paid')->get();

        foreach ($billings as $billing) {
            $date = Carbon::parse($billing->billing_date);

            $transactions[] = [
                'billing_id'       => $billing->billing_id,
                'name'             => match ($billing->billing_type) {
                    'move_in'  => "Move-In Payment - Billing #{$billing->billing_id}",
                    'move_out' => "Move-Out Settlement - Billing #{$billing->billing_id}",
                    default    => "Rent Payment - Billing #{$billing->billing_id}",
                },
                'reference_number' => 'FRNT-' . strtoupper($date->format('M')) . $date->format('Y') . '-' . $sequenceNumber,
                'or_number'        => 'OR-' . $date->format('Ymd') . '-' . $sequenceNumber,
                'transaction_type' => 'Credit',
                'category'         => 'Rent Payment',
                'payment_method'   => $this->paymentMethods[array_rand($this->paymentMethods)],
                'transaction_date' => $date->format('Y-m-d'),
                'amount'           => $billing->amount,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $sequenceNumber++;
        }

        foreach (array_chunk($transactions, 1000) as $chunk) {
            Transaction::insert($chunk);
        }
    }
}
