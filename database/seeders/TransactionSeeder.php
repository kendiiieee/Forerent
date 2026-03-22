<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $transactions = [];
        $transactionId = Transaction::max('transaction_id') + 1;

        $paymentMethods = ['GCash', 'Maya', 'Bank Transfer', 'Cash'];
        $methodPrefixes = [
            'GCash'         => 'GC',
            'Maya'          => 'MY',
            'Bank Transfer' => 'BT',
            'Cash'          => 'CS',
        ];

        // ── Transactions for all paid billings (monthly + move-in) ──────────
        $billings = Billing::where('status', 'Paid')->get();

        foreach ($billings as $billing) {
            $date = Carbon::parse($billing->billing_date);
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            // Determine category based on billing_type
            $category = match ($billing->billing_type) {
                'move_in' => 'Advance',
                'move_out' => 'Deposit',
                default => 'Rent Payment',
            };

            // Determine reference prefix
            $refPrefix = match ($billing->billing_type) {
                'move_in' => 'MOVE',
                'move_out' => 'OUT',
                default => 'CITI',
            };

            $transactions[] = [
                'transaction_id'   => $transactionId,
                'billing_id'       => $billing->billing_id,
                'name'             => match ($billing->billing_type) {
                    'move_in' => "Move-In Payment - Billing #{$billing->billing_id}",
                    'move_out' => "Move-Out Settlement - Billing #{$billing->billing_id}",
                    default => "Rent Payment - Billing #{$billing->billing_id}",
                },
                'reference_number' => $refPrefix . '-' . strtoupper($date->format('M')) . $date->format('Y') . '-' . str_pad($transactionId, 4, '0', STR_PAD_LEFT),
                'or_number'        => 'OR-' . $date->format('Ymd') . '-' . str_pad($transactionId, 4, '0', STR_PAD_LEFT),
                'transaction_type' => 'Credit',
                'category'         => $category,
                'payment_method'   => $paymentMethod,
                'transaction_date' => $date->format('Y-m-d'),
                'amount'           => $billing->amount,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $transactionId++;
        }

        // ── Other transactions (Maintenance, Vendor Payment) ────────────────
        $startDate  = Carbon::create(2021, 1, 1);
        $endDate    = Carbon::now();
        $categories = ['Maintenance', 'Vendor Payment'];

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $transactionsPerMonth = rand(3, 6);

            for ($i = 0; $i < $transactionsPerMonth; $i++) {
                $category = $categories[array_rand($categories)];

                [$amount, $type] = match ($category) {
                    'Maintenance'    => [rand(60000, 1500000) / 100, 'Debit'],
                    'Vendor Payment' => [rand(30000, 1000000) / 100, 'Debit'],
                };

                $txnDate = $currentDate->copy()->addDays(rand(0, 27));
                $txnPaymentMethod = $paymentMethods[array_rand($paymentMethods)];

                $transactions[] = [
                    'transaction_id'   => $transactionId,
                    'billing_id'       => null,
                    'name'             => "Transaction {$transactionId}",
                    'reference_number' => $this->generateReferenceNumber($category, $currentDate, $transactionId),
                    'or_number'        => 'OR-' . $txnDate->format('Ymd') . '-' . str_pad($transactionId, 4, '0', STR_PAD_LEFT),
                    'transaction_type' => $type,
                    'category'         => $category,
                    'payment_method'   => $txnPaymentMethod,
                    'transaction_date' => $txnDate->format('Y-m-d'),
                    'amount'           => $amount,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                $transactionId++;
            }

            $currentDate->addMonth();
        }

        foreach (array_chunk($transactions, 1000) as $chunk) {
            Transaction::insert($chunk);
        }
    }

    private function generateReferenceNumber($category, $date, $id)
    {
        $prefixes = [
            'Deposit'        => 'DEP',
            'Advance'        => 'ADV',
            'Maintenance'    => 'MNT',
            'Vendor Payment' => 'VEND',
        ];

        $prefix = $prefixes[$category] ?? 'TXN';
        return $prefix . $date->format('Ymd') . str_pad($id, 6, '0', STR_PAD_LEFT);
    }
}
