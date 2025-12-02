<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
            line-height: 1.4;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .company-info {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        .company-address {
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .company-contact {
            font-size: 11px;
            color: #333;
            margin-top: 8px;
        }
        .logo-section {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            text-align: right;
        }
        .logo-container {
            margin-bottom: 0;
            text-align: right;
        }
        .invoice-title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            margin: 30px 0;
            color: #1a1a1a;
        }
        .invoice-details-section {
            margin-bottom: 30px;
            position: relative;
        }
        .buyer-info {
            width: 45%;
            float: left;
        }
        .invoice-info {
            width: 45%;
            float: right;
            text-align: right;
        }
        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 8px;
            color: #1a1a1a;
        }
        .invoice-info .section-title {
            text-align: right;
        }
        .info-row {
            margin-bottom: 4px;
            font-size: 11px;
        }
        .invoice-info .info-row {
            text-align: right;
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
            text-align: left;
            margin-right: 2px;
        }
        .invoice-info .info-label {
            text-align: left;
        }
        .invoice-info .info-row span:last-child {
            display: inline-block;
            min-width: 120px;
            text-align: right;
            background-color: #f5f5f5;
            padding: 2px 6px;
            border-radius: 2px;
            margin-left: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f5f5f5;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
            clear: both;
            text-align: right;
        }
        .summary-box {
            width: 280px;
            margin-left: auto;
            margin-right: 0;
        }
        .summary-row {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
            font-size: 11px;
            text-align: right;
        }
        .summary-row span:first-child {
            display: inline-block;
            min-width: 120px;
            text-align: left;
            margin-right: 10px;
        }
        .summary-row span:last-child {
            display: inline-block;
            min-width: 80px;
            text-align: right;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 10px 0;
            margin-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 0 20px;
        }
        .buyer-address {
            white-space: pre-line;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <div class="company-name">Radiance Eco Ltd</div>
            <div class="company-address">
                Unit 9, The Farthing Enterprise Centre<br>
                39 Farthing Grove, Netherfield<br>
                Milton Keynes, MK6 4JH
            </div>
            <div class="company-contact">
                Tel: 02034883601<br>
                Email: info@radianceeco.co.uk
            </div>
        </div>
        <div class="logo-section">
            <div class="logo-container">
                <img src="{{ public_path('images/logo.svg') }}" alt="Logo" style="height: 60px; width: auto;">
            </div>
        </div>
    </div>

    <div class="invoice-title">Invoice</div>

    <div class="invoice-details-section">
        <div class="buyer-info">
            <div class="info-row" style="font-weight: bold; margin-bottom: 6px;">{{ $invoice->buyer_name }}</div>
            <div class="buyer-address">{{ $invoice->buyer_address }}</div>
        </div>
        <div class="invoice-info">
            <div class="section-title">Invoice Details:</div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span>{{ $invoice->invoice_date->format('d F Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Invoice No.:</span>
                <span>{{ $invoice->invoice_no }}</span>
            </div>
            @if($invoice->order_no)
            <div class="info-row">
                <span class="info-label">Order No.:</span>
                <span>{{ $invoice->order_no }}</span>
            </div>
            @endif
            @if($invoice->submission_no)
            <div class="info-row">
                <span class="info-label">Submission No.:</span>
                <span>{{ $invoice->submission_no }}</span>
            </div>
            @endif
            @if($invoice->po_no)
            <div class="info-row">
                <span class="info-label">PO No.:</span>
                <span>{{ $invoice->po_no }}</span>
            </div>
            @endif
        </div>
    </div>

    <div style="clear: both;"></div>
    
    <table>
        <thead>
            <tr>
                <th>Details</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->line_items as $item)
            <tr>
                <td>{{ $item['details'] }}</td>
                <td class="text-right">{{ number_format($item['qty'], 3) }}</td>
                <td class="text-right">£{{ number_format($item['price'], 2) }}</td>
                <td class="text-right">£{{ number_format($item['qty'] * $item['price'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-box">
            <div class="summary-row">
                <span>Amount:</span>
                <span>£{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>VAT @ 20%:</span>
                <span>£{{ number_format($invoice->vat_amount, 2) }}</span>
            </div>
            <div class="summary-row total">
                <span>Invoice Total:</span>
                <span>£{{ number_format($invoice->total, 2) }}</span>
            </div>
            @if($invoice->due_date)
            <div class="summary-row" style="margin-top: 10px; font-size: 10px; color: #666;">
                <span>Due Date:</span>
                <span>{{ $invoice->due_date->format('d F Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>
            Lloyds Bank, Radiance Eco Ltd: 30-54-66, Account No. 19091268<br>
            Company reg Number: 15258647<br>
            VAT Registration Number: GB 479 2526 53
        </p>
    </div>
</body>
</html>
