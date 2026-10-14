<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice Receipt - {{ $invoice->invoice_number }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 20px 0;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            line-height: 24px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .invoice-box {
                box-shadow: none;
                border: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4 fw-bold me-2">
            <i class="bi bi-printer"></i> Print Statement / Receipt
        </button>
        <button onclick="window.close()" class="btn btn-light border px-4">
            Close Window
        </button>
    </div>

    <div class="invoice-box">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1">{{ $hospitalName }}</h3>
                <div class="text-muted small">{{ $hospitalAddress }}</div>
                <div class="text-muted small">Tel: {{ $hospitalPhone }} &bull; Email: {{ $hospitalEmail }}</div>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-dark">{{ $invoice->invoice_number }}</h4>
                <div class="text-muted small">Date: {{ $invoice->invoice_date->format('M d, Y') }}</div>
                <div class="badge bg-{{ $invoice->payment_status === 'paid' ? 'success' : 'warning text-dark' }} text-uppercase">
                    {{ $invoice->payment_status }}
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <strong class="text-muted text-uppercase small">Patient Details:</strong>
                <h5 class="fw-bold mb-0">{{ $invoice->patient->full_name }}</h5>
                <div class="small">MRN: <strong>{{ $invoice->patient->mrn }}</strong></div>
                <div class="small">Phone: {{ $invoice->patient->phone }}</div>
                <div class="small">Address: {{ $invoice->patient->address ?? 'Not recorded' }}</div>
            </div>
            <div class="col-6 text-end">
                <strong class="text-muted text-uppercase small">Payment Details:</strong>
                <div class="small">Cashier: <strong>{{ $invoice->generatedBy->name ?? 'Admin Staff' }}</strong></div>
                <div class="small">Tender Method: <strong>{{ ucfirst($invoice->payment_method ?? 'Pending') }}</strong></div>
                @if($invoice->patient->insurance_provider)
                    <div class="small text-primary">Insurance: {{ $invoice->patient->insurance_provider }} ({{ $invoice->patient->insurance_policy_number }})</div>
                @endif
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Service / Item Description</th>
                    <th>Category</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $item->item_type) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">{{ $currencySymbol }}{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @if($invoice->discount_amount > 0)
                    <tr>
                        <th colspan="5" class="text-end">Discount Applied:</th>
                        <th class="text-end">- {{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</th>
                    </tr>
                @endif
                @if($invoice->tax_amount > 0)
                    <tr>
                        <th colspan="5" class="text-end">Hospital Tax:</th>
                        <th class="text-end">+ {{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</th>
                    </tr>
                @endif
                <tr>
                    <th colspan="5" class="text-end fs-6">Gross Total:</th>
                    <th class="text-end fs-6">{{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end text-success">Total Amount Paid:</th>
                    <th class="text-end text-success">{{ $currencySymbol }}{{ number_format($invoice->paid_amount, 2) }}</th>
                </tr>
                <tr class="{{ $invoice->balance_due > 0 ? 'table-danger' : 'table-light' }}">
                    <th colspan="5" class="text-end fs-5">Remaining Balance:</th>
                    <th class="text-end fs-5">{{ $currencySymbol }}{{ number_format($invoice->balance_due, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        @if($invoice->notes)
            <div class="p-3 bg-light rounded border mb-4 small">
                <strong>Notes / Memo:</strong><br>
                {!! nl2br(e($invoice->notes)) !!}
            </div>
        @endif

        <div class="row pt-5 mt-4 text-center">
            <div class="col-6">
                <div class="border-top pt-2 small text-muted">Patient / Guardian Signature</div>
            </div>
            <div class="col-6">
                <div class="border-top pt-2 small text-muted">Authorized Hospital Cashier Stamp & Signature</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-5 pt-3 border-top">
            Thank you for choosing our hospital. This is a computer-generated official billing document.
        </div>
    </div>
</body>
</html>
