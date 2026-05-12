<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
                $billingIds = $billings->pluck('billing_id')->all();
                $itemsByBilling = BillingItem::whereIn('billing_id', $billingIds)
                    ->get()
                    ->groupBy('billing_id');

                foreach ($billings as $billing) {
                    $date = Carbon::parse($billing->billing_date);
                    $items = $itemsByBilling[$billing->billing_id] ?? collect();

                    if ($items->isNotEmpty()) {
                        foreach ($items as $item) {
                            if ((float) $item->amount === 0.0) {
                                continue;
                            }

                            $rawAmount = (float) $item->amount;
                            $transactionType = $rawAmount < 0 ? 'Debit' : 'Credit';
                            $amount = abs($rawAmount);
                            if ($amount === 0.0) {
                                continue;
                            }

                            $category = $this->mapCategory($item->charge_type, $item->description);
                            $transactions[] = [
                                'billing_id'       => $billing->billing_id,
                                'name'             => $item->description ?: "Billing Item - #{$billing->billing_id}",
                                'reference_number' => 'FRNT-' . strtoupper($date->format('M')) . $date->format('Y') . '-' . $sequenceNumber,
                                'or_number'        => 'OR-' . $date->format('Ymd') . '-' . $sequenceNumber,
                                'transaction_type' => $transactionType,
                                'category'         => $category,
                                'payment_method'   => $this->paymentMethods[random_int(0, count($this->paymentMethods) - 1)],
                                'transaction_date' => $date->format('Y-m-d'),
                                'amount'           => $amount,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ];

                            $sequenceNumber++;
                        }

                        continue;
                    }

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

    private function mapCategory(?string $chargeType, ?string $description): string
    {
        return match ($chargeType) {
            'advance' => 'Advance',
            'security_deposit' => 'Deposit',
            'deposit_refund' => 'Deposit',
            'rent' => 'Rent Payment',
            'electricity_share' => 'Rent Payment',
            'water_share' => 'Rent Payment',
            'short_term_premium' => 'Rent Payment',
            'late_fee' => 'Rent Payment',
            default => 'Rent Payment',
        };
    }
}
