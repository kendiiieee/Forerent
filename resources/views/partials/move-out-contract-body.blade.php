{{--
    Move-Out Contract Body — Reusable Partial
    Matches the official Dorm_Move_Out_Contract.pdf template exactly.

    Required variables:
    - $t                             : tenant data array
    - $deposit                       : security deposit amount
    - $moveOutChecklist              : array of move-out checklist items (optional)
    - $itemsReturned                 : array of returned items (optional)
    - $inspectionChecklist           : array of move-in checklist items for comparison (optional)
    - $moveOutTenantSignature        : move-out tenant signature path (nullable)
    - $moveOutOwnerSignature         : move-out owner signature path (nullable)
    - $moveOutManagerSignature       : move-out manager/witness signature path (nullable)
    - $moveOutTenantSignedAt         : formatted date string (nullable)
    - $moveOutOwnerSignedAt          : formatted date string (nullable)
    - $moveOutManagerSignedAt        : formatted date string (nullable)
    - $moveOutContractAgreed         : bool
    - $signatureMode                 : 'owner', 'manager', 'tenant', or 'readonly'
--}}

@php
    $moveOutChecklist = $moveOutChecklist ?? [];
    $itemsReturned = $itemsReturned ?? [];
    $inspectionChecklist = $inspectionChecklist ?? [];
    $moveOutTenantSignature = $moveOutTenantSignature ?? null;
    $moveOutOwnerSignature = $moveOutOwnerSignature ?? null;
    $moveOutManagerSignature = $moveOutManagerSignature ?? null;
    $moveOutTenantSignedAt = $moveOutTenantSignedAt ?? null;
    $moveOutOwnerSignedAt = $moveOutOwnerSignedAt ?? null;
    $moveOutManagerSignedAt = $moveOutManagerSignedAt ?? null;
    $moveOutContractAgreed = $moveOutContractAgreed ?? false;
    $signatureMode = $signatureMode ?? 'tenant';
    $outstandingBalances = $outstandingBalances ?? [];
    $depositRefund = $depositRefund ?? [];
    $checklistItemNames = \App\Livewire\Concerns\InspectionConfig::CHECKLIST_ITEMS;
    $returnItemNames = \App\Livewire\Concerns\InspectionConfig::RETURNED_ITEMS;
@endphp

{{-- Page Header --}}
<div style="background-color: #1a1a4e; margin: -2rem -2rem 0 -2rem; padding: 0.85rem 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <p style="font-size: 0.875rem; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 0.025em;">Dormitory Rental Agreement</p>
        <p style="font-size: 10px; color: #d1d5db;">Republic of the Philippines</p>
    </div>
    <p style="font-size: 0.75rem; font-weight: 600; color: #d1d5db; text-transform: uppercase; letter-spacing: 0.05em;">Move-Out Clearance</p>
</div>

{{-- Title --}}
<div class="text-center py-4">
    <h1 class="text-2xl font-bold text-gray-900 uppercase">Move-Out Clearance &<br>Deposit Settlement Agreement</h1>
    <p class="text-sm text-gray-500 mt-1">Dormitory Bedspace / Room Lease Termination</p>
</div>

<div class="border-t border-gray-300 mb-4"></div>

<p class="text-sm text-gray-700 leading-relaxed">This Move-Out Clearance and Deposit Settlement Agreement (<strong>"Agreement"</strong>) is entered into by and between the <strong>LESSOR</strong> (the dormitory owner or authorized operator) and the <strong>LESSEE</strong> (the tenant) to formally document the termination of the lease, the condition of the premises at move-out, and the settlement of all outstanding financial obligations, in compliance with Republic Act No. 9653 and other applicable Philippine laws.</p>

