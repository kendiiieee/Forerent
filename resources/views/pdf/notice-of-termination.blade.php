<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notice of Termination — {{ $tenant['personal_info']['first_name'] }} {{ $tenant['personal_info']['last_name'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            padding: 14mm;
        }
        .page {
            background: #fff;
            position: relative;
        }
        .confidential-banner {
            text-align: center;
            font-size: 7.5pt;
            color: #555;
            border-top: 0.5pt solid #999;
            border-bottom: 0.5pt solid #999;
            padding: 2px 0;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }
        .doc-header-banner {
            background: #1a2744;
            color: #fff;
            padding: 12px 16px;
            margin-bottom: 4px;
            border-bottom: 3px solid #B91C1C;
        }
        .doc-header-banner table {
            width: 100%;
            border-collapse: collapse;
        }
        .doc-header-banner td { vertical-align: middle; }
        .doc-header-banner .banner-left { width: 65%; }
        .doc-header-banner .banner-right {
            width: 35%;
            text-align: right;
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #fff;
            line-height: 1.4;
        }
        .doc-title-main {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #fff;
        }
        .republic {
            font-size: 8.5pt;
            letter-spacing: 0.3px;
            color: #ccc;
            margin-top: 2px;
        }
        .intro {
            font-size: 9pt;
            text-align: justify;
            line-height: 1.45;
            margin: 8px 0;
            color: #222;
        }
        .section-heading {
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #070589;
            background: #EEF2FF;
            padding: 3px 8px;
            margin: 9px 0 5px;
            border-left: 3px solid #2360E8;
        }
        table.parties-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin: 4px 0 6px;
        }
        table.parties-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        table.parties-table td.label {
            color: #555;
            white-space: nowrap;
            width: 16%;
            font-weight: normal;
        }
        table.parties-table td.value {
            font-weight: bold;
            color: #000;
            width: 34%;
            border-bottom: 0.5pt solid #999;
        }
        .vacate-callout {
            background: #FEE2E2;
            border: 1pt solid #B91C1C;
            border-left: 4pt solid #B91C1C;
            padding: 6px 12px;
            margin: 6px 0;
            text-align: center;
        }
        .vacate-callout .label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #B91C1C;
            font-weight: bold;
        }
        .vacate-callout .date {
            font-size: 14pt;
            font-weight: bold;
            color: #7F1D1D;
            margin-top: 1px;
            letter-spacing: 0.5px;
        }
        .vacate-callout .meta {
            font-size: 8pt;
            color: #7F1D1D;
            margin-top: 0;
        }
        .grounds-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin: 4px 0;
            table-layout: fixed;
        }
        .grounds-table th {
            background: #070589;
            color: #fff;
            padding: 4px 6px;
            text-align: left;
            font-size: 8pt;
        }
        .grounds-table td {
            padding: 5px 6px;
            border: 0.5pt solid #ccc;
            vertical-align: top;
            color: #111;
            font-size: 8.5pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .grounds-table tr:nth-child(even) td {
            background: #f9f9ff;
        }
        .legal-block, .rights-block {
            font-size: 8pt;
            line-height: 1.4;
            padding: 6px 8px;
            margin: 6px 0;
        }
        .legal-block {
            color: #444;
            background: #FFFBEB;
            border: 0.5pt solid #FCD34D;
        }
        .rights-block {
            color: #1F2937;
            background: #ECFDF5;
            border: 0.5pt solid #10B981;
        }
        .rights-block .title {
            font-weight: bold;
            color: #065F46;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 8.5pt;
            margin-bottom: 4px;
        }
        ul.tight {
            margin: 4px 0 0 16px;
            font-size: 8.5pt;
            line-height: 1.5;
        }
        ul.tight li { margin-bottom: 2px; }

        p.steps-intro {
            font-size: 9pt;
            line-height: 1.5;
            margin-bottom: 4px;
        }
        ul.tight.steps li {
            font-size: 8.75pt;
        }

        .signature-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            padding: 0 12px;
            vertical-align: top;
        }
        .signature-line {
            border-bottom: 0.75pt solid #000;
            height: 24px;
        }
        .signature-name {
            font-weight: bold;
            font-size: 9.5pt;
            text-align: center;
            margin-top: 3px;
        }
        .signature-role {
            font-size: 8.5pt;
            color: #555;
            text-align: center;
        }
        .footer {
            margin-top: 8px;
            border-top: 0.5pt solid #999;
            padding-top: 4px;
            font-size: 7pt;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="confidential-banner">CONFIDENTIAL — FOR THE NAMED LESSEE AND PROPERTY MANAGEMENT ONLY</div>

    <div class="doc-header-banner">
        <table>
            <tr>
                <td class="banner-left">
                    <div class="doc-title-main">Notice of Lease Termination</div>
                    <div class="republic">Republic of the Philippines</div>
                </td>
                <td class="banner-right">
                    Issued: {{ $issuedAt->format('M d, Y') }}<br>
                    Ref: {{ $referenceNumber }}
                </td>
            </tr>
        </table>
    </div>

    <p class="intro">
        This Notice is issued by the undersigned Lessor (or duly authorized representative) to the
        Lessee named below, in accordance with the executed Lease Agreement and applicable provisions
        of Philippine law. The Lessee is hereby formally notified of the termination of the lease for
        the cause(s) described herein.
    </p>

    {{-- ═══════════════════════════════════════════════
         PARTIES & PREMISES
         ═══════════════════════════════════════════════ --}}
    <div class="section-heading">Section 1 — Parties &amp; Premises</div>

    <table class="parties-table">
        <tr>
            <td class="label">Lessee:</td>
            <td class="value">{{ $tenant['personal_info']['first_name'] }} {{ $tenant['personal_info']['last_name'] }}</td>
            <td class="label">Contact:</td>
            <td class="value">{{ $tenant['contact_info']['contact_number'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Email:</td>
            <td class="value">{{ $tenant['contact_info']['email'] ?? '—' }}</td>
            <td class="label">Lease ID:</td>
            <td class="value">{{ $lease->lease_id }}</td>
        </tr>
        <tr>
            <td class="label">Property:</td>
            <td class="value">{{ $propertyName }}</td>
            <td class="label">Unit / Bed:</td>
            <td class="value">{{ $unitNumber }}{{ $bedNumber ? ' / ' . $bedNumber : '' }}</td>
        </tr>
        <tr>
            <td class="label">Lease Start:</td>
            <td class="value">{{ $lease->start_date?->format('M d, Y') ?? '—' }}</td>
            <td class="label">Original End:</td>
            <td class="value">{{ $lease->end_date?->format('M d, Y') ?? '—' }}</td>
        </tr>
    </table>

    {{-- ═══════════════════════════════════════════════
         GROUNDS
         ═══════════════════════════════════════════════ --}}
    <div class="section-heading">Section 2 — Grounds for Termination</div>

    <p class="steps-intro">
        Termination is hereby effected on the basis of the following documented violation(s) of the
        Lease Agreement, in accordance with the agreed escalation schedule (1st offense — written
        warning; 2nd offense — fine; 3rd offense — grounds for termination):
    </p>

    <table class="grounds-table">
        <colgroup>
            <col style="width: 14%">
            <col style="width: 13%">
            <col style="width: 18%">
            <col style="width: 9%">
            <col style="width: 46%">
        </colgroup>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Date</th>
                <th>Category</th>
                <th>Offense</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groundsViolations as $vio)
                <tr>
                    <td><strong>{{ $vio->violation_number }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($vio->violation_date)->format('M d, Y') }}</td>
                    <td>{{ $vio->category }}</td>
                    <td>{{ $vio->offense_number }}{{ ['1'=>'st','2'=>'nd','3'=>'rd'][(string)$vio->offense_number] ?? 'th' }}</td>
                    <td>{{ $vio->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════════════════════════════════════════════
         NOTICE PERIOD / VACATE-BY DATE
         ═══════════════════════════════════════════════ --}}
    <div class="section-heading">Section 3 — Notice Period &amp; Vacate-By Date</div>

    <p class="steps-intro">
        Pursuant to this Notice and the {{ $noticePeriodDays }}-day notice period agreed in the
        Lease Agreement, the Lessee is required to vacate the leased premises on or before:
    </p>

    <div class="vacate-callout">
        <div class="label">Vacate Premises On or Before</div>
        <div class="date">{{ $lease->vacate_by_date->format('F d, Y') }}</div>
        <div class="meta">({{ $noticePeriodDays }} days from Notice issuance — {{ $issuedAt->format('M d, Y') }})</div>
    </div>

    {{-- ═══════════════════════════════════════════════
         NEXT STEPS — SETTLEMENT
         ═══════════════════════════════════════════════ --}}
    <div class="section-heading">Section 4 — Settlement, Turnover &amp; Clearance</div>

    <p class="steps-intro">
        Prior to vacating, the Lessee shall coordinate with management to complete the following:
    </p>
    <ul class="tight steps">
        <li><strong>Final billing settlement</strong> — outstanding rent, utilities, fines, and any other charges as of the move-out date.</li>
        <li><strong>Move-out inspection</strong> — joint walkthrough to assess room condition and verify return of furnished items / keys / IDs.</li>
        <li><strong>Security deposit reconciliation</strong> — return of the deposit (less lawful deductions) per the Lease Agreement and RA 9653 IRR Section 7.</li>
        <li><strong>Move-out contract &amp; clearance</strong> — execution of the final settlement document signed by Lessor, Manager, and Lessee.</li>
    </ul>

    {{-- ═══════════════════════════════════════════════
         TENANT RIGHTS — RA 9653
         ═══════════════════════════════════════════════ --}}
    <div class="rights-block">
        <div class="title">Lessee Rights — Reminder</div>
        Notwithstanding this Notice, the Lessor and management will not, and may not lawfully,
        do any of the following without a court order:
        <ul class="tight">
            <li>Lock the Lessee out of the premises or change the locks.</li>
            <li>Cut off water, electricity, internet, or other utilities to compel the Lessee to leave.</li>
            <li>Remove, detain, or dispose of the Lessee's personal belongings.</li>
            <li>Use force, intimidation, or threats of any kind.</li>
        </ul>
        Should a dispute arise, the parties shall first proceed to barangay conciliation
        (Katarungang Pambarangay) before any court action. Only a competent court may issue
        an actual writ of ejectment.
    </div>

    {{-- ═══════════════════════════════════════════════
         LEGAL BASIS
         ═══════════════════════════════════════════════ --}}
    <div class="legal-block">
        <strong>Legal Basis.</strong> This Notice is issued pursuant to (i) the violation and
        termination clauses of the executed Lease Agreement; (ii) the Civil Code of the
        Philippines (Articles 1159, 1306, 1657, 1659, 1673); and (iii) Republic Act No. 9653
        (Rent Control Act of 2009) and its Implementing Rules and Regulations, where applicable.
        This Notice does not waive any other right or remedy available to the Lessor under the
        Lease Agreement or law.
    </div>

    {{-- ═══════════════════════════════════════════════
         SIGNATURES
         ═══════════════════════════════════════════════ --}}
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $managerName }}</div>
                <div class="signature-role">Property Manager (for the Lessor)</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $tenant['personal_info']['first_name'] }} {{ $tenant['personal_info']['last_name'] }}</div>
                <div class="signature-role">Lessee — Acknowledgment of Receipt</div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
