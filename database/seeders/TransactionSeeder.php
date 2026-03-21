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
        $transactionId = Transaction::max('transaction_id') + 1; // avoid ID conflicts if reseeding

        // ── Rent Payment transactions matched to billings ─────────────────
        $billings = Billing::all();

        foreach ($billings as $billing) {
            // Only create a transaction for paid billings
            if ($billing->status !== 'Paid') continue;

            $date = Carbon::parse($billing->billing_date);

            $transactions[] = [
                'transaction_id'   => $transactionId,
                'billing_id'       => $billing->billing_id,
                'name'             => "Rent Payment - Billing #{$billing->billing_id}",
                'reference_number' => 'RENT' . $date->format('Ymd') . str_pad($transactionId, 6, '0', STR_PAD_LEFT),
                'transaction_type' => 'Credit',
                'category'         => 'Rent Payment',
                'transaction_date' => $date->format('Y-m-d'),
                'amount'           => $billing->amount,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $transactionId++;
        }

        // ── Other transactions (Deposit, Advance, Maintenance, Vendor) ────
        $startDate  = Carbon::create(2021, 1, 1);
        $endDate    = Carbon::now();
        $categories = ['Deposit', 'Advance', 'Maintenance', 'Vendor Payment'];

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $transactionsPerMonth = rand(3, 6);

            for ($i = 0; $i < $transactionsPerMonth; $i++) {
                $category = $categories[array_rand($categories)];

                [$amount, $type] = match ($category) {
                    'Deposit'        => [rand(500000, 1000000) / 100, 'Credit'],
                    'Advance'        => [rand(1000000, 2000000) / 100, 'Credit'],
                    'Maintenance'    => [rand(60000, 1500000) / 100, 'Debit'],
                    'Vendor Payment' => [rand(30000, 1000000) / 100, 'Debit'],
                };

                $transactions[] = [
                    'transaction_id'   => $transactionId,
                    'billing_id'       => null,
                    'name'             => "Transaction {$transactionId}",
                    'reference_number' => $this->generateReferenceNumber($category, $currentDate, $transactionId),
                    'transaction_type' => $type,
                    'category'         => $category,
                    'transaction_date' => $currentDate->copy()->addDays(rand(0, 27))->format('Y-m-d'),
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
