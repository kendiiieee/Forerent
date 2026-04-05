{{--
    Move-In Contract Body — Reusable Partial

    Required variables:
    - $t                    : tenant data array (lessor_info, personal_info, contact_info, rent_details, move_in_details)
    - $rate                 : monthly rate
    - $deposit              : security deposit
    - $premium              : short-term premium
    - $dueDay               : monthly due day
    - $dueSfx               : suffix (st, nd, rd, th)
    - $totalMoveIn          : advance + deposit
    - $inspectionChecklist  : array of checklist items (optional, defaults to [])
    - $itemsReceived        : array of received items (optional, defaults to [])
    - $tenantSignature      : tenant signature path (nullable)
    - $ownerSignature       : owner signature path (nullable)
    - $tenantSignedAt       : formatted date string (nullable)
    - $ownerSignedAt        : formatted date string (nullable)
    - $contractAgreed       : bool
    - $signatureMode        : 'manager' (interactive sign buttons) or 'tenant' (read-only display)
--}}

@php
    $inspectionChecklist = $inspectionChecklist ?? [];
    $itemsReceived = $itemsReceived ?? [];
    $signatureMode = $signatureMode ?? 'tenant';
    $contractSettings = $contractSettings ?? [];

    $defaultInclusions = [
        'Association dues / condo or building fees',
        'Wi-Fi / Internet access',
        'Access to building amenities (pool, gym, function areas, etc.)',
        'Housekeeping / common-area cleaning',
        'Use of shared appliances',
        '24/7 building security',
        'Furnished room (bed, cabinet, air conditioning, etc.)',
        'Water utility',
    ];
    $defaultExclusions = [
        'Electricity (split equally among unit tenants)',
        'Water (if not included above)',
        'Laundry services',
        'Parking fees',
    ];
    $defaultHouseRules = [
        'No overnight visitors or unauthorized guests. Visitors must leave by the designated curfew time.',
        'No smoking inside the unit or building common areas.',
        'No illegal drugs, substances, or activities of any kind.',
        'No pets allowed within the premises unless explicitly permitted in writing.',
        'Observe quiet hours from 10:00 PM to 6:00 AM.',
        'No unauthorized room transfers, subletting, or sharing of assigned bed with another person.',
        'No tampering with air conditioning units, electrical systems, or building infrastructure.',
        'Report all maintenance issues to the dormitory administration promptly.',
        'Keep personal area and all shared spaces clean and orderly.',
        'Follow proper garbage disposal and recycling procedures.',
        'Respect fellow tenants\' privacy, belongings, and personal space.',
        'Comply with all building management rules and regulations.',
    ];
    $defaultPenalties = 'First offense — written warning. Second offense — fine of PHP 500.00. Third offense — grounds for lease termination with possible deposit forfeiture. Serious violations (illegal activity, property destruction) may result in immediate termination.';

    $inclusions = data_get($contractSettings, 'inclusions', $defaultInclusions);
    $exclusions = data_get($contractSettings, 'exclusions', $defaultExclusions);
    $houseRules = data_get($contractSettings, 'house_rules', $defaultHouseRules);
    $penaltySchedule = data_get($contractSettings, 'penalty_schedule', $defaultPenalties);
@endphp

{{-- Page Header --}}
<div style="background-color: #1a1a4e; margin: -2rem -2rem 0 -2rem; padding: 0.85rem 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <p style="font-size: 0.875rem; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 0.025em;">Dormitory Rental Agreement</p>
        <p style="font-size: 10px; color: #d1d5db;">Republic of the Philippines</p>
    </div>
    <p style="font-size: 0.75rem; font-weight: 600; color: #d1d5db; text-transform: uppercase; letter-spacing: 0.05em;">Move-In Contract</p>
</div>

{{-- Title --}}
<div class="text-center py-4">
    <h1 class="text-2xl font-bold text-gray-900 uppercase">Move-In Contract</h1>
    <p class="text-sm text-gray-500 mt-1">Dormitory Bedspace / Room Lease Agreement</p>
