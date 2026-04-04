<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Transaction;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    protected Generator $faker;

    private array $paymentMethods = ['GCash', 'Maya', 'Bank Transfer', 'Cash'];

    public function run(): void
    {
        $this->faker = app(Generator::class);

        $transactions   = [];
        $sequenceNumber = Transaction::count() + 1;

        // ── Transactions for all Paid billings ──────────────────────────────
        $billings = Billing::where('status', 'Paid')->get();

        foreach ($billings as $billing) {
            $date          = Carbon::parse($billing->billing_date);
            $paymentMethod = $this->paymentMethods[array_rand($this->paymentMethods)];

            $transactions[] = [
                'billing_id'       => $billing->billing_id,
                'name'             => match ($billing->billing_type) {
                    'move_in'  => "Move-In Payment - Billing #{$billing->billing_id}",
                    'move_out' => "Move-Out Settlement - Billing #{$billing->billing_id}",
                    default    => "Rent Payment - Billing #{$billing->billing_id}",
                },
                'reference_number' => 'FRNT-' .
                    strtoupper($date->format('M')) .
                    $date->format('Y') . '-' .
                    str_pad($sequenceNumber, 4, '0', STR_PAD_LEFT),
                'or_number'        => 'OR-' .
                    $date->format('Ymd') . '-' .
                    str_pad($sequenceNumber, 4, '0', STR_PAD_LEFT),
                'transaction_type' => 'Credit',
                'category'         => 'Rent Payment',
                'payment_method'   => $paymentMethod,
                'transaction_date' => $date->format('Y-m-d'),
                'amount'           => $billing->amount,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $sequenceNumber++;
        }

        // ── Other transactions (Maintenance / Vendor Payment) ────────────────
        $categories  = ['Maintenance', 'Vendor Payment'];
        $currentDate = Carbon::create(2021, 1, 1);
        $endDate     = Carbon::now();

        while ($currentDate->lte($endDate)) {
            $transactionsPerMonth = rand(3, 6);

            for ($i = 0; $i < $transactionsPerMonth; $i++) {
                $category      = $categories[array_rand($categories)];
                $txnDate       = $currentDate->copy()->addDays(rand(0, 27));
                $paymentMethod = $this->paymentMethods[array_rand($this->paymentMethods)];

                [$amount, $type] = match ($category) {
                    'Maintenance'    => [rand(60000, 1500000) / 100, 'Debit'],
                    'Vendor Payment' => [rand(30000, 1000000) / 100, 'Debit'],
                };

                $transactions[] = [
                    'billing_id'       => null,
                    'name'             => "{$category} - " . $txnDate->format('F Y'),
                    'reference_number' => $this->generateReferenceNumber($category, $currentDate, $sequenceNumber),
                    'or_number'        => 'OR-' .
                        $txnDate->format('Ymd') . '-' .
                        str_pad($sequenceNumber, 4, '0', STR_PAD_LEFT),
                    'transaction_type' => $type,
                    'category'         => $category,
                    'payment_method'   => $paymentMethod,
                    'transaction_date' => $txnDate->format('Y-m-d'),
                    'amount'           => $amount,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                $sequenceNumber++;
            }

            $currentDate->addMonth();
        }

        foreach (array_chunk($transactions, 1000) as $chunk) {
            Transaction::insert($chunk);
        }
    }

    private function generateReferenceNumber(string $category, Carbon $date, int $id): string
    {
        $prefixes = [
            'Maintenance'    => 'MNT',
            'Vendor Payment' => 'VEND',
            'Rent Payment'   => 'RENT',
        ];

        $prefix = $prefixes[$category] ?? 'TXN';

        return $prefix . '-' .
            strtoupper($date->format('M')) .
            $date->format('Y') . '-' .
            str_pad($id, 4, '0', STR_PAD_LEFT);
    }
}
