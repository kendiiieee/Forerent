<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Lease;
use App\Models\PaymentCategory;
use App\Models\PaymentRequest;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Generator;

class PaymentRequestSeeder extends Seeder
{
    protected Generator $faker;

    private array $pendingMethods = ['GCash', 'Maya', 'Bank Transfer'];

    private array $methodPrefixes = [
        'GCash'         => 'GC',
        'Maya'          => 'MY',
        'Bank Transfer' => 'BT',
    ];

    public function run(): void
    {
        $this->faker = app(Generator::class);

        $incomeCategories = PaymentCategory::active()
            ->income()
            ->pluck('payment_category_id')
            ->toArray();

        $leases = Lease::with('bed.unit')->orderBy('start_date')->get();

        foreach ($leases as $lease) {
            $unit = $lease->bed->unit ?? null;
            $managerId = $unit?->manager_id;

            // Fetch ALL billings (Monthly, Move-in, Move-out) to ensure none are missed
            $billings = Billing::where('lease_id', $lease->lease_id)->get();

            foreach ($billings as $billing) {
                // 1. Skip if the bill is negative or zero
                if ($billing->amount <= 0 && $billing->to_pay <= 0) {
                    continue;
                }

                if ($billing->status === 'Paid') {
                    // --- CASE 1: PAID BILLINGS ---
                    // Find the transaction tied to this specific billing
                    $transaction = Transaction::where('billing_id', $billing->billing_id)->first();

                    if ($transaction) {
                        // 2. Skip ONLY if the method is 'Cash'
                        if ($transaction->payment_method === 'Cash') {
                            continue;
                        }

                        $submittedAt = Carbon::parse($transaction->transaction_date);

                        PaymentRequest::create([
                            'billing_id'          => $billing->billing_id,
                            'lease_id'            => $lease->lease_id,
                            'tenant_id'           => $lease->tenant_id,
                            'payment_category_id' => $this->faker->randomElement($incomeCategories),
                            'payment_method'      => $transaction->payment_method,
                            'reference_number'    => $transaction->reference_number,
                            'amount_paid'         => $transaction->amount,
                            'proof_image'         => 'payment_proofs/sample_proof_' . $this->faker->numberBetween(1, 5) . '.svg',
                            'status'              => 'Confirmed',
                            'created_at'          => $submittedAt,
                            'updated_at'          => $submittedAt,
                            'reviewed_by'         => $managerId,
                            'reviewed_at'         => $submittedAt,
                        ]);
                    }
                    // Optional: If a bill is marked 'Paid' but has NO transaction,
                    // you could add a fallback here, but it's better to fix the TransactionSeeder.
                }
                elseif (in_array($billing->status, ['Unpaid', 'Overdue'])) {
                    // --- CASE 2: UNPAID/OVERDUE BILLINGS (Pending) ---
                    $method = $this->faker->randomElement($this->pendingMethods);
                    $prefix = $this->methodPrefixes[$method] ?? 'PAY';
                    $submittedAt = Carbon::parse($billing->billing_date)->addDays(rand(1, 5));

                    PaymentRequest::create([
                        'billing_id'          => $billing->billing_id,
                        'lease_id'            => $lease->lease_id,
                        'tenant_id'           => $lease->tenant_id,
                        'payment_category_id' => $this->faker->randomElement($incomeCategories),
                        'payment_method'      => $method,
                        'reference_number'    => $prefix . '-' . strtoupper($this->faker->bothify('???####')),
                        'amount_paid'         => $billing->to_pay,
                        'proof_image'         => 'payment_proofs/sample_proof_' . $this->faker->numberBetween(1, 5) . '.svg',
                        'status'              => 'Pending',
                        'created_at'          => $submittedAt,
                        'updated_at'          => $submittedAt,
                        'reviewed_by'         => null,
                        'reviewed_at'         => null,
                    ]);
                }
            }
        }
    }
}