</div>
<p class="text-sm text-gray-700 leading-relaxed">This Move-In Contract ("Agreement") is entered into by and between the <strong>LESSOR</strong> (the dormitory owner or authorized operator) and the <strong>LESSEE</strong> (the tenant), under the terms and conditions set forth below, in compliance with Republic Act No. 9653 (Rent Control Act of 2009) and other applicable laws of the Republic of the Philippines.</p>

{{-- SECTION 1 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 1 — Parties to the Agreement</h3>
    <p class="font-bold text-xs uppercase text-gray-600 mb-2">Lessor (Dormitory Owner / Operator)</p>
    <table class="w-full border border-gray-300 text-sm mb-4"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Business / Trade Name:</td><td class="p-2">{{ $t['lessor_info']['business_name'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Registered Company Name:</td><td class="p-2">{{ $t['lessor_info']['company_name'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Business Address:</td><td class="p-2">{{ $t['lessor_info']['address'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Contact Number:</td><td class="p-2">{{ $t['lessor_info']['contact'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Email:</td><td class="p-2">{{ $t['lessor_info']['email'] ?? '—' }}</td></tr>
        <tr><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Authorized Representative:</td><td class="p-2">{{ $t['lessor_info']['representative'] ?? '—' }}</td></tr>
    </tbody></table>
    <p class="font-bold text-xs uppercase text-gray-600 mb-2">Lessee (Tenant)</p>
    <table class="w-full border border-gray-300 text-sm mb-2"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Full Legal Name:</td><td class="p-2">{{ $t['personal_info']['first_name'] }} {{ $t['personal_info']['last_name'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Permanent Home Address:</td><td class="p-2">{{ $t['personal_info']['permanent_address'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Contact Number:</td><td class="p-2">{{ $t['contact_info']['contact_number'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Email:</td><td class="p-2">{{ $t['contact_info']['email'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Valid Government ID Type:</td><td class="p-2">{{ $t['personal_info']['government_id_type'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">ID Number:</td><td class="p-2">{{ $t['personal_info']['government_id_number'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Company / School:</td><td class="p-2">{{ $t['personal_info']['company_school'] ?? '—' }}</td></tr>
        <tr><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Position / Course:</td><td class="p-2">{{ $t['personal_info']['position_course'] ?? '—' }}</td></tr>
    </tbody></table>
    <p class="font-bold text-xs text-gray-600 mb-1">Emergency Contact Person</p>
    <table class="w-full border border-gray-300 text-sm"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Full Name:</td><td class="p-2">{{ $t['personal_info']['emergency_contact_name'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Relationship:</td><td class="p-2">{{ $t['personal_info']['emergency_contact_relationship'] ?? '—' }}</td></tr>
        <tr><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Contact Number:</td><td class="p-2">{{ $t['personal_info']['emergency_contact_number'] ?? '—' }}</td></tr>
    </tbody></table>
</div>

{{-- SECTION 2 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 2 — Property Details</h3>
    <table class="w-full border border-gray-300 text-sm"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Building / Property Name:</td><td class="p-2">{{ $t['personal_info']['property'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Complete Address:</td><td class="p-2">{{ $t['personal_info']['address'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Unit / Room Number:</td><td class="p-2">{{ $t['personal_info']['unit'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Floor:</td><td class="p-2">{{ $t['rent_details']['floor'] ?? '—' }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Bed Assignment:</td><td class="p-2">{{ $t['rent_details']['bed_number'] }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Room Type:</td><td class="p-2">{{ $t['rent_details']['room_type'] ?? '—' }}</td></tr>
        <tr><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Gender Policy:</td><td class="p-2">{{ $t['rent_details']['dorm_type'] }}</td></tr>
    </tbody></table>
</div>

{{-- SECTION 3 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 3 — Lease Term</h3>
    <table class="w-full border border-gray-300 text-sm mb-3"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Lease Start Date:</td><td class="p-2">{{ \Carbon\Carbon::parse($t['rent_details']['lease_start_date'])->format('F d, Y') }}</td></tr>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Lease End Date:</td><td class="p-2">{{ \Carbon\Carbon::parse($t['rent_details']['lease_end_date'])->format('F d, Y') }}</td></tr>
        <tr><td class="p-2 font-semibold text-gray-600 border-r bg-gray-50">Minimum Stay Period:</td><td class="p-2">{{ $t['rent_details']['lease_term'] }} Month(s)</td></tr>
    </tbody></table>
    <p class="text-xs text-gray-600 leading-relaxed">This is a fixed-term lease. The lease shall automatically expire on the Lease End Date stated above. No separate notice to vacate is required for normal end-of-lease move-outs. If the Lessee wishes to renew, both parties must execute a new Agreement in writing before the Lease End Date. For early termination (moving out before the Lease End Date), refer to Section 7 of this Agreement.</p>
</div>

{{-- SECTION 4 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 4 — Rent and Payment Terms</h3>
    <table class="w-full border border-gray-300 text-sm mb-3">
        <thead><tr class="bg-[#3B5998] text-white"><th class="p-2 text-left">Item</th><th class="p-2 text-left">Description</th><th class="p-2 text-right">Amount (PHP)</th></tr></thead>
        <tbody>
            <tr class="border-b"><td class="p-2 font-semibold">Monthly Rent</td><td class="p-2 text-gray-600">Base monthly rate for the assigned bed / room</td><td class="p-2 text-right">&#8369; {{ number_format($rate, 2) }}</td></tr>
            <tr class="border-b"><td class="p-2 font-semibold">Short-Term Premium</td><td class="p-2 text-gray-600">PHP 500/month — automatically applied when lease term is below 6 months</td><td class="p-2 text-right">{{ $premium > 0 ? '₱ 500.00' : '—' }}</td></tr>
            <tr class="border-b"><td class="p-2 font-semibold">1 Month Advance</td><td class="p-2 text-gray-600">Covers the first month of occupancy</td><td class="p-2 text-right">&#8369; {{ number_format($rate, 2) }}</td></tr>
            <tr class="border-b"><td class="p-2 font-semibold">Security Deposit (max 2 months per RA 9653)</td><td class="p-2 text-gray-600">Refundable upon move-out, subject to inspection and clearance</td><td class="p-2 text-right">&#8369; {{ number_format($deposit, 2) }}</td></tr>
            <tr class="bg-gray-50"><td class="p-2 font-bold">TOTAL MOVE-IN COST</td><td class="p-2 text-gray-600">Advance + Deposit</td><td class="p-2 text-right font-bold">&#8369; {{ number_format($totalMoveIn, 2) }}</td></tr>
        </tbody>
    </table>
    <table class="w-full border border-gray-300 text-sm mb-3"><tbody>
        <tr class="border-b"><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Monthly Due Date:</td><td class="p-2">{{ $dueDay ? $dueDay . $dueSfx . ' of the month' : '—' }}</td></tr>
        <tr><td class="p-2 font-semibold text-gray-600 w-1/3 border-r bg-gray-50">Accepted Payment Methods:</td><td class="p-2">GCash, Maya, Bank Transfer, Cash</td></tr>
    </tbody></table>
    <p class="text-xs text-gray-700 leading-relaxed mb-2"><strong>Short-Term Premium:</strong> A fixed charge of PHP 500.00 per month is automatically applied when the lease term is below six (6) months. This will be reflected in the monthly billing statement.</p>
    <p class="text-xs text-gray-700 leading-relaxed mb-3"><strong>Late Payment Penalty:</strong> A penalty of {{ $t['move_in_details']['late_payment_penalty'] ?? 1 }}% of the monthly rent per day of delay shall be automatically computed and applied to any rent payment received after the monthly due date.</p>
    <ul class="text-xs text-gray-600 space-y-1 list-disc pl-5">
        <li>Under RA 9653, the Lessor cannot demand more than one (1) month advance rent and two (2) months' security deposit.</li>
        <li>The security deposit shall be placed in a bank account under the Lessor's name. Interest earned shall be returned to the Lessee upon lease expiration.</li>
        <li>The security deposit shall NOT be applied as monthly rent during the lease term. It is refundable only upon move-out after inspection and clearance.</li>
        <li>Utility charges (electricity, water, etc.) that are not included in the base rent shall be billed separately, split equally among tenants in the unit, and prorated for mid-month move-ins.</li>
    </ul>
</div>

{{-- SECTION 4A --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 4A — Reservation Policy</h3>
    <p class="text-xs text-gray-700 leading-relaxed">There is no reservation fee. Once a tenant confirms intent to rent a specific slot, the slot shall be held for a maximum of three (3) calendar days. If the tenant fails to complete the full move-in payment (advance + deposit) within the 3-day holding period, the slot shall automatically be released and made available to other prospective tenants. No financial obligation arises from the reservation hold itself.</p>
</div>

{{-- SECTION 5 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 5 — Rent Inclusions and Exclusions</h3>
    <p class="text-xs font-bold text-gray-700 mb-1">The following items are included in the monthly rent:</p>
    <ul class="text-xs text-gray-600 list-disc pl-5 space-y-0.5 mb-3">
        @foreach($inclusions as $item)<li>{{ $item }}</li>@endforeach
    </ul>
    <p class="text-xs font-bold text-gray-700 mb-1">The following items are NOT included and will be billed separately:</p>
    <ul class="text-xs text-gray-600 list-disc pl-5 space-y-0.5">
        @foreach($exclusions as $item)<li>{{ $item }}</li>@endforeach
    </ul>
</div>

{{-- SECTION 6 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 6 — House Rules and Policies</h3>
    <p class="text-xs text-gray-700 mb-2">The Lessee agrees to abide by the following rules at all times:</p>
    <ul class="text-xs text-gray-600 list-disc pl-5 space-y-0.5 mb-3">
        @foreach($houseRules as $rule)<li>{{ $rule }}</li>@endforeach
    </ul>
    <p class="text-xs text-gray-700"><strong>Violation Penalties:</strong> {{ $penaltySchedule }}</p>
</div>

{{-- SECTION 7 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 7 — Early Termination</h3>
    <p class="text-xs text-gray-700 mb-2">Early termination means the Lessee vacates before the Lease End Date specified in Section 3. If early termination occurs, the following shall apply:</p>
    <ul class="text-xs text-gray-600 list-disc pl-5 space-y-1">
        <li>The Lessee must provide a minimum of thirty (30) calendar days' written notice of intent to vacate early.</li>
        <li>The security deposit shall be automatically forfeited in full as liquidated damages.</li>
        <li>Any outstanding utility balances, unpaid rent, and other charges must be settled in full before vacating.</li>
        <li>No additional early termination fee shall be charged beyond the deposit forfeiture.</li>
    </ul>
</div>

{{-- SECTION 8 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 8 — Move-In Room Condition Checklist</h3>
    <p class="text-xs text-gray-700 mb-3">Both parties shall inspect the room on the move-in date and record the condition of each item below.</p>
    <table class="w-full border border-gray-300 text-xs">
        <thead><tr class="bg-[#3B5998] text-white"><th class="p-2 text-left">Item</th><th class="p-2 text-center w-16">Good</th><th class="p-2 text-center w-20">Damaged</th><th class="p-2 text-center w-18">Missing</th><th class="p-2 text-left">Remarks</th></tr></thead>
        <tbody>
            @foreach($inspectionChecklist as $item)
                <tr class="border-b">
                    <td class="p-2">{{ $item['item_name'] }}</td>
                    <td class="p-2 text-center border-l">@if($item['condition'] === 'good') &#10003; @endif</td>
                    <td class="p-2 text-center border-l">@if($item['condition'] === 'damaged') &#10003; @endif</td>
                    <td class="p-2 text-center border-l">@if($item['condition'] === 'missing') &#10003; @endif</td>
                    <td class="p-2 border-l">{{ $item['remarks'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- SECTION 9 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 9 — Items Received by Tenant</h3>
    <table class="w-full border border-gray-300 text-xs">
        <thead><tr class="bg-[#3B5998] text-white"><th class="p-2 text-left">Item</th><th class="p-2 text-center w-12">Qty</th><th class="p-2 text-left">Condition</th><th class="p-2 text-center w-24">Confirmed</th></tr></thead>
        <tbody>
            @foreach($itemsReceived as $item)
                <tr class="border-b">
                    <td class="p-2">{{ $item['item_name'] }}</td>
                    <td class="p-2 text-center border-l">{{ $item['quantity'] ?: '' }}</td>
                    <td class="p-2 border-l">{{ $item['condition'] ?? '' }}</td>
                    <td class="p-2 text-center border-l">@if($item['tenant_confirmed']) &#10003; @endif</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- SECTION 10 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 10 — Billing and Payments During Active Tenancy</h3>
    <p class="text-xs text-gray-700 mb-2">During the active lease period, the following billing rules shall apply:</p>
    <ul class="text-xs text-gray-600 list-disc pl-5 space-y-1">
        <li>A monthly billing statement shall be generated and issued to the Lessee on or before the 1st of each month, showing all charges due for the current period.</li>
        <li>The billing statement shall include the base monthly rent, electricity share, water share, short-term premium (if applicable at PHP 500/month for leases under 6 months), and any conditional charges.</li>
        <li>Electricity and water utility charges shall be computed by dividing the total unit bill equally among all active tenants in the room. Mid-month move-ins shall be prorated by the number of days occupied.</li>
        <li>Late Payment Penalty: {{ $t['move_in_details']['late_payment_penalty'] ?? 1 }}% of the monthly rent per day shall be automatically computed and added to the next billing statement for any payment received after the monthly due date.</li>
        <li>A payment receipt with an Official Receipt (OR) number shall be generated upon confirmed payment, as required by RA 9653 and BIR regulations.</li>
        <li>Accepted payment methods, payment history, and downloadable receipts shall be made available to the Lessee.</li>
    </ul>
</div>

{{-- SECTION 11 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 11 — Lease Renewal</h3>
    <p class="text-xs text-gray-700 leading-relaxed">The Lessor shall send a renewal notice to the Lessee at least thirty (30) days before the Lease End Date. The notice shall include any rate adjustments, which shall not exceed the maximum increase allowed under RA 9653. If the Lessee confirms renewal, a new contract shall be signed with updated terms. If the Lessee does not wish to renew, the lease simply expires on the Lease End Date and the move-out process (Section 12) applies.</p>
</div>

{{-- SECTION 12 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 12 — Move-Out Inspection and Clearance</h3>
    <p class="text-xs text-gray-700 mb-2">This section applies when the lease ends on the Lease End Date (normal expiration) or after the 30-day notice period for early termination (Section 7).</p>
    <p class="text-xs font-bold text-gray-700 mb-1">Move-Out Inspection:</p>
    <ul class="text-xs text-gray-600 list-disc pl-5 space-y-0.5 mb-3">
        <li>The Lessor and Lessee shall conduct a joint room inspection, comparing current conditions against the move-in checklist and photos (Section 8).</li>
        <li>Any damages beyond normal wear and tear shall be documented and the repair cost deducted from the security deposit.</li>
        <li>The Lessee must return all keys, access cards, and borrowed items.</li>
        <li>The Lessor shall sign off on a clearance form upon satisfactory completion.</li>
    </ul>
    <p class="text-xs font-bold text-gray-700 mb-1">Deposit Refund (Normal End of Lease Only):</p>
    <ul class="text-xs text-gray-600 list-disc pl-5 space-y-0.5">
        <li>The deposit refund shall be calculated as: Original Deposit – Unpaid Utility Balance – Damage Repair Costs – Lost Key/Card Replacement + Interest Earned = Net Deposit Refund.</li>
        <li>If the Lessee terminated early (Section 7), the deposit is forfeited in full and no refund applies.</li>
        <li>Refund shall be processed within thirty (30) days after move-out and clearance.</li>
        <li>Refund shall be issued via the same payment method used or via bank transfer.</li>
    </ul>
</div>

{{-- SECTION 13 --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 13 — Governing Law and Dispute Resolution</h3>
    <p class="text-xs text-gray-700 leading-relaxed">This Agreement shall be governed by the laws of the Republic of the Philippines, including RA 9653 (Rent Control Act of 2009). Disputes shall first be settled through amicable negotiation, then Barangay mediation, then proper courts.</p>
</div>

{{-- SECTION 14: Signatures --}}
<div>
    <h3 class="text-sm font-bold text-[#3B5998] uppercase mb-3 border-b border-gray-200 pb-1">Section 14 — Agreement and Signatures</h3>
    <p class="text-xs text-gray-700 mb-4">By signing below, both parties acknowledge that they have read, understood, and voluntarily agree to all terms and conditions stated in this Move-In Contract.</p>

    {{-- Signature display (read-only for both views) --}}
    <div class="grid grid-cols-2 gap-8 mt-4">
        {{-- Tenant Signature --}}
        <div class="text-center">
            @if($tenantSignature)
                <div class="border-2 border-emerald-200 bg-emerald-50/50 rounded-xl h-24 mb-2 flex items-center justify-center p-2">
                    <img src="{{ asset('storage/' . $tenantSignature) }}" class="max-h-full max-w-full object-contain" alt="Tenant Signature">
                </div>
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-800">{{ $t['personal_info']['first_name'] }} {{ $t['personal_info']['last_name'] }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-1">Signed: {{ $tenantSignedAt }}</p>
            @else
                <div class="border-2 border-dashed border-gray-300 rounded-xl h-24 mb-2 flex items-center justify-center">
                    <span class="text-[11px] text-gray-400">Awaiting tenant signature</span>
                </div>
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-500">{{ $t['personal_info']['first_name'] }} {{ $t['personal_info']['last_name'] }}</p>
                <p class="text-[11px] text-gray-400 mt-1">Tenant's Signature</p>
            @endif
        </div>

        {{-- Owner/Manager Signature --}}
        <div class="text-center">
            @if($ownerSignature)
                <div class="border-2 border-emerald-200 bg-emerald-50/50 rounded-xl h-24 mb-2 flex items-center justify-center p-2">
                    <img src="{{ asset('storage/' . $ownerSignature) }}" class="max-h-full max-w-full object-contain" alt="Owner Signature">
                </div>
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-800">{{ $t['lessor_info']['representative'] }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-1">Signed: {{ $ownerSignedAt }}</p>
            @else
                @if($signatureMode === 'manager')
                    <button
                        wire:click="openSignatureModal('owner')"
                        class="w-full border-2 border-dashed border-indigo-300 bg-indigo-50/30 rounded-xl h-24 mb-2 flex flex-col items-center justify-center hover:bg-indigo-50 hover:border-indigo-400 transition-all cursor-pointer group"
                    >
                        <svg class="w-6 h-6 text-indigo-400 group-hover:text-indigo-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        <span class="text-[11px] font-semibold text-indigo-500 group-hover:text-indigo-600">Click to Sign</span>
                    </button>
                @else
                    <div class="border-2 border-dashed border-gray-300 rounded-xl h-24 mb-2 flex items-center justify-center">
                        <span class="text-[11px] text-gray-400">Awaiting signature</span>
                    </div>
                @endif
                <div class="border-b border-gray-400 mb-1"></div>
                <p class="text-xs font-semibold text-gray-500">{{ $t['lessor_info']['representative'] }}</p>
                <p class="text-[11px] text-gray-400 mt-1">Lessor / Authorized Representative</p>
            @endif
        </div>
    </div>

    {{-- Contract Status --}}
    @if($contractAgreed)
        <div class="mt-6 bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
            <span class="text-sm font-bold text-emerald-700">Contract Fully Signed</span>
            <p class="text-[11px] text-emerald-600 mt-1">Both parties have signed this agreement electronically per RA 8792.</p>
        </div>
    @endif

    <p class="text-xs text-gray-500 text-center mt-6 italic">This Agreement is executed in two (2) original copies — one for the Lessor and one for the Lessee.</p>
</div>

{{-- APPENDIX --}}
<div class="border-t pt-6">
    <div class="text-center mb-4">
        <h3 class="text-sm font-bold text-[#3B5998] uppercase">Appendix — Tenant Valid ID</h3>
        <p class="text-xs text-gray-500">Attached copy of the tenant's government-issued identification</p>
    </div>
    @if($t['personal_info']['government_id_image'] ?? null)
        <div class="flex flex-col items-center">
            <div class="border-2 border-gray-200 rounded-xl overflow-hidden bg-gray-50 p-3 max-w-lg w-full">
                <img src="{{ asset('storage/' . $t['personal_info']['government_id_image']) }}" class="w-full object-contain rounded-lg" alt="Tenant Valid ID">
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
