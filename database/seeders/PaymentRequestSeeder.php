<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Lease;
use App\Models\PaymentRequest;
use App\Models\Unit;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;

class PaymentRequestSeeder extends Seeder
{
    protected Generator $faker;

    public function run(): void
    {
        $this->faker = app(Generator::class);

        $paymentMethods = ['GCash', 'Maya', 'Bank Transfer', 'Cash'];
        $methodPrefixes = [
            'GCash'         => 'GC',
            'Maya'          => 'MY',
            'Bank Transfer' => 'BT',
            'Cash'          => 'CS',
        ];

        // Get active leases to create payment requests for their billings
        $leases = Lease::where('status', 'Active')->with('bed.unit')->get();

        foreach ($leases as $lease) {
            // Find the manager for this lease's unit
            $unit = $lease->bed->unit ?? null;
            $managerId = $unit?->manager_id;

            // Get Unpaid/Overdue monthly billings for this lease — these are the ones tenants would submit payment for
            $unpaidBillings = Billing::where('lease_id', $lease->lease_id)
                ->where('billing_type', 'monthly')
                ->whereIn('status', ['Unpaid', 'Overdue'])
                ->orderBy('billing_date', 'desc')
                ->get();

            // Also grab some Paid billings to create Confirmed payment requests (already reviewed)
            $paidBillings = Billing::where('lease_id', $lease->lease_id)
                ->where('billing_type', 'monthly')
                ->where('status', 'Paid')
                ->orderBy('billing_date', 'desc')
                ->limit(3)
                ->get();

            // Create Pending payment requests for unpaid/overdue billings
            foreach ($unpaidBillings as $billing) {
                $method = $this->faker->randomElement($paymentMethods);
                $prefix = $methodPrefixes[$method];
                $submittedAt = Carbon::parse($billing->billing_date)->addDays(rand(1, 10));

                PaymentRequest::create([
                    'billing_id'       => $billing->billing_id,
                    'lease_id'         => $lease->lease_id,
                    'tenant_id'        => $lease->tenant_id,
                    'payment_method'   => $method,
                    'reference_number' => $method !== 'Cash'
                        ? $prefix . '-' . strtoupper($this->faker->bothify('???####'))
                        : null,
                    'amount_paid'      => $billing->to_pay,
                    'proof_image'      => 'payment_proofs/sample_proof_' . $this->faker->numberBetween(1, 5) . '.svg',
                    'status'           => 'Pending',
                    'created_at'       => $submittedAt,
                    'updated_at'       => $submittedAt,
                ]);
            }

            // Create Confirmed payment requests for some paid billings
            foreach ($paidBillings as $index => $billing) {
                $method = $this->faker->randomElement($paymentMethods);
                $prefix = $methodPrefixes[$method];
                $submittedAt = Carbon::parse($billing->billing_date)->addDays(rand(1, 5));
                $reviewedAt = $submittedAt->copy()->addDays(rand(1, 3));

                PaymentRequest::create([
                    'billing_id'       => $billing->billing_id,
                    'lease_id'         => $lease->lease_id,
                    'tenant_id'        => $lease->tenant_id,
                    'payment_method'   => $method,
                    'reference_number' => $method !== 'Cash'
                        ? $prefix . '-' . strtoupper($this->faker->bothify('???####'))
                        : null,
                    'amount_paid'      => $billing->to_pay,
                    'proof_image'      => 'payment_proofs/sample_proof_' . $this->faker->numberBetween(1, 5) . '.svg',
                    'status'           => 'Confirmed',
                    'reviewed_by'      => $managerId,
                    'reviewed_at'      => $reviewedAt,
                    'created_at'       => $submittedAt,
                    'updated_at'       => $reviewedAt,
                ]);
            }

            // Create 1 Rejected payment request from an older paid billing (tenant re-submitted successfully later)
            $rejectedBilling = Billing::where('lease_id', $lease->lease_id)
                ->where('billing_type', 'monthly')
                ->where('status', 'Paid')
                ->orderBy('billing_date', 'asc')
                ->skip(1)
                ->first();

            if ($rejectedBilling) {
                $method = $this->faker->randomElement($paymentMethods);
                $prefix = $methodPrefixes[$method];
                $submittedAt = Carbon::parse($rejectedBilling->billing_date)->addDays(rand(1, 5));
                $reviewedAt = $submittedAt->copy()->addDays(rand(1, 2));

                $rejectReasons = [
                    'Proof of payment is blurry and unreadable. Please resubmit a clearer image.',
                    'Reference number does not match our records. Please double-check and resubmit.',
                    'Amount paid does not match the billing amount. Please verify and resubmit.',
                    'Payment receipt appears to be for a different transaction.',
                ];

                PaymentRequest::create([
                    'billing_id'       => $rejectedBilling->billing_id,
                    'lease_id'         => $lease->lease_id,
                    'tenant_id'        => $lease->tenant_id,
                    'payment_method'   => $method,
                    'reference_number' => $method !== 'Cash'
                        ? $prefix . '-' . strtoupper($this->faker->bothify('???####'))
                        : null,
                    'amount_paid'      => $rejectedBilling->to_pay,
                    'proof_image'      => 'payment_proofs/sample_proof_' . $this->faker->numberBetween(1, 5) . '.svg',
                    'status'           => 'Rejected',
                    'reject_reason'    => $this->faker->randomElement($rejectReasons),
                    'reviewed_by'      => $managerId,
                    'reviewed_at'      => $reviewedAt,
                    'created_at'       => $submittedAt,
                    'updated_at'       => $reviewedAt,
                ]);
            }
        }
    }
}
