<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move-Out Contract — {{ $tenant['personal_info']['first_name'] }} {{ $tenant['personal_info']['last_name'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 18mm 20mm 20mm 20mm;
            background: #fff;
            page-break-after: always;
            position: relative;
        }
        .page:last-child {
            page-break-after: auto;
        }

        .confidential-banner {
            text-align: center;
            font-size: 7.5pt;
            color: #555;
            border-top: 0.5pt solid #999;
            border-bottom: 0.5pt solid #999;
            padding: 2px 0;
            margin-bottom: 10px;
            letter-spacing: 0.3px;
        }

        .doc-header-banner {
            background: #1a2744;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            margin-bottom: 4px;
            border-bottom: 3px solid #2360E8;
        }
        .doc-header-banner .banner-left .doc-title-main {
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #fff;
            margin: 0;
        }
        .doc-header-banner .banner-left .republic {
            font-size: 8.5pt;
            letter-spacing: 0.5px;
            color: #ccc;
            margin-top: 2px;
        }
        .doc-header-banner .banner-right {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #fff;
        }

        .divider-thin {
            border: none;
            border-top: 0.5pt solid #ccc;
            margin: 6px 0;
        }

        .intro {
            font-size: 9.5pt;
            text-align: justify;
            line-height: 1.55;
            margin-bottom: 12px;
            color: #222;
        }

        .section-heading {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #070589;
            background: #EEF2FF;
            padding: 4px 8px;
            margin: 14px 0 8px;
            border-left: 3px solid #2360E8;
        }

        .sub-heading {
            font-size: 9.5pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 8px 0 5px;
            color: #111;
        }

        .field-row {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            margin-bottom: 6px;
            font-size: 9.5pt;
        }
        .field-row.two-col {
            flex-wrap: wrap;
            gap: 6px 20px;
        }
        .field-row.two-col > .field-row {
            min-width: 200px;
        }
        .field-label {
            white-space: nowrap;
            color: #333;
            flex-shrink: 0;
        }
        .field-value {
            border-bottom: 0.75pt solid #333;
            flex: 1;
            min-width: 80px;
            padding-bottom: 1px;
            font-weight: bold;
            color: #000;
            font-size: 9.5pt;
        }

        .inspection-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin: 8px 0;
        }
        .inspection-table th {
            background: #070589;
            color: #fff;
            padding: 4px 6px;
            text-align: center;
            font-size: 8pt;
        }
        .inspection-table th:first-child {
            text-align: left;
        }
        .inspection-table td {
            padding: 5px 6px;
            border: 0.5pt solid #ccc;
            vertical-align: middle;
        }
        .inspection-table td.check-cell {
            text-align: center;
        }
        .inspection-table tr:nth-child(even) td {
            background: #f9f9ff;
        }
        .damage-row td {
            background: #fff5f5 !important;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin: 8px 0;
        }
        .items-table th {
            background: #070589;
            color: #fff;
            padding: 4px 8px;
            font-size: 8pt;
            text-align: left;
        }
        .items-table td {
            padding: 5px 8px;
            border: 0.5pt solid #ccc;
        }
        .items-table tr:nth-child(even) td {
            background: #f9f9ff;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 9pt;
        }
        .payment-table th {
            background: #070589;
            color: #fff;
            padding: 5px 8px;
            text-align: left;
            font-size: 8.5pt;
        }
        .payment-table th.right,
        .payment-table td.right {
            text-align: right;
        }
        .payment-table td {
            padding: 5px 8px;
            border-bottom: 0.5pt solid #ddd;
            vertical-align: top;
            color: #111;
        }
        .payment-table tr.total-row td {
            font-weight: bold;
            border-top: 1pt solid #070589;
            background: #EEF2FF;
            color: #070589;
        }

        .policy-list {
            margin: 6px 0 6px 18px;
            font-size: 9pt;
            line-height: 1.6;
            color: #222;
        }
        .policy-list li {
            margin-bottom: 3px;
        }

        .signature-section {
            margin-top: 24px;
        }
        .sig-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 20px;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-top: 1pt solid #000;
            margin-top: 42px;
            margin-bottom: 4px;
        }
        .sig-label {
            font-size: 8.5pt;
            color: #333;
            line-height: 1.4;
        }
        .sig-date-row {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            margin-top: 8px;
            font-size: 9pt;
            justify-content: center;
        }
        .sig-date-line {
            border-bottom: 0.75pt solid #333;
            width: 120px;
        }

        .doc-footer {
            text-align: center;
            font-size: 7.5pt;
            color: #666;
            border-top: 0.5pt solid #ccc;
            padding-top: 5px;
            margin-top: 10px;
        }

        .law-note {
            font-size: 8.5pt;
            background: #f0f4ff;
            border-left: 2.5pt solid #2360E8;
            padding: 5px 8px;
            margin: 5px 0;
            color: #333;
        }

        @media print {
            body { background: #fff; }
            .page {
                width: 210mm;
                margin: 0;
                padding: 15mm 18mm 18mm 18mm;
                page-break-after: always;
            }
            .page:last-child { page-break-after: auto; }
        }

        @media screen {
            body { background: #e0e4ef; padding: 20px 0; }
            .page { box-shadow: 0 2px 16px rgba(0,0,0,0.15); margin-bottom: 20px; }
        }
    </style>
</head>
<body>

@php
    $moveInChecklist = $moveInChecklist ?? [];
    $moveOutChecklist = $moveOutChecklist ?? [];
    $itemsReturned = $itemsReturned ?? [];
    $outstandingBalances = $outstandingBalances ?? [];
    $depositRefund = $depositRefund ?? [];
    $deposit = $tenant['move_in_details']['security_deposit'] ?? 0;
    $checklistItemNames = \App\Livewire\Concerns\InspectionConfig::CHECKLIST_ITEMS;
    $returnItemNames = \App\Livewire\Concerns\InspectionConfig::RETURNED_ITEMS;
@endphp

{{-- ═══════════════════════════════════════ PAGE 1 ═══════════════════════════════════════ --}}
<div class="page">

    <div class="doc-header-banner">
        <div class="banner-left">
            <div class="doc-title-main">Dormitory Rental Agreement</div>
            <div class="republic">Republic of the Philippines</div>
        </div>
        <div class="banner-right">Move-Out Clearance</div>
    </div>
    <div class="confidential-banner">This document is confidential and intended solely for the parties named herein.</div>

    <p class="intro">
        This Move-Out Clearance and Deposit Settlement Agreement (<strong>"Agreement"</strong>) is entered into by and between the <strong>LESSOR</strong> (the dormitory owner or authorized operator) and the <strong>LESSEE</strong> (the tenant) to formally document the termination of the lease, the condition of the premises at move-out, and the settlement of all outstanding financial obligations, in compliance with <em>Republic Act No. 9653</em> and other applicable Philippine laws.
    </p>

    {{-- SECTION 1 — PARTIES --}}
    <div class="section-heading">Section 1 — Parties</div>

    <div class="sub-heading">LESSOR</div>
    <div class="field-row">
        <span class="field-label">Business / Trade Name:</span>
        <span class="field-value">{{ $tenant['lessor_info']['business_name'] ?? '' }}</span>
    </div>
    <div class="field-row">
        <span class="field-label">Authorized Representative:</span>
        <span class="field-value">{{ $tenant['lessor_info']['representative'] ?? '' }}</span>
    </div>
    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Contact Number:</span>
            <span class="field-value">{{ $tenant['lessor_info']['contact'] ?? '' }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Email Address:</span>
            <span class="field-value">{{ $tenant['lessor_info']['email'] ?? '' }}</span>
        </div>
    </div>

    <div class="sub-heading">LESSEE</div>
    <div class="field-row">
        <span class="field-label">Full Legal Name:</span>
        <span class="field-value">{{ $tenant['personal_info']['first_name'] }} {{ $tenant['personal_info']['last_name'] }}</span>
    </div>
    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Contact Number:</span>
            <span class="field-value">{{ $tenant['contact_info']['contact_number'] }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Email Address:</span>
            <span class="field-value">{{ $tenant['contact_info']['email'] }}</span>
        </div>
    </div>
    <div class="field-row">
        <span class="field-label">Forwarding Address:</span>
        <span class="field-value">{{ $tenant['move_out_details']['forwarding_address'] ?? '' }}</span>
    </div>

    {{-- SECTION 2 — LEASE REFERENCE --}}
    <div class="section-heading">Section 2 — Lease Reference</div>

    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Original Move-In Date:</span>
            <span class="field-value">{{ ($tenant['move_in_details']['move_in_date'] ?? null) ? \Carbon\Carbon::parse($tenant['move_in_details']['move_in_date'])->format('F d, Y') : '' }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Actual Move-Out Date:</span>
            <span class="field-value">{{ ($tenant['move_out_details']['move_out_date'] ?? null) ? \Carbon\Carbon::parse($tenant['move_out_details']['move_out_date'])->format('F d, Y') : '' }}</span>
        </div>
    </div>
    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Lease Start Date:</span>
            <span class="field-value">{{ \Carbon\Carbon::parse($tenant['rent_details']['lease_start_date'])->format('F d, Y') }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Lease End Date:</span>
            <span class="field-value">{{ \Carbon\Carbon::parse($tenant['rent_details']['lease_end_date'])->format('F d, Y') }}</span>
        </div>
    </div>
    <div class="field-row">
        <span class="field-label">Building / Unit / Bed:</span>
        <span class="field-value">{{ $tenant['personal_info']['property'] }} / {{ $tenant['personal_info']['unit'] }} / {{ $tenant['rent_details']['bed_number'] }}</span>
    </div>
    <div class="field-row">
        <span class="field-label">Reason for Vacating:</span>
        <span class="field-value">{{ $tenant['move_out_details']['reason_for_vacating'] ?? '' }}</span>
    </div>

    {{-- SECTION 3 — MOVE-OUT ROOM CONDITION INSPECTION --}}
    <div class="section-heading">Section 3 — Move-Out Room Condition Inspection</div>

    <p style="font-size:8.5pt; color:#333; line-height:1.55; margin:0 0 6px;">
        Both parties shall conduct a joint room inspection on the move-out date. The condition of each item below is compared against the Move-In Checklist to identify damages beyond normal wear and tear.
    </p>

    <table class="inspection-table">
        <thead>
        <tr>
            <th style="text-align:left;">Item</th>
            <th>Move-In</th>
            <th>Move-Out</th>
            <th>Damage?</th>
            <th style="text-align:right;">Repair Cost</th>
        </tr>
        </thead>
        <tbody>
        @php $totalRepairCost = 0; @endphp
        @foreach($checklistItemNames as $itemName)
            @php
                $moveInItem = collect($moveInChecklist)->firstWhere('item_name', $itemName);
                $moveOutItem = collect($moveOutChecklist)->firstWhere('item_name', $itemName);
                $moveInCond = $moveInItem['condition'] ?? '';
                $moveOutCond = $moveOutItem['condition'] ?? '';
                $damageFound = $moveInCond && $moveOutCond && $moveInCond !== $moveOutCond && $moveOutCond !== 'good';
                $repairCost = (float) ($moveOutItem['repair_cost'] ?? 0);
                if ($damageFound) $totalRepairCost += $repairCost;
            @endphp
            <tr class="{{ $damageFound ? 'damage-row' : '' }}">
                <td style="font-weight:500;">{{ $itemName }}</td>
                <td class="check-cell" style="text-transform:capitalize;">{{ $moveInCond }}</td>
                <td class="check-cell" style="text-transform:capitalize; {{ $damageFound ? 'color:#c0392b; font-weight:bold;' : '' }}">{{ $moveOutCond }}</td>
                <td class="check-cell" style="{{ $damageFound ? 'color:#c0392b; font-weight:bold;' : '' }}">{{ $damageFound ? 'Yes' : ($moveOutCond ? 'No' : '') }}</td>
                <td style="text-align:right;">{{ $damageFound && $repairCost > 0 ? '₱ ' . number_format($repairCost, 2) : '' }}</td>
            </tr>
        @endforeach
        @if($totalRepairCost > 0)
        <tr><td colspan="4" style="font-weight:bold; text-align:right;">Total Repair Cost</td><td style="text-align:right; font-weight:bold;">₱ {{ number_format($totalRepairCost, 2) }}</td></tr>
        @endif
        </tbody>
    </table>

    <hr class="divider-thin" style="margin-top:auto;">
    <div class="doc-footer">DORMITORY RENTAL AGREEMENT &nbsp;|&nbsp; Republic of the Philippines &nbsp;|&nbsp; MOVE-OUT CLEARANCE</div>
</div>


{{-- ═══════════════════════════════════════ PAGE 2 ═══════════════════════════════════════ --}}
<div class="page">

    <div class="doc-header-banner">
        <div class="banner-left">
            <div class="doc-title-main">Dormitory Rental Agreement</div>
            <div class="republic">Republic of the Philippines | Move-Out Clearance</div>
        </div>
        <div class="banner-right">Page 2</div>
    </div>
    <div class="confidential-banner">This document is confidential and intended solely for the parties named herein.</div>

    {{-- SECTION 4 — ITEMS RETURNED --}}
    <div class="section-heading">Section 4 — Items Returned by Tenant</div>

    <table class="items-table">
        <thead>
        <tr>
            <th>Item</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:center;">Returned?</th>
            <th>Condition</th>
            <th style="text-align:right;">Replacement Cost</th>
        </tr>
        </thead>
        <tbody>
        @php $totalReplacementCost = 0; @endphp
        @foreach($returnItemNames as $itemName)
            @php
                $returned = collect($itemsReturned)->firstWhere('item_name', $itemName);
                $isReturned = $returned && ($returned['is_returned'] ?? false);
                $condition = $returned['condition'] ?? '';
                $replacementCost = (float) ($returned['replacement_cost'] ?? 0);
                if (!$isReturned && $returned) $totalReplacementCost += $replacementCost;
            @endphp
            <tr>
                <td style="font-weight:500;">{{ $itemName }}</td>
                <td style="text-align:center;">{{ $returned['quantity'] ?? '' }}</td>
                <td style="text-align:center;">{{ $isReturned ? 'Yes' : ($returned ? 'No' : '') }}</td>
                <td>{{ $condition }}</td>
                <td style="text-align:right;">{{ (!$isReturned && $returned && $replacementCost > 0) ? '₱ ' . number_format($replacementCost, 2) : '' }}</td>
            </tr>
        @endforeach
        @if($totalReplacementCost > 0)
        <tr><td colspan="4" style="font-weight:bold; text-align:right;">Total Replacement Cost</td><td style="text-align:right; font-weight:bold;">₱ {{ number_format($totalReplacementCost, 2) }}</td></tr>
        @endif
        </tbody>
    </table>

    {{-- SECTION 5 — DEPOSIT REFUND --}}
    <div class="section-heading">Section 5 — Security Deposit Refund Calculation</div>

    <div class="law-note">
        In accordance with RA 9653, the security deposit refund is calculated as follows:
    </div>

    @php
        $deductions = $depositRefund['deductions'] ?? [];
        $refundAmount = $depositRefund['refund_amount'] ?? null;
        $totalDeductions = collect($deductions)->sum('amount');
    @endphp
    <table class="payment-table" style="margin-top:8px;">
        <thead>
        <tr>
            <th>Item</th>
            <th class="right" style="width:140px;">Amount (PHP)</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><strong>Original Security Deposit Held</strong></td>
            <td class="right"><strong>&#8369; {{ number_format($deposit, 2) }}</strong></td>
        </tr>
        @forelse($deductions as $deduction)
        <tr>
            <td>(-) {{ $deduction['label'] }}</td>
            <td class="right">{{ (float) $deduction['amount'] > 0 ? '(₱ ' . number_format($deduction['amount'], 2) . ')' : 'TBD' }}</td>
        </tr>
        @empty
        <tr><td colspan="2" style="color:#999;">No deductions</td></tr>
        @endforelse
        <tr class="total-row">
            <td><strong>NET DEPOSIT REFUND</strong></td>
            <td class="right"><strong>&#8369; {{ $refundAmount !== null ? number_format($refundAmount, 2) : number_format(max(0, $deposit - $totalDeductions), 2) }}</strong></td>
        </tr>
        </tbody>
    </table>

    <div class="field-row" style="margin-top:10px;">
        <span class="field-label">Refund Method:</span>
        <span class="field-value">{{ $tenant['move_out_details']['deposit_refund_method'] ?? '' }}</span>
    </div>
    <div class="field-row">
        <span class="field-label">Account Name / Number:</span>
        <span class="field-value">{{ $tenant['move_out_details']['deposit_refund_account'] ?? '' }}</span>
    </div>

    {{-- SECTION 6 — CLEARANCE CERTIFICATION --}}
    <div class="section-heading">Section 6 — Clearance Certification</div>

    <p style="font-size:8.5pt; color:#333; margin-bottom:5px;">Both parties hereby certify the following:</p>
    <ul class="policy-list">
        <li>The joint move-out inspection has been completed and all findings are accurately recorded in this Agreement.</li>
        <li>All outstanding balances have been settled in full or will be deducted from the security deposit as agreed.</li>
        <li>All keys, access cards, and borrowed items have been returned or accounted for above.</li>
        <li>The Lessee has vacated the premises and removed all personal belongings.</li>
        <li>The Lessor agrees to process and release the deposit refund within thirty (30) calendar days from the date of this clearance.</li>
        <li>Both parties release each other from any further claims related to this lease, except as specified herein.</li>
    </ul>

    {{-- SECTION 7 — GOVERNING LAW --}}
    <div class="section-heading">Section 7 — Governing Law</div>

    <p style="font-size:9pt; color:#222; line-height:1.6; text-align:justify;">
        This Agreement is governed by the laws of the Republic of the Philippines, including <em>Republic Act No. 9653 (Rent Control Act of 2009)</em>. Any dispute shall first be resolved through amicable negotiation, then through Barangay mediation, and thereafter through the proper courts of competent jurisdiction.
    </p>

    {{-- SECTION 8 — SIGNATURES --}}
    <div class="section-heading">Section 8 — Agreement and Signatures</div>

    <p style="font-size:9pt; color:#222; line-height:1.55; margin-bottom:8px; text-align:justify;">
        By signing below, both parties confirm that the move-out inspection has been conducted, all balances have been accounted for, and they voluntarily agree to the deposit settlement terms stated herein.
    </p>

    <div class="signature-section">
        @if(!empty($tenantSignatureBase64) && !empty($ownerSignatureBase64))
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:8px 12px; margin-bottom:14px; text-align:center;">
                <span style="font-size:8pt; color:#166534; font-weight:bold;">ELECTRONICALLY SIGNED — RA 8792 COMPLIANT</span>
            </div>
        @endif

        <div class="sig-grid">
            <div class="sig-block">
                @if(!empty($tenantSignatureBase64))
                    <div style="height:60px; display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                        <img src="{{ $tenantSignatureBase64 }}" style="max-height:55px; max-width:100%;" alt="Tenant Signature">
                    </div>
                @else
                    <div class="sig-line"></div>
                @endif
                <div class="sig-label">
                    <strong>{{ $tenant['personal_info']['first_name'] }} {{ $tenant['personal_info']['last_name'] }}</strong><br>
                    Tenant's Signature Over Printed Name
                </div>
                <div class="sig-date-row">
                    <span>Date:</span>
                    @if(!empty($tenantSignedAt))
                        <span style="font-size:8.5pt; margin-left:4px;">{{ $tenantSignedAt }}</span>
                    @else
                        <div class="sig-date-line"></div>
                    @endif
                </div>
            </div>
            <div class="sig-block">
                @if(!empty($ownerSignatureBase64))
                    <div style="height:60px; display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                        <img src="{{ $ownerSignatureBase64 }}" style="max-height:55px; max-width:100%;" alt="Lessor Signature">
                    </div>
                @else
                    <div class="sig-line"></div>
                @endif
                <div class="sig-label">
                    <strong>{{ $tenant['lessor_info']['representative'] ?? '' }}</strong><br>
                    Lessor / Authorized Representative<br>Signature Over Printed Name
                </div>
                <div class="sig-date-row">
                    <span>Date:</span>
                    @if(!empty($ownerSignedAt))
                        <span style="font-size:8.5pt; margin-left:4px;">{{ $ownerSignedAt }}</span>
                    @else
                        <div class="sig-date-line"></div>
                    @endif
                </div>
            </div>
        </div>

        <p style="font-size:9pt; color:#333; margin: 16px 0 8px; font-weight:bold;">Witnessed by:</p>
        <div class="sig-grid">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Witness 1 — Signature Over Printed Name</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Witness 2 — Signature Over Printed Name</div>
            </div>
        </div>

        <p style="font-size:8.5pt; color:#555; text-align:center; margin-top:18px; font-style:italic;">
            This Agreement is executed in two (2) original copies — one for the Lessor and one for the Lessee.
        </p>
    </div>

    <hr class="divider-thin" style="margin-top:16px;">
    <div class="doc-footer">DORMITORY RENTAL AGREEMENT &nbsp;|&nbsp; Republic of the Philippines &nbsp;|&nbsp; MOVE-OUT CLEARANCE</div>
</div>

</body>
</html>