{{-- ═══════════════════════════════════════════════
     SECTION 1 — PARTIES
═══════════════════════════════════════════════ --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 1 — Parties</h3>

    <p class="font-bold text-xs uppercase text-gray-600 mb-2">Lessor</p>
    <table class="w-full border border-gray-300 text-sm mb-4"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Business / Trade Name:</td><td class="p-2">{{ $t['lessor_info']['business_name'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Authorized Representative:</td><td class="p-2">{{ $t['lessor_info']['representative'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Government ID:</td><td class="p-2">{{ $t['lessor_info']['government_id_type'] ?? '—' }} — {{ $t['lessor_info']['government_id_number'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Contact Number:</td><td class="p-2">{{ $t['lessor_info']['contact'] ?? '—' }}</td></tr>
        <tr><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Email Address:</td><td class="p-2">{{ $t['lessor_info']['email'] ?? '—' }}</td></tr>
    </tbody></table>

    <p class="font-bold text-xs uppercase text-gray-600 mb-2">Lessee</p>
    <table class="w-full border border-gray-300 text-sm"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Full Legal Name:</td><td class="p-2">{{ $t['personal_info']['first_name'] }} {{ $t['personal_info']['last_name'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Contact Number:</td><td class="p-2">{{ $t['contact_info']['contact_number'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Email Address:</td><td class="p-2">{{ $t['contact_info']['email'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Forwarding Address (for deposit refund / correspondence):</td><td class="p-2 {{ empty($t['move_out_details']['forwarding_address']) ? 'text-red-500 font-medium' : '' }}">{{ $t['move_out_details']['forwarding_address'] ?? 'Not provided — required before signing' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Emergency Contact:</td><td class="p-2">{{ $t['personal_info']['emergency_contact_name'] ?? '—' }} ({{ $t['personal_info']['emergency_contact_relationship'] ?? '' }}) — {{ $t['personal_info']['emergency_contact_number'] ?? '—' }}</td></tr>
    </tbody></table>
</div>

{{-- ═══════════════════════════════════════════════
     SECTION 2 — LEASE REFERENCE
═══════════════════════════════════════════════ --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 2 — Lease Reference</h3>
    <table class="w-full border border-gray-300 text-sm mb-3"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Original Move-In Contract Date:</td><td class="p-2">{{ ($t['move_in_details']['move_in_date'] ?? null) ? \Carbon\Carbon::parse($t['move_in_details']['move_in_date'])->format('F d, Y') : '—' }}</td></tr>
        <tr class="border-b">
            <td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Lease Start Date:</td>
            <td class="p-2">
                <span>{{ \Carbon\Carbon::parse($t['rent_details']['lease_start_date'])->format('F d, Y') }}</span>
            </td>
        </tr>
        <tr class="border-b">
            <td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Lease End Date:</td>
            <td class="p-2">{{ \Carbon\Carbon::parse($t['rent_details']['lease_end_date'])->format('F d, Y') }}</td>
        </tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Building / Unit / Floor / Bed Assignment:</td><td class="p-2">{{ $t['personal_info']['property'] }} / {{ $t['personal_info']['unit'] }} / Floor {{ $t['rent_details']['floor'] ?? '—' }} / Bed {{ $t['rent_details']['bed_number'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Actual Move-Out Date:</td><td class="p-2">{{ ($t['move_out_details']['move_out_date'] ?? null) ? \Carbon\Carbon::parse($t['move_out_details']['move_out_date'])->format('F d, Y') : '—' }}</td></tr>
    </tbody></table>

    <p class="text-xs font-bold text-gray-700 mb-2">Reason for Vacating:</p>
    @php
        $reason = $t['move_out_details']['reason_for_vacating'] ?? '';
        $reasons = [
            'End of lease term (contract expired)',
            'Voluntary early termination by Lessee',
            'Mutual agreement between both parties',
            'Lease violation or termination by Lessor',
            'Transfer to a different unit / building (internal transfer)',
        ];
        $isOther = $reason && !in_array($reason, $reasons);
    @endphp
    <div class="space-y-1 ml-2 text-xs text-gray-700">
        @foreach($reasons as $r)
            <div class="flex items-center gap-2">
                <span class="w-3.5 h-3.5 border border-gray-400 rounded-sm flex items-center justify-center text-[11px] {{ $reason === $r ? 'bg-[#3B5998] text-white border-[#3B5998]' : '' }}">@if($reason === $r)&#10003;@endif</span>
                <span>{{ $r }}</span>
            </div>
        @endforeach
        <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 border border-gray-400 rounded-sm flex items-center justify-center text-[11px] {{ $isOther ? 'bg-[#3B5998] text-white border-[#3B5998]' : '' }}">@if($isOther)&#10003;@endif</span>
            <span>Other: {{ $isOther ? $reason : '___________________________________' }}</span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     SECTION 3 — MOVE-OUT ROOM CONDITION INSPECTION
═══════════════════════════════════════════════ --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 3 — Move-Out Room Condition Inspection</h3>
    <p class="text-xs text-gray-700 mb-3">Both parties shall conduct a joint room inspection on the move-out date. The condition of each item below will be compared against the Move-In Checklist (Section 8 of the Move-In Contract) to identify damages beyond normal wear and tear.</p>

    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
        <p class="text-[10px] font-bold text-gray-600 uppercase mb-1">Definition: Normal Wear and Tear</p>
        <p class="text-[10px] text-gray-500 leading-relaxed mb-2">Normal wear and tear refers to the natural and gradual deterioration of the premises resulting from ordinary, everyday use. The following are considered <strong>normal</strong> and shall NOT be charged to the Lessee:</p>
        <ul class="text-[10px] text-gray-500 list-disc pl-4 space-y-0.5">
            <li>Minor scuffs or light marks on walls from daily contact</li>
            <li>Slight fading of paint or wall color due to sunlight exposure</li>
            <li>Light wear on floor surfaces from regular foot traffic</li>
            <li>Minor loosening of door handles or cabinet hinges from regular use</li>
            <li>Dust or dirt accumulation requiring standard cleaning</li>
        </ul>
        <p class="text-[10px] text-gray-500 leading-relaxed mt-2 mb-1">The following are <strong>NOT</strong> normal wear and tear and may result in deductions:</p>
        <ul class="text-[10px] text-gray-500 list-disc pl-4 space-y-0.5">
            <li>Holes, large cracks, or gouges in walls, floors, or ceilings</li>
            <li>Broken windows, door locks, or fixtures</li>
            <li>Stains, burns, or water damage from misuse or neglect</li>
            <li>Unauthorized modifications or removal of installed items</li>
            <li>Damage caused by pets, if pets were not permitted</li>
        </ul>
    </div>

    <table class="w-full border border-gray-300 text-xs">
        <thead><tr class="bg-[#3B5998] text-white">
            <th class="p-2 text-left">Item</th>
            <th class="p-2 text-center w-24">Move-In Condition</th>
            <th class="p-2 text-center w-24">Move-Out Condition</th>
            <th class="p-2 text-center w-20">Damage Found?</th>
            <th class="p-2 text-right w-28">Est. Repair Cost (PHP)</th>
        </tr></thead>
        <tbody>
            @php $totalRepairCost = 0; @endphp
            @foreach($checklistItemNames as $index => $itemName)
                @php
                    $moveInItem = collect($inspectionChecklist)->firstWhere('item_name', $itemName);
                    $moveOutItem = collect($moveOutChecklist)->firstWhere('item_name', $itemName);
                    $moveInCond = $moveInItem['condition'] ?? '';
                    $moveOutCond = $moveOutItem['condition'] ?? '';

                    // Damage detection: flag if move-out condition is worse than move-in,
                    // OR if move-out is damaged/poor/missing even when move-in was not recorded
                    $hasBadCondition = in_array($moveOutCond, ['damaged', 'poor', 'missing', 'fair']);
                    $conditionWorsened = $moveInCond && $moveOutCond && $moveInCond !== $moveOutCond && $moveOutCond !== 'good';
                    $noMoveInButDamaged = !$moveInCond && in_array($moveOutCond, ['damaged', 'poor', 'missing']);
                    $damageFound = $conditionWorsened || $noMoveInButDamaged;

                    $repairCost = (float) ($moveOutItem['repair_cost'] ?? 0);
                    if ($damageFound) $totalRepairCost += $repairCost;
                @endphp
                <tr class="border-b {{ $damageFound ? 'bg-red-50' : '' }} {{ !$moveInCond && $moveOutCond ? 'bg-yellow-50' : '' }}">
                    <td class="p-2 font-medium">{{ $itemName }}</td>
                    <td class="p-2 text-center border-l capitalize {{ !$moveInCond && $moveOutCond ? 'text-amber-500 italic' : '' }}">{{ $moveInCond ?: 'Not recorded' }}</td>
                    <td class="p-2 text-center border-l capitalize {{ $damageFound ? 'text-red-600 font-bold' : '' }}">{{ $moveOutCond ?: '' }}</td>
                    <td class="p-2 text-center border-l {{ $damageFound ? 'text-red-600 font-bold' : '' }}">{{ $damageFound ? 'Yes' : ($moveOutCond ? 'No' : '') }}</td>
                    <td class="p-2 text-right border-l">{{ $damageFound && $repairCost > 0 ? '&#8369; ' . number_format($repairCost, 2) : ($damageFound ? 'TBD' : '') }}</td>
                </tr>
            @endforeach
            @if($totalRepairCost > 0)
            <tr class="bg-gray-50">
                <td class="p-2 font-bold" colspan="4">Total Damage Repair Cost</td>
                <td class="p-2 text-right border-l font-bold">&#8369; {{ number_format($totalRepairCost, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>
    <p class="text-xs text-gray-600 mt-3 italic leading-relaxed">Move-out photographs shall be compared against move-in photographs on file. Both parties acknowledge the accuracy of the inspection findings recorded above.</p>
</div>

{{-- ═══════════════════════════════════════════════
     SECTION 4 — ITEMS RETURNED BY TENANT
═══════════════════════════════════════════════ --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 4 — Items Returned by Tenant</h3>

    <table class="w-full border border-gray-300 text-xs">
        <thead><tr class="bg-[#3B5998] text-white">
            <th class="p-2 text-left">Item</th>
            <th class="p-2 text-center w-16">Qty Issued</th>
            <th class="p-2 text-center w-20">Qty Returned</th>
            <th class="p-2 text-center w-16">Status</th>
            <th class="p-2 text-left">Condition</th>
            <th class="p-2 text-right w-32">Replacement Cost (PHP)</th>
        </tr></thead>
        <tbody>
            @php $totalReplacementCost = 0; @endphp
            @foreach($returnItemNames as $itemName)
                @php
                    $returned = collect($itemsReturned)->firstWhere('item_name', $itemName);
                    $issuedQty = (int) ($returned['quantity'] ?? 0);
                    $returnedQty = (int) ($returned['quantity_returned'] ?? 0);
                    $isReturned = $returned && ($returned['is_returned'] ?? false);
                    $isPartial = $isReturned && $issuedQty > 0 && $returnedQty < $issuedQty;
                    $missingQty = $issuedQty - $returnedQty;
                    $condition = $returned['condition'] ?? '';
                    $replacementCost = (float) ($returned['replacement_cost'] ?? 0);
                    // Charge for unreturned OR partial returns
                    if ((!$isReturned || $isPartial) && $returned) $totalReplacementCost += $replacementCost;

                    $statusLabel = '';
                    $statusClass = '';
                    if ($returned) {
                        if (!$isReturned) {
                            $statusLabel = '✗ Not Returned';
                            $statusClass = 'text-red-600 font-bold';
                        } elseif ($isPartial) {
                            $statusLabel = "⚠ Partial ({$missingQty} missing)";
                            $statusClass = 'text-amber-600 font-bold';
                        } else {
                            $statusLabel = '✓ Complete';
                            $statusClass = 'text-green-600';
                        }
                    }
                @endphp
                <tr class="border-b {{ (!$isReturned && $returned) ? 'bg-red-50' : ($isPartial ? 'bg-amber-50' : '') }}">
                    <td class="p-2 font-medium">{{ $itemName }}</td>
                    <td class="p-2 text-center border-l">{{ $issuedQty ?: '—' }}</td>
                    <td class="p-2 text-center border-l {{ $isPartial ? 'text-amber-600 font-bold' : '' }}">{{ $returned ? $returnedQty : '' }}</td>
                    <td class="p-2 text-center border-l {{ $statusClass }}">{{ $statusLabel }}</td>
                    <td class="p-2 border-l">{{ $condition }}</td>
                    <td class="p-2 text-right border-l">{{ ((!$isReturned || $isPartial) && $returned && $replacementCost > 0) ? '&#8369; ' . number_format($replacementCost, 2) : (((!$isReturned || $isPartial) && $returned) ? 'TBD' : '') }}</td>
                </tr>
            @endforeach
            @if($totalReplacementCost > 0)
            <tr class="bg-gray-50">
                <td class="p-2 font-bold" colspan="5">Total Replacement Cost</td>
                <td class="p-2 text-right border-l font-bold">&#8369; {{ number_format($totalReplacementCost, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

{{-- ═══════════════════════════════════════════════
     SECTION 5 — OUTSTANDING BALANCES
═══════════════════════════════════════════════ --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 5 — Outstanding Balances</h3>
    <p class="text-[10px] text-gray-400 mb-2 italic">Balances as of {{ now()->format('F d, Y h:i A') }}. These amounts will be deducted from the security deposit in Section 6.</p>

    @php
        $advanceRent = (float) ($t['move_in_details']['advance_amount'] ?? 0);
    @endphp

    <table class="w-full border border-gray-300 text-sm">
        <thead><tr class="bg-[#3B5998] text-white">
            <th class="p-2 text-left">Charge</th>
            <th class="p-2 text-left">Period / Description</th>
            <th class="p-2 text-center w-28">Settlement</th>
            <th class="p-2 text-right w-36">Amount (PHP)</th>
        </tr></thead>
        <tbody>
            @php $totalOutstanding = 0; @endphp
            @forelse($outstandingBalances as $balance)
                @php $totalOutstanding += (float) $balance['amount']; @endphp
                <tr class="border-b">
                    <td class="p-2">{{ $balance['charge'] }}</td>
                    <td class="p-2">{{ $balance['period'] ?: '—' }}</td>
                    <td class="p-2 text-center text-xs text-gray-500">Deduct from deposit</td>
                    <td class="p-2 text-right">&#8369; {{ number_format($balance['amount'], 2) }}</td>
                </tr>
            @empty
                <tr class="border-b"><td class="p-2 text-gray-400 text-center" colspan="4">No outstanding balances</td></tr>
            @endforelse

            @if($advanceRent > 0)
                <tr class="border-b bg-green-50">
                    <td class="p-2 text-green-700">Advance Rent Credit (paid at move-in)</td>
                    <td class="p-2 text-green-700">Applied to final month</td>
                    <td class="p-2 text-center text-xs text-green-600">Credit</td>
                    <td class="p-2 text-right text-green-700">(&#8369; {{ number_format($advanceRent, 2) }})</td>
                </tr>
                @php $totalOutstanding -= $advanceRent; @endphp
            @endif

            <tr class="bg-gray-50">
                <td class="p-2 font-bold" colspan="3">NET OUTSTANDING BALANCE</td>
                <td class="p-2 text-right font-bold">&#8369; {{ number_format(max(0, $totalOutstanding), 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ═══════════════════════════════════════════════
     SECTION 6 — SECURITY DEPOSIT REFUND CALCULATION
═══════════════════════════════════════════════ --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 6 — Security Deposit Refund Calculation</h3>
    <p class="text-xs text-gray-700 mb-3">In accordance with RA 9653, the security deposit refund is calculated as follows:</p>

    @php
        $deductions = $depositRefund['deductions'] ?? [];
        $refundAmount = $depositRefund['amount'] ?? null;
        $interestEarned = (float) ($depositRefund['interest_earned'] ?? 0);
        $totalDeductions = collect($deductions)->sum('amount');
    @endphp
    <table class="w-full border border-gray-300 text-sm">
        <thead><tr class="bg-[#3B5998] text-white">
            <th class="p-2 text-left">Item</th>
            <th class="p-2 text-right w-40">Amount (PHP)</th>
        </tr></thead>
        <tbody>
            <tr class="border-b"><td class="p-2 font-semibold">Original Security Deposit Held</td><td class="p-2 text-right font-semibold">&#8369; {{ number_format($deposit, 2) }}</td></tr>
            @if($interestEarned > 0)
                <tr class="border-b bg-green-50">
                    <td class="p-2 text-green-700">(+) Deposit Interest Earned (RA 9653 IRR §7b)</td>
                    <td class="p-2 text-right text-green-700 font-medium">&#8369; {{ number_format($interestEarned, 2) }}</td>
                </tr>
            @endif
            @forelse($deductions as $deduction)
                <tr class="border-b">
                    <td class="p-2">(-) {{ $deduction['label'] }}</td>
                    <td class="p-2 text-right {{ (float) $deduction['amount'] > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                        @if((float) $deduction['amount'] > 0)
                            (&#8369; {{ number_format($deduction['amount'], 2) }})
                        @else
                            TBD
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="border-b"><td class="p-2 text-gray-400" colspan="2">No deductions recorded yet</td></tr>
            @endforelse
            <tr class="border-b bg-gray-50">
                <td class="p-2 font-semibold">Total Deductions</td>
                <td class="p-2 text-right font-semibold text-red-600">(&#8369; {{ number_format($totalDeductions, 2) }})</td>
            </tr>
            <tr class="bg-blue-50">
                <td class="p-2 font-bold text-[#3B5998]">NET DEPOSIT REFUND</td>
                <td class="p-2 text-right font-bold text-[#3B5998]">
                    @if($refundAmount !== null)
                        &#8369; {{ number_format($refundAmount, 2) }}
                    @else
                        &#8369; {{ number_format(max(0, $deposit - $totalDeductions + $interestEarned), 2) }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
    <p class="text-[10px] text-gray-400 mt-1 italic">Note: Outstanding balances from Section 5 are already included in the deductions above. They will NOT be collected separately.</p>

    <p class="text-xs font-bold text-gray-700 mt-4 mb-2">Refund Details:</p>
    <table class="w-full border border-gray-300 text-sm"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Refund Method (GCash / Bank / Cash):</td><td class="p-2">{{ $t['move_out_details']['deposit_refund_method'] ?? '___________________________' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Account Name or Number:</td><td class="p-2">{{ $t['move_out_details']['deposit_refund_account'] ?? '___________________________' }}</td></tr>
        @php
            $moveOutDate = $t['move_out_details']['move_out_date'] ?? null;
            $expectedRefund = $moveOutDate ? \Carbon\Carbon::parse($moveOutDate)->addDays(30)->format('F d, Y') : null;
        @endphp
        <tr><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Expected Refund Date (within 30 days of clearance):</td><td class="p-2">{{ $expectedRefund ?? 'To be determined upon move-out completion' }}</td></tr>
    </tbody></table>
</div>

{{-- ═══════════════════════════════════════════════
     SECTION 7 — CLEARANCE CERTIFICATION
═══════════════════════════════════════════════ --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 7 — Clearance Certification</h3>
    <p class="text-xs text-gray-700 mb-2">Both parties hereby certify the following:</p>
    <ul class="text-xs text-gray-600 list-disc pl-5 space-y-1.5">
        <li>The joint move-out inspection has been completed and all findings are accurately recorded in this Agreement.</li>
        <li>All outstanding balances have been settled in full or will be deducted from the security deposit as agreed. <strong>If the total outstanding balance exceeds the security deposit, the Lessee remains liable for the deficit and shall settle the remaining amount within fifteen (15) calendar days from the date of this clearance.</strong></li>
        <li>A final utility meter reading (electricity and water) has been conducted as of the move-out date. Any remaining utility charges will be included in the outstanding balances above.</li>
        <li>All keys, access cards, Wi-Fi credentials, and borrowed items have been returned or accounted for above.</li>
        <li>The Lessee has vacated the premises and removed all personal belongings. Any items left behind may be coordinated for retrieval through the messaging system within a reasonable period.</li>
        <li>The Lessor agrees to process and release the deposit refund within thirty (30) calendar days from the date of this clearance.</li>
        <li>Both parties release each other from any further claims, demands, or liabilities related to this lease, except as expressly specified in this Agreement.</li>
    </ul>
</div>

{{-- ═══════════════════════════════════════════════
     SECTION 8 — AGREEMENT AND SIGNATURES
═══════════════════════════════════════════════ --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 8 — Agreement and Signatures</h3>
    <p class="text-xs text-gray-700 mb-6">By signing below, both parties confirm that the move-out inspection has been conducted, all balances have been accounted for, and they voluntarily agree to the deposit settlement terms stated herein.</p>

    {{-- 3 Signature blocks: Owner (1st) → Manager/Witness (2nd) → Tenant (3rd) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 items-end">
        {{-- 1. Owner/Lessor Signature (signs first) --}}
        <div class="text-center">
            @if($moveOutOwnerSignature)
                <div class="border-2 border-emerald-200 bg-emerald-50/50 rounded-xl h-24 mb-2 flex items-center justify-center p-2">
                    <img src="{{ route('secure.file', $moveOutOwnerSignature) }}" class="max-h-full max-w-full object-contain" alt="Owner Signature">
                </div>
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-800">{{ $t['lessor_info']['representative'] }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-1">Signed: {{ $moveOutOwnerSignedAt }}</p>
            @else
                @if($signatureMode === 'owner')
                    <div x-data="{ ownerReadConfirmed: false, showConfirm: false }">
                        <label class="inline-flex items-start gap-2 mb-2 cursor-pointer px-1">
                            <input type="checkbox" x-model="ownerReadConfirmed" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-[10px] text-gray-600 text-left leading-tight">I have read and agree to all terms in this contract.</span>
                        </label>
                        <button
                            x-show="ownerReadConfirmed && !showConfirm"
                            x-on:click="showConfirm = true"
                            class="w-full border-2 border-dashed border-indigo-300 bg-indigo-50/30 rounded-xl h-24 mb-2 flex flex-col items-center justify-center hover:bg-indigo-50 hover:border-indigo-400 transition-all cursor-pointer group no-print"
                        >
                            <svg class="w-6 h-6 text-indigo-400 group-hover:text-indigo-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            <span class="text-[11px] font-semibold text-indigo-500 group-hover:text-indigo-600">Click to Sign</span>
                        </button>
                        {{-- Confirmation dialog --}}
                        <div x-show="showConfirm" x-cloak class="border-2 border-indigo-400 bg-indigo-50 rounded-xl p-3 mb-2 no-print">
                            <p class="text-[11px] text-indigo-800 font-semibold mb-2">Are you sure you want to sign this contract?</p>
                            <p class="text-[10px] text-indigo-600 mb-3">This action cannot be undone.</p>
                            <div class="flex gap-2 justify-center">
                                <button x-on:click="showConfirm = false" class="px-3 py-1 text-[10px] bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                                <button wire:click="openMoveOutSignatureModal" class="px-3 py-1 text-[10px] bg-indigo-500 text-white rounded-lg hover:bg-indigo-600">Yes, Sign</button>
                            </div>
                        </div>
                        <div x-show="!ownerReadConfirmed" class="border-2 border-dashed border-gray-200 rounded-xl h-24 mb-2 flex items-center justify-center">
                            <span class="text-[11px] text-gray-400">Check the box above to sign</span>
                        </div>
                    </div>
                @else
                    <div class="border-2 border-dashed border-gray-300 rounded-xl h-24 mb-2 flex items-center justify-center">
                        <span class="text-[11px] text-gray-400">Awaiting owner signature</span>
                    </div>
                @endif
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-500">{{ $t['lessor_info']['representative'] }}</p>
                <p class="text-[11px] text-gray-400 mt-1">Lessor / Property Owner</p>
            @endif
            <p class="text-xs text-gray-400 mt-2">Date: {{ $moveOutOwnerSignedAt ?? '___________________' }}</p>
        </div>

        {{-- 2. Manager/Witness Signature (signs second) --}}
        <div class="text-center">
            @if($moveOutManagerSignature)
                <div class="border-2 border-amber-200 bg-amber-50/50 rounded-xl h-24 mb-2 flex items-center justify-center p-2">
                    <img src="{{ route('secure.file', $moveOutManagerSignature) }}" class="max-h-full max-w-full object-contain" alt="Manager Witness Signature">
                </div>
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-800">{{ $t['manager_info']['name'] ?? 'Unit Manager' }}</p>
                <p class="text-[11px] text-amber-600 font-medium mt-1">Witnessed: {{ $moveOutManagerSignedAt }}</p>
            @else
                @if($signatureMode === 'manager' && $moveOutOwnerSignature)
                    <div x-data="{ managerReadConfirmed: false, showConfirm: false }">
                        <label class="inline-flex items-start gap-2 mb-2 cursor-pointer px-1">
                            <input type="checkbox" x-model="managerReadConfirmed" class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-[10px] text-gray-600 text-left leading-tight">I have read and agree to all terms in this contract.</span>
                        </label>
                        <button
                            x-show="managerReadConfirmed && !showConfirm"
                            x-on:click="showConfirm = true"
                            class="w-full border-2 border-dashed border-amber-300 bg-amber-50/30 rounded-xl h-24 mb-2 flex flex-col items-center justify-center hover:bg-amber-50 hover:border-amber-400 transition-all cursor-pointer group no-print"
                        >
                            <svg class="w-6 h-6 text-amber-400 group-hover:text-amber-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            <span class="text-[11px] font-semibold text-amber-500 group-hover:text-amber-600">Sign as Witness</span>
                        </button>
                        {{-- Confirmation dialog --}}
                        <div x-show="showConfirm" x-cloak class="border-2 border-amber-400 bg-amber-50 rounded-xl p-3 mb-2 no-print">
                            <p class="text-[11px] text-amber-800 font-semibold mb-2">Are you sure you want to sign this contract as witness?</p>
                            <p class="text-[10px] text-amber-600 mb-3">This action cannot be undone.</p>
                            <div class="flex gap-2 justify-center">
                                <button x-on:click="showConfirm = false" class="px-3 py-1 text-[10px] bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                                <button wire:click="openMoveOutSignatureModal('manager')" class="px-3 py-1 text-[10px] bg-amber-500 text-white rounded-lg hover:bg-amber-600">Yes, Sign</button>
                            </div>
                        </div>
                        <div x-show="!managerReadConfirmed" class="border-2 border-dashed border-gray-200 rounded-xl h-24 mb-2 flex items-center justify-center">
                            <span class="text-[11px] text-gray-400">Check the box above to sign</span>
                        </div>
                    </div>
                @else
                    <div class="border-2 border-dashed border-gray-300 rounded-xl h-24 mb-2 flex items-center justify-center">
                        <span class="text-[11px] text-gray-400">{{ $moveOutOwnerSignature ? 'Awaiting witness signature' : 'Waiting for owner to sign first' }}</span>
                    </div>
                @endif
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-500">{{ $t['manager_info']['name'] ?? 'Unit Manager' }}</p>
                <p class="text-[11px] text-gray-400 mt-1">Witness</p>
            @endif
            <p class="text-xs text-gray-400 mt-2">Date: {{ $moveOutManagerSignedAt ?? '___________________' }}</p>
        </div>

        {{-- 3. Tenant Signature (signs last) --}}
        <div class="text-center">
            @if($moveOutTenantSignature)
                <div class="border-2 border-emerald-200 bg-emerald-50/50 rounded-xl h-24 mb-2 flex items-center justify-center p-2">
                    <img src="{{ route('secure.file', $moveOutTenantSignature) }}" class="max-h-full max-w-full object-contain" alt="Tenant Signature">
                </div>
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-800">{{ $t['personal_info']['first_name'] }} {{ $t['personal_info']['last_name'] }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-1">Signed: {{ $moveOutTenantSignedAt }}</p>
            @else
                @if($signatureMode === 'tenant' && $moveOutOwnerSignature && $moveOutManagerSignature)
                    <div x-data="{ tenantReadConfirmed: false, showConfirm: false }">
                        <label class="inline-flex items-start gap-2 mb-2 cursor-pointer px-1">
                            <input type="checkbox" x-model="tenantReadConfirmed" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[10px] text-gray-600 text-left leading-tight">I have read and agree to all terms in this contract.</span>
                        </label>
                        <button
                            x-show="tenantReadConfirmed && !showConfirm"
                            x-on:click="showConfirm = true"
                            class="w-full border-2 border-dashed border-blue-300 bg-blue-50/30 rounded-xl h-24 mb-2 flex flex-col items-center justify-center hover:bg-blue-50 hover:border-blue-400 transition-all cursor-pointer group no-print"
                        >
                            <svg class="w-6 h-6 text-blue-400 group-hover:text-blue-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            <span class="text-[11px] font-semibold text-blue-500 group-hover:text-blue-600">Click to Sign</span>
                        </button>
                        {{-- Confirmation dialog --}}
                        <div x-show="showConfirm" x-cloak class="border-2 border-blue-400 bg-blue-50 rounded-xl p-3 mb-2 no-print">
                            <p class="text-[11px] text-blue-800 font-semibold mb-2">Are you sure you want to sign this contract?</p>
                            <p class="text-[10px] text-blue-600 mb-3">By signing, you agree to all terms including deposit deductions.</p>
                            <div class="flex gap-2 justify-center">
                                <button x-on:click="showConfirm = false" class="px-3 py-1 text-[10px] bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                                <button wire:click="openMoveOutSignatureModal" class="px-3 py-1 text-[10px] bg-blue-500 text-white rounded-lg hover:bg-blue-600">Yes, Sign</button>
                            </div>
                        </div>
                        <div x-show="!tenantReadConfirmed" class="border-2 border-dashed border-gray-200 rounded-xl h-24 mb-2 flex items-center justify-center">
                            <span class="text-[11px] text-gray-400">Check the box above to sign</span>
                        </div>
                    </div>
                @else
                    <div class="border-2 border-dashed border-gray-300 rounded-xl h-24 mb-2 flex items-center justify-center flex-col gap-1">
                        @if(!$moveOutOwnerSignature)
                            <span class="text-[11px] text-gray-400">Step 1: Waiting for owner signature</span>
                            <span class="text-[10px] text-gray-300">Owner has been notified</span>
                        @elseif(!$moveOutManagerSignature)
                            <span class="text-[11px] text-gray-400">Step 2: Waiting for manager/witness</span>
                            <span class="text-[10px] text-gray-300">Owner signed {{ $moveOutOwnerSignedAt }}</span>
                        @else
                            <span class="text-[11px] text-gray-400">Awaiting tenant signature</span>
                        @endif
                    </div>
                @endif
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-500">{{ $t['personal_info']['first_name'] }} {{ $t['personal_info']['last_name'] }}</p>
                <p class="text-[11px] text-gray-400 mt-1">Tenant / Lessee</p>
            @endif
            <p class="text-xs text-gray-400 mt-2">Date: {{ $moveOutTenantSignedAt ?? '___________________' }}</p>
        </div>
    </div>

    {{-- Contract Status --}}
    @if($moveOutContractAgreed)
        <div class="mt-6 bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
            <span class="text-sm font-bold text-emerald-700">Move-Out Contract Fully Signed</span>
            <p class="text-[11px] text-emerald-600 mt-1">All parties have signed this agreement electronically per RA 8792.</p>
        </div>
    @endif

    <p class="text-xs text-gray-500 text-center mt-6 italic">This Agreement is executed in two (2) original copies — one for the Lessor and one for the Lessee.</p>
</div>

{{-- APPENDIX — Tenant Valid ID --}}
<div class="border-t pt-6">
    <div class="text-center mb-4">
        <h3 class="text-sm font-bold text-[#3B5998] uppercase">Appendix — Tenant Valid ID</h3>
        <p class="text-xs text-gray-500">Attached copy of the tenant's government-issued identification</p>
    </div>
    @if($t['personal_info']['government_id_image'] ?? null)
        <div class="flex flex-col items-center">
            <div class="border-2 border-gray-200 rounded-xl overflow-hidden bg-gray-50 p-3 max-w-lg w-full">
                <img src="{{ route('secure.file', $t['personal_info']['government_id_image']) }}" class="w-full object-contain rounded-lg" alt="Tenant Valid ID">
            </div>
            <div class="mt-3 text-center text-sm">
                <p class="text-gray-600"><span class="font-semibold">ID Type:</span> {{ $t['personal_info']['government_id_type'] ?? '—' }}</p>
                <p class="text-gray-600"><span class="font-semibold">ID Number:</span> {{ $t['personal_info']['government_id_number'] ?? '—' }}</p>
                <p class="text-gray-600"><span class="font-semibold">Name:</span> {{ $t['personal_info']['first_name'] }} {{ $t['personal_info']['last_name'] }}</p>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-10 text-gray-400">
            <svg class="w-12 h-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h2.25M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
            <p class="text-sm">No ID image uploaded</p>
        </div>
    @endif
</div>

{{-- Footer --}}
<div class="border-t pt-3 mt-6 text-center">
    <p class="text-[11px] text-gray-400">This document is confidential and intended solely for the parties named herein.</p>
</div>
