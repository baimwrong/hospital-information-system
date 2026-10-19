@extends('layouts.app')

@section('title', 'Invoice - ' . $invoice->invoice_number)
@section('page_title', 'Billing Invoice & Payment Processing')

@section('content')
<div class="mb-3 d-flex align-items-center justify-content-between">
    <a href="{{ route('billing.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Invoices
    </a>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('billing.print', $invoice) }}" target="_blank" class="btn btn-sm btn-outline-dark">
            <i class="bi bi-printer me-1"></i> Print Hospital Receipt
        </a>
        @if($invoice->balance_due > 0)
            <button type="button" class="btn btn-sm btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="bi bi-cash-coin me-1"></i> Process Payment
            </button>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4">
            <!-- Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div>
                    <h3 class="fw-bold text-primary mb-1">{{ $hospitalName }}</h3>
                    <small class="text-muted d-block">{{ $hospitalAddress }} &bull; {{ $hospitalPhone }}</small>
                </div>
                <div class="text-end">
                    <h4 class="fw-bold mb-0 text-dark">{{ $invoice->invoice_number }}</h4>
                    <span class="badge badge-{{ $invoice->payment_status }} text-capitalize fs-6">{{ $invoice->payment_status }}</span>
                    <div class="small text-muted mt-1">Date: {{ $invoice->invoice_date->format('M d, Y') }}</div>
                </div>
            </div>

            <!-- Billed To -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1">Patient Bill-To</small>
                    <h5 class="fw-bold text-dark mb-0">{{ $invoice->patient->full_name }}</h5>
                    <div class="small text-muted">MRN: {{ $invoice->patient->mrn }}</div>
                    <div class="small text-muted">Phone: {{ $invoice->patient->phone }}</div>
                    @if($invoice->patient->insurance_provider)
                        <div class="small text-primary fw-semibold">Payer: {{ $invoice->patient->insurance_provider }} ({{ $invoice->patient->insurance_policy_number }})</div>
                    @endif
                </div>
                <div class="col-sm-6 text-sm-end">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1">Account Summary</small>
                    <div class="small">Payment Method: <strong>{{ ucfirst($invoice->payment_method ?? 'Pending') }}</strong></div>
                    <div class="small">Cashier / Staff: <strong>{{ $invoice->generatedBy->name ?? 'Admin' }}</strong></div>
                    @if($invoice->due_date)
                        <div class="small text-danger">Due By: {{ $invoice->due_date->format('M d, Y') }}</div>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="fw-semibold text-dark">{{ $item->description }}</td>
                                <td><span class="badge bg-light text-dark border text-capitalize">{{ str_replace('_', ' ', $item->item_type) }}</span></td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-bold">{{ $currencySymbol }}{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Calculation Totals -->
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <ul class="list-group list-group-flush small">
                        @if($invoice->discount_amount > 0)
                            <li class="list-group-item d-flex justify-content-between text-muted">
                                <span>Discount</span>
                                <span>- {{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</span>
                            </li>
                        @endif
                        @if($invoice->tax_amount > 0)
                            <li class="list-group-item d-flex justify-content-between text-muted">
                                <span>Hospital Service Tax</span>
                                <span>+ {{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between fw-bold fs-6">
                            <span>Total Billed</span>
                            <span>{{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between text-success fw-bold">
                            <span>Paid to Date</span>
                            <span>{{ $currencySymbol }}{{ number_format($invoice->paid_amount, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between fw-bold fs-5 {{ $invoice->balance_due > 0 ? 'text-danger bg-danger bg-opacity-10' : 'text-muted' }}">
                            <span>Balance Due</span>
                            <span>{{ $currencySymbol }}{{ number_format($invoice->balance_due, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if($invoice->payments->isNotEmpty())
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-1"></i> Recorded Payment Receipts</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Date & Time</th>
                                    <th>Tender Method</th>
                                    <th>Reference / Auth</th>
                                    <th>Cashier</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $payment->payment_number }}</td>
                                        <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                        <td><span class="badge bg-light text-dark border text-capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</span></td>
                                        <td>{{ $payment->transaction_reference ?? '-' }}</td>
                                        <td>{{ $payment->recordedBy->name ?? 'System' }}</td>
                                        <td class="text-end fw-bold text-success">{{ $currencySymbol }}{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($invoice->notes)
                <div class="mt-4 p-3 bg-light rounded border small text-muted">
                    <strong>Billing Notes:</strong><br>
                    {!! nl2br(e($invoice->notes)) !!}
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Payment Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Payment Status</h6>
            </div>
            <div class="card-body p-4 text-center">
                @if($invoice->balance_due <= 0)
                    <div class="text-success mb-2">
                        <i class="bi bi-patch-check-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-success mb-1">Account Fully Settled</h5>
                    <p class="text-muted small">This invoice has been cleared in full.</p>
                @else
                    <div class="text-danger mb-2">
                        <i class="bi bi-clock-history fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-danger mb-1">{{ $currencySymbol }}{{ number_format($invoice->balance_due, 2) }}</h5>
                    <p class="text-muted small mb-3">Outstanding balance pending receipt.</p>
                    <button type="button" class="btn btn-success w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        Record Cashier Payment
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('billing.payment', $invoice) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Record Cashier Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Amount ({{ $currencySymbol }}) *</label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg" max="{{ $invoice->balance_due }}" value="{{ $invoice->balance_due }}" required>
                        <div class="form-text">Maximum payable: {{ $currencySymbol }}{{ number_format($invoice->balance_due, 2) }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Tender Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash Tender</option>
                            <option value="credit_card">Credit Card (POS Terminal)</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Direct Bank Transfer</option>
                            <option value="insurance">Third-Party Insurance Guarantee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Transaction Reference / POS Approval Code</label>
                        <input type="text" name="transaction_reference" class="form-control" placeholder="e.g. POS-AUTH-9812, TXN-0012">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Receipt Memo / Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Cash tendered at desk, partial installment, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">Accept Payment & Issue Receipt</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
