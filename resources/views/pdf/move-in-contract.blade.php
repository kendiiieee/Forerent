<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move-In Contract — {{ $tenant['personal_info']['first_name'] }} {{ $tenant['personal_info']['last_name'] }}</title>
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

        /* ── Page wrapper ── */
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

        /* ── Confidential banner ── */
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

        /* ── Document header (banner style) ── */
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
        .doc-header {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-header .page-label {
            font-size: 8.5pt;
            color: #666;
            margin-top: 2px;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1.5pt solid #070589;
            margin: 8px 0;
        }
        .divider-thin {
            border: none;
            border-top: 0.5pt solid #ccc;
            margin: 6px 0;
        }

        /* ── Intro paragraph ── */
        .intro {
            font-size: 9.5pt;
            text-align: justify;
            line-height: 1.55;
            margin-bottom: 12px;
            color: #222;
        }

        /* ── Section heading ── */
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

        /* ── Sub-heading (LESSOR / LESSEE) ── */
        .sub-heading {
            font-size: 9.5pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 8px 0 5px;
            color: #111;
        }

        /* ── Field rows ── */
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
        .field-value.fixed {
            flex: none;
            min-width: 160px;
        }
        .field-value.short {
            min-width: 100px;
            flex: none;
        }

        /* ── Payment table ── */
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
            font-weight: bold;
            letter-spacing: 0.3px;
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
        .payment-table td.desc {
            font-size: 8.5pt;
            color: #555;
        }
        .payment-table tr:nth-child(even) td {
            background: #f7f8ff;
        }
        .payment-table tr.total-row td {
            font-weight: bold;
            border-top: 1pt solid #070589;
            background: #EEF2FF;
            color: #070589;
        }
        .amount-value {
            font-weight: bold;
        }

        /* ── Bullet lists ── */
        .policy-list {
            margin: 6px 0 6px 18px;
            font-size: 9pt;
            line-height: 1.6;
            color: #222;
        }
        .policy-list li {
            margin-bottom: 3px;
        }

        /* ── Checkbox grid ── */
        .checkbox-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px 20px;
            margin: 6px 0;
            font-size: 9pt;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .checkbox-box {
            width: 10px;
            height: 10px;
            border: 1pt solid #555;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
        }
        .checkbox-box.checked {
            background: #070589;
            border-color: #070589;
            color: #fff;
        }

        /* ── Inspection table ── */
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

        /* ── Items received table ── */
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

        /* ── Signature block ── */
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

        /* ── Footer ── */
        .doc-footer {
            text-align: center;
            font-size: 7.5pt;
            color: #666;
            border-top: 0.5pt solid #ccc;
            padding-top: 5px;
            margin-top: 10px;
        }

        /* ── Penalty highlight ── */
        .penalty-note {
            font-size: 8.5pt;
            background: #fff8f0;
            border-left: 2.5pt solid #e67e22;
            padding: 5px 8px;
            margin: 6px 0;
            color: #333;
        }

        /* ── RA note ── */
        .law-note {
            font-size: 8.5pt;
            background: #f0f4ff;
            border-left: 2.5pt solid #2360E8;
            padding: 5px 8px;
            margin: 5px 0;
            color: #333;
        }

        /* ── Print-only styles ── */
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

{{-- ═══════════════════════════════════════ PAGE 1 ═══════════════════════════════════════ --}}
<div class="page">

    {{-- Header banner --}}
    <div class="doc-header-banner">
        <div class="banner-left">
            <div class="doc-title-main">Dormitory Rental Agreement</div>
            <div class="republic">Republic of the Philippines</div>
        </div>
        <div class="banner-right">Move-In Contract</div>
    </div>
    <div class="confidential-banner">This document is confidential and intended solely for the parties named herein.</div>

    <p class="intro">
        This Move-In Contract ("Agreement") is entered into by and between the <strong>LESSOR</strong> (the dormitory owner or authorized operator) and the <strong>LESSEE</strong> (the tenant), under the terms and conditions set forth below, in compliance with <em>Republic Act No. 9653 (Rent Control Act of 2009)</em> and other applicable laws of the Republic of the Philippines.
    </p>

    {{-- SECTION 1 --}}
    <div class="section-heading">Section 1 — Parties to the Agreement</div>

    <div class="sub-heading">LESSOR (Dormitory Owner / Operator)</div>

    <div class="field-row">
        <span class="field-label">Business / Trade Name:</span>
        <span class="field-value">{{ $lessor['trade_name'] ?? '' }}</span>
    </div>
    <div class="field-row">
        <span class="field-label">Registered Company Name:</span>
        <span class="field-value">{{ $lessor['company_name'] ?? '' }}</span>
    </div>
    <div class="field-row">
        <span class="field-label">Business Address:</span>
        <span class="field-value">{{ $lessor['address'] ?? '' }}</span>
    </div>
    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Contact Number:</span>
            <span class="field-value">{{ $lessor['contact_number'] ?? '' }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Email Address:</span>
            <span class="field-value">{{ $lessor['email'] ?? '' }}</span>
        </div>
    </div>
    <div class="field-row">
        <span class="field-label">Authorized Representative:</span>
        <span class="field-value">{{ $lessor['representative'] ?? '' }}</span>
    </div>

    <div class="sub-heading">LESSEE (Tenant)</div>

    <div class="field-row">
        <span class="field-label">Full Legal Name:</span>
        <span class="field-value">{{ $tenant['personal_info']['first_name'] }} {{ $tenant['personal_info']['last_name'] }}</span>
    </div>
    <div class="field-row">
        <span class="field-label">Permanent Home Address:</span>
        <span class="field-value">{{ $tenant['personal_info']['address'] }}</span>
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
    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Valid Government ID Type:</span>
            <span class="field-value">{{ $tenant['personal_info']['gov_id_type'] ?? '' }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">ID Number:</span>
            <span class="field-value">{{ $tenant['personal_info']['gov_id_number'] ?? '' }}</span>
        </div>
    </div>
    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Company / School:</span>
            <span class="field-value">{{ $tenant['personal_info']['school_company'] ?? '' }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Position / Course:</span>
            <span class="field-value">{{ $tenant['personal_info']['position_course'] ?? '' }}</span>
        </div>
    </div>

    <div class="sub-heading">Emergency Contact Person</div>

    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Full Name:</span>
            <span class="field-value">{{ $tenant['emergency_contact']['name'] ?? '' }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Relationship to Tenant:</span>
            <span class="field-value">{{ $tenant['emergency_contact']['relationship'] ?? '' }}</span>
        </div>
    </div>
    <div class="field-row">
        <span class="field-label">Contact Number:</span>
        <span class="field-value">{{ $tenant['emergency_contact']['contact_number'] ?? '' }}</span>
    </div>

    {{-- SECTION 2 --}}
    <div class="section-heading">Section 2 — Property Details</div>

    <div class="field-row">
        <span class="field-label">Building / Property Name:</span>
        <span class="field-value">{{ $tenant['personal_info']['property'] }}</span>
    </div>
    <div class="field-row">
        <span class="field-label">Complete Address:</span>
        <span class="field-value">{{ $property['address'] ?? '' }}</span>
    </div>
    <div class="field-row two-col">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Unit / Room Number:</span>
            <span class="field-value">{{ $tenant['personal_info']['unit'] }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Floor:</span>
            <span class="field-value">{{ $property['floor'] ?? '' }}</span>
        </div>
    </div>

    <hr class="divider-thin" style="margin-top:auto;">
    <div class="doc-footer">DORMITORY RENTAL AGREEMENT &nbsp;|&nbsp; Republic of the Philippines &nbsp;|&nbsp; MOVE-IN CONTRACT</div>
</div>


{{-- ═══════════════════════════════════════ PAGE 2 ═══════════════════════════════════════ --}}
<div class="page">

    <div class="doc-header-banner">
        <div class="banner-left">
            <div class="doc-title-main">Dormitory Rental Agreement</div>
            <div class="republic">Republic of the Philippines | Move-In Contract</div>
        </div>
        <div class="banner-right">Page 2</div>
    </div>
    <div class="confidential-banner">This document is confidential and intended solely for the parties named herein.</div>

    <div class="field-row two-col" style="margin-top:10px;">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Bed Assignment (if applicable):</span>
            <span class="field-value">{{ $tenant['rent_details']['bed_number'] }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Room Type:</span>
            <span class="field-value">{{ $tenant['rent_details']['dorm_type'] }}</span>
        </div>
    </div>
    <div class="field-row">
        <span class="field-label">Gender Policy (Male-Only / Female-Only / Co-ed):</span>
        <span class="field-value">{{ $property['gender_policy'] ?? '' }}</span>
    </div>

    {{-- SECTION 3 --}}
    <div class="section-heading">Section 3 — Lease Term</div>

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
        <span class="field-label">Minimum Stay Period:</span>
        <span class="field-value">{{ $tenant['rent_details']['lease_term'] }} month(s)</span>
    </div>

    <p style="font-size:8.5pt; color:#333; line-height:1.55; margin:6px 0 0;">
        The lease shall automatically expire on the end date stated above unless renewed in writing by both parties. The Lessee must provide at least <strong>thirty (30) calendar days'</strong> written notice prior to the intended move-out date. Failure to provide timely notice may result in additional charges equivalent to one (1) month's rent or as specified under Section 7 of this Agreement.
    </p>

    {{-- SECTION 4 --}}
    <div class="section-heading">Section 4 — Rent and Payment Terms</div>

    @php
        $monthlyRate   = $tenant['move_in_details']['monthly_rate'];
        $secDeposit    = $tenant['move_in_details']['security_deposit'];
        $advance       = $monthlyRate;   // 1 month advance
        $reservationFee = $tenant['move_in_details']['reservation_fee'] ?? 0;
        $shortTermPremium = $tenant['move_in_details']['short_term_premium'] ?? 0;
        $total = $monthlyRate + $shortTermPremium + $advance + $secDeposit - $reservationFee;
    @endphp

    <table class="payment-table">
        <thead>
        <tr>
            <th>Item Description</th>
            <th>Details</th>
            <th class="right">Amount (PHP)</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><strong>Monthly Rent</strong></td>
            <td class="desc">Base monthly rate for the assigned bed / room</td>
            <td class="right amount-value">&#8369; {{ number_format($monthlyRate, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Short-Term Premium</strong> <span style="font-size:7.5pt;color:#888;">(if applicable)</span></td>
            <td class="desc">Additional charge for contracts below the standard minimum term</td>
            <td class="right amount-value">&#8369; {{ number_format($shortTermPremium, 2) }}</td>
        </tr>
        <tr>
            <td><strong>1 Month Advance</strong></td>
            <td class="desc">Covers the first month of occupancy</td>
            <td class="right amount-value">&#8369; {{ number_format($advance, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Security Deposit</strong> <span style="font-size:7.5pt;color:#888;">(max 2 months per RA 9653)</span></td>
            <td class="desc">Refundable upon move-out, subject to inspection and clearance</td>
            <td class="right amount-value">&#8369; {{ number_format($secDeposit, 2) }}</td>
        </tr>
        @if($reservationFee > 0)
            <tr>
                <td><strong>Less: Reservation Fee Paid</strong></td>
                <td class="desc">Deducted from advance payment (if any)</td>
                <td class="right amount-value" style="color:#c0392b;">( &#8369; {{ number_format($reservationFee, 2) }} )</td>
            </tr>
        @endif
        <tr class="total-row">
            <td colspan="2"><strong>TOTAL MOVE-IN COST</strong></td>
            <td class="right"><strong>&#8369; {{ number_format($total, 2) }}</strong></td>
        </tr>
        </tbody>
    </table>

    <div class="field-row two-col" style="margin-top:8px;">
        <div class="field-row" style="flex:1;">
            <span class="field-label">Monthly Due Date:</span>
            <span class="field-value">{{ $payment['due_date'] ?? '' }}</span>
        </div>
        <div class="field-row" style="flex:1;">
            <span class="field-label">Accepted Payment Methods:</span>
            <span class="field-value">{{ $payment['methods'] ?? '' }}</span>
        </div>
    </div>

    <div class="penalty-note">
        <strong>Late Payment Penalty:</strong> <strong>{{ $payment['late_fee'] ?? '1' }}%</strong> of the monthly rent per day of delay after the due date.
    </div>

    <div class="law-note">
        Under RA 9653, the Lessor cannot demand more than one (1) month advance rent and two (2) months' security deposit. The security deposit shall be placed in a bank account under the Lessor's name. Interest earned shall be returned to the Lessee upon lease expiration. The security deposit shall <strong>NOT</strong> be applied as monthly rent during the lease term — it is refundable only upon move-out after inspection and clearance. Utility charges not included in the base rent shall be billed separately, split equally among tenants, and prorated for mid-month move-ins.
    </div>

    {{-- SECTION 5 --}}
    <div class="section-heading">Section 5 — Rent Inclusions and Exclusions</div>

    <p style="font-size:9pt; margin-bottom:5px; color:#222;">The following items are <strong>included</strong> in the monthly rent (check all that apply):</p>

    @php
        $inclusions = $property['inclusions'] ?? [];
        $allInclusions = [
            'association_dues'   => 'Association dues / condo or building fees',
            'wifi'               => 'Wi-Fi / Internet access',
            'amenities'          => 'Access to building amenities (pool, gym, function areas, etc.)',
            'housekeeping'       => 'Housekeeping / common-area cleaning',
            'appliances'         => 'Use of shared appliances',
            'security'           => '24/7 building security',
            'furnished'          => 'Furnished room (bed, cabinet, air conditioning, etc.)',
            'water'              => 'Water utility',
        ];
    @endphp

    <div class="checkbox-grid">
        @foreach($allInclusions as $key => $label)
            <div class="checkbox-item">
                <div class="checkbox-box {{ in_array($key, $inclusions) ? 'checked' : '' }}">{{ in_array($key, $inclusions) ? '✓' : '' }}</div>
                <span>{{ $label }}</span>
            </div>
        @endforeach
        <div class="checkbox-item">
            <div class="checkbox-box"></div>
            <span>Other: <span style="border-bottom: 0.5pt solid #999; display:inline-block; min-width:80px;">{{ $property['inclusion_other'] ?? '' }}</span></span>
        </div>
    </div>

    <hr class="divider-thin">
    <div class="doc-footer">DORMITORY RENTAL AGREEMENT &nbsp;|&nbsp; Republic of the Philippines &nbsp;|&nbsp; MOVE-IN CONTRACT</div>
</div>


{{-- ═══════════════════════════════════════ PAGE 3 ═══════════════════════════════════════ --}}
<div class="page">

    <div class="doc-header-banner">
        <div class="banner-left">
            <div class="doc-title-main">Dormitory Rental Agreement</div>
            <div class="republic">Republic of the Philippines | Move-In Contract</div>
        </div>
        <div class="banner-right">Page 3</div>
    </div>
    <div class="confidential-banner">This document is confidential and intended solely for the parties named herein.</div>

    <p style="font-size:9pt; margin: 10px 0 5px; color:#222;">The following items are <strong>NOT included</strong> and will be billed separately:</p>

    @php
        $exclusions = $property['exclusions'] ?? [];
        $allExclusions = [
            'electricity' => 'Electricity (split equally among unit tenants)',
            'water_excl'  => 'Water (if not included above)',
            'laundry'     => 'Laundry services',
            'parking'     => 'Parking fees',
        ];
    @endphp

    <div class="checkbox-grid">
        @foreach($allExclusions as $key => $label)
            <div class="checkbox-item">
                <div class="checkbox-box {{ in_array($key, $exclusions) ? 'checked' : '' }}">{{ in_array($key, $exclusions) ? '✓' : '' }}</div>
                <span>{{ $label }}</span>
            </div>
        @endforeach
        <div class="checkbox-item">
            <div class="checkbox-box"></div>
            <span>Other: <span style="border-bottom: 0.5pt solid #999; display:inline-block; min-width:80px;">{{ $property['exclusion_other'] ?? '' }}</span></span>
        </div>
    </div>

    {{-- SECTION 6 --}}
    <div class="section-heading">Section 6 — House Rules and Policies</div>

    <p style="font-size:9pt; margin-bottom:4px; color:#222;">The Lessee agrees to abide by the following rules at all times:</p>
    <ul class="policy-list">
        <li>No overnight visitors or unauthorized guests. Visitors must leave by the designated curfew time.</li>
        <li>No smoking inside the unit or building common areas.</li>
        <li>No illegal drugs, substances, or activities of any kind.</li>
        <li>No pets allowed within the premises unless explicitly permitted in writing.</li>
        <li>Observe quiet hours from <strong>{{ $property['quiet_hours_start'] ?? '______' }} PM</strong> to <strong>{{ $property['quiet_hours_end'] ?? '______' }} AM</strong>.</li>
        <li>No unauthorized room transfers, subletting, or sharing of assigned bed with another person.</li>
        <li>No tampering with air conditioning units, electrical systems, or building infrastructure.</li>
        <li>Report all maintenance issues to the dormitory administration promptly.</li>
        <li>Keep personal area and all shared spaces clean and orderly.</li>
        <li>Follow proper garbage disposal and recycling procedures.</li>
        <li>Respect fellow tenants' privacy, belongings, and personal space.</li>
        <li>Comply with all building management rules and regulations.</li>
    </ul>

    <div class="penalty-note">
        <strong>Violation Penalties:</strong> First offense — written warning. Second offense — fine of PHP <strong>{{ $property['violation_fine'] ?? '______' }}</strong>. Third offense — grounds for lease termination with possible deposit forfeiture. Serious violations (illegal activity, property destruction) may result in immediate termination.
    </div>

    {{-- SECTION 7 --}}
    <div class="section-heading">Section 7 — Early Termination</div>

    <p style="font-size:9pt; margin-bottom:4px; color:#222;">If the Lessee vacates before the end of the minimum lease term, the following shall apply:</p>
    <ul class="policy-list">
        <li>The security deposit may be partially or fully forfeited as liquidated damages.</li>
        <li>Any outstanding utility balances and charges must be settled in full before vacating.</li>
        <li>The Lessee must still provide a minimum of thirty (30) calendar days' written notice.</li>
        <li>An additional early termination fee of PHP <strong>{{ $property['early_termination_fee'] ?? '______' }}</strong> may be charged, as mutually agreed upon signing.</li>
    </ul>

    {{-- SECTION 8 --}}
    <div class="section-heading">Section 8 — Move-In Room Condition Checklist</div>

    <p style="font-size:8.5pt; color:#333; margin-bottom:6px; line-height:1.5;">
        Both parties shall inspect the room on the move-in date and record the condition of each item below. This checklist serves as the baseline for the move-out inspection. Photographs of the room condition at move-in shall be taken and stored as supporting evidence.
    </p>

    @php
        $checklistItems = [
            'Bed Frame & Mattress / Foam',
            'Cabinet / Wardrobe (doors & locks)',
            'Air Conditioning Unit & Remote',
            'Bathroom Fixtures (shower, toilet, faucet, heater)',
            'Electrical Outlets & Light Switches',
            'Windows, Curtains / Blinds',
            'Walls (stains, cracks, holes)',
            'Floor Condition',
            'Door Lock & Keys',
            'Other: ___________________________',
        ];
        $roomCondition = $tenant['room_condition'] ?? [];
    @endphp

    <table class="inspection-table">
        <thead>
        <tr>
            <th style="width:38%;">Item</th>
            <th style="width:12%;">Good</th>
            <th style="width:12%;">Damaged</th>
            <th style="width:12%;">Missing</th>
            <th>Remarks</th>
        </tr>
        </thead>
        <tbody>
        @foreach($checklistItems as $item)
            @php $cond = $roomCondition[$item] ?? null; @endphp
            <tr>
                <td>{{ $item }}</td>
                <td class="check-cell">{{ $cond === 'Good' ? '✓' : '' }}</td>
                <td class="check-cell">{{ $cond === 'Damaged' ? '✓' : '' }}</td>
                <td class="check-cell">{{ $cond === 'Missing' ? '✓' : '' }}</td>
                <td style="font-size:7.5pt; color:#555;">{{ $roomCondition[$item . '_remarks'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr class="divider-thin">
    <div class="doc-footer">DORMITORY RENTAL AGREEMENT &nbsp;|&nbsp; Republic of the Philippines &nbsp;|&nbsp; MOVE-IN CONTRACT</div>
</div>


{{-- ═══════════════════════════════════════ PAGE 4 ═══════════════════════════════════════ --}}
<div class="page">

    <div class="doc-header-banner">
        <div class="banner-left">
            <div class="doc-title-main">Dormitory Rental Agreement</div>
            <div class="republic">Republic of the Philippines | Move-In Contract</div>
        </div>
        <div class="banner-right">Page 4</div>
    </div>
    <div class="confidential-banner">This document is confidential and intended solely for the parties named herein.</div>

    {{-- SECTION 9 --}}
    <div class="section-heading" style="margin-top:10px;">Section 9 — Items Received by Tenant</div>

    @php
        $itemsReceived = [
            ['label' => 'Unit Key(s)',                   'key' => 'unit_keys'],
            ['label' => 'Building Access Card / Fob',    'key' => 'access_card'],
            ['label' => 'Wi-Fi Password / Credentials',  'key' => 'wifi_creds'],
            ['label' => 'Air Conditioning Remote',        'key' => 'ac_remote'],
            ['label' => 'Cabinet Key',                   'key' => 'cabinet_key'],
            ['label' => 'Other',                         'key' => 'other'],
        ];
        $received = $tenant['items_received'] ?? [];
    @endphp

    <table class="items-table">
        <thead>
        <tr>
            <th style="width:40%;">Item</th>
            <th style="width:10%;">Qty</th>
            <th style="width:20%;">Condition</th>
            <th>Tenant Initials</th>
        </tr>
        </thead>
        <tbody>
        @foreach($itemsReceived as $item)
            @php $r = $received[$item['key']] ?? []; @endphp
            <tr>
                <td>{{ $item['label'] }}</td>
                <td>{{ $r['qty'] ?? '' }}</td>
                <td>{{ $r['condition'] ?? '' }}</td>
                <td></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- SECTION 10 --}}
    <div class="section-heading">Section 10 — Governing Law and Dispute Resolution</div>

    <p style="font-size:9pt; color:#222; line-height:1.6; text-align:justify;">
        This Agreement shall be governed by and construed in accordance with the laws of the Republic of the Philippines, including but not limited to <em>Republic Act No. 9653 (Rent Control Act of 2009)</em> and its implementing rules. Any dispute arising from this Agreement shall first be settled through amicable negotiation. Should negotiation fail, the parties agree to seek mediation through the Barangay where the property is located, and thereafter through the proper courts of competent jurisdiction.
    </p>

    {{-- SECTION 11 --}}
    <div class="section-heading">Section 11 — Agreement and Signatures</div>

    <p style="font-size:9pt; color:#222; line-height:1.55; margin-bottom:8px; text-align:justify;">
        By signing below, both parties acknowledge that they have read, understood, and voluntarily agree to all terms and conditions stated in this Move-In Contract. Both parties confirm that all information provided herein is true, correct, and complete.
    </p>

    <div class="signature-section">
        @if(!empty($tenantSignatureBase64) && !empty($ownerSignatureBase64))
            {{-- E-Signature verification banner --}}
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
                    <strong>{{ $lessor['representative'] ?? '' }}</strong><br>
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
    <div class="doc-footer">DORMITORY RENTAL AGREEMENT &nbsp;|&nbsp; Republic of the Philippines &nbsp;|&nbsp; MOVE-IN CONTRACT</div>
</div>

</body>
</html>
