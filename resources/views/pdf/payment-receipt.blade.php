<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payment Receipt - {{ $data['invoice_no'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1a1a1a; font-size: 13px; }

        .header {
            background: linear-gradient(90deg, #1E42B1 0%, #4A83E6 100%);
            color: white;
            padding: 30px 40px;
            position: relative;
        }
        .header h1 { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase; }
        .header-details { position: absolute; top: 30px; right: 40px; text-align: right; font-size: 12px; }
        .header-details table { margin-left: auto; }
        .header-details td { padding: 2px 0; }
        .header-details .label { opacity: 0.85; padding-right: 16px; }
        .header-details .value { font-weight: 700; }

        .content { padding: 30px 40px; }

        .info-grid { width: 100%; margin-bottom: 24px; }
        .info-grid td { vertical-align: top; }
        .info-grid .left-col { width: 48%; padding-right: 2%; }
        .info-grid .right-col { width: 48%; padding-left: 2%; }

        .info-box {
            border: 1px solid #E2E2E2;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 20px;
        }
        .info-box h3 {
            color: #1E3A8A;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #E2E2E2;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row table { width: 100%; }
        .info-label { color: #9CA3AF; font-size: 12px; font-weight: 500; width: 90px; padding: 6px 0; }
        .info-value { color: #01295E; font-size: 13px; font-weight: 700; text-align: right; padding: 6px 0; }

        .financial-table { width: 100%; border-collapse: collapse; border-radius: 10px; overflow: hidden; margin-bottom: 24px; border: 1px solid #E2E2E2; }
        .financial-table thead th {
            background: #2563EB;
            color: white;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 24px;
        }
        .financial-table thead th:first-child { text-align: left; }
        .financial-table thead th:last-child { text-align: right; }
        .financial-table tbody td { padding: 16px 24px; font-size: 13px; }
        .financial-table tbody td:first-child { font-weight: 500; }
        .financial-table tbody td:last-child { text-align: right; font-weight: 700; }

        .total-box {
            background: linear-gradient(90deg, #1D4ED8 0%, #3B82F6 100%);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            color: white;
        }
        .total-box .label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; opacity: 0.9; margin-bottom: 4px; }
        .total-box .amount { font-size: 32px; font-weight: 800; letter-spacing: -0.5px; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Payment Receipt</h1>
        <div class="header-details">
            <table>
                <tr><td class="label">Invoice Number</td><td class="value">{{ $data['invoice_no'] }}</td></tr>
                <tr><td class="label">Issued Date</td><td class="value">{{ $data['issued_date'] }}</td></tr>
                <tr><td class="label">Due Date</td><td class="value">{{ $data['due_date'] }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Content --}}
    <div class="content">
        {{-- Row 1: Tenant Info & Payment Details --}}
        <table class="info-grid">
            <tr>
                <td class="left-col">
                    <div class="info-box">
                        <h3>Tenant Information</h3>
                        <table style="width:100%">
                            <tr><td class="info-label">Name</td><td class="info-value">{{ $data['tenant']['name'] }}</td></tr>
                            <tr><td class="info-label">Unit</td><td class="info-value">{{ $data['tenant']['unit'] }}</td></tr>
                            <tr><td class="info-label">Building</td><td class="info-value">{{ $data['tenant']['building'] }}</td></tr>
                            <tr><td class="info-label">Address</td><td class="info-value">{{ $data['tenant']['address'] }}</td></tr>
                            <tr><td class="info-label">Contact</td><td class="info-value">{{ $data['tenant']['contact'] }}</td></tr>
                        </table>
                    </div>
                </td>
                <td class="right-col">
                    <div class="info-box">
                        <h3>Payment Details</h3>
                        <table style="width:100%">
                            <tr><td class="info-label">Date Paid</td><td class="info-value">{{ $data['payment']['date_paid'] }}</td></tr>
                            <tr><td class="info-label">Transaction ID</td><td class="info-value">{{ $data['payment']['txn_id'] }}</td></tr>
                            <tr><td class="info-label">Lease Type</td><td class="info-value">{{ $data['payment']['lease_type'] }}</td></tr>
                            <tr><td class="info-label">Period</td><td class="info-value">{{ $data['payment']['period'] }}</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Row 2: Recipient Info & Financial Table --}}
        <table class="info-grid">
            <tr>
                <td class="left-col">
                    <div class="info-box">
                        <h3>Recipient Information</h3>
                        <table style="width:100%">
                            <tr><td class="info-label">Name</td><td class="info-value">{{ $data['recipient']['name'] }}</td></tr>
                            <tr><td class="info-label">Position</td><td class="info-value">{{ $data['recipient']['position'] }}</td></tr>
                        </table>
                    </div>
                </td>
                <td class="right-col">
                    <table class="financial-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $data['financials']['description'] }}</td>
                                <td>₱ {{ number_format($data['financials']['amount'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Total --}}
        <div class="total-box">
            <div class="label">Total Amount Paid</div>
            <div class="amount">₱ {{ number_format($data['financials']['amount'], 2) }}</div>
        </div>
    </div>
</body>
</html>
