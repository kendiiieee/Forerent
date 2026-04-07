<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    private array $paymentMethods = ['GCash', 'Maya', 'Bank Transfer', 'Cash'];

    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        $sequenceNumber = Transaction::count() + 1;

        // Fetch only necessary columns for paid billings
        Billing::where('status', 'Paid')
            ->select(['billing_id', 'billing_type', 'billing_date', 'amount'])
            ->chunkById(self::CHUNK_SIZE, function ($billings) use (&$sequenceNumber) {
                $transactions = [];

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
                        'payment_method'   => $this->paymentMethods[random_int(0, count($this->paymentMethods) - 1)],
                        'transaction_date' => $date->format('Y-m-d'),
                        'amount'           => $billing->amount,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];

                    $sequenceNumber++;
                }

                // Bulk insert per chunk
                Transaction::insert($transactions);
            });
    }
}
