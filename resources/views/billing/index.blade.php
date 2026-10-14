@extends('layouts.app')

@section('title', 'Billing & Revenue Cycle')
@section('page_title', 'Revenue Cycle Management - Invoices & Cashier')

@section('content')
<!-- Financial Health Metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="metric-card">
            <div class="metric-val text-primary">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($totalBilled, 2) }}</div>
            <div class="metric-label">Total Gross Billed</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="metric-card">
            <div class="metric-val text-success">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($totalCollected, 2) }}</div>
            <div class="metric-label">Total Cash & Payments Collected</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="metric-card">
            <div class="metric-val text-danger">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($totalOutstanding, 2) }}</div>
            <div class="metric-label">Outstanding Receivables</div>
        </div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form action="{{ route('billing.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 500px;">
            <input type="text" name="search" class="form-control" placeholder="Search by Invoice #, Patient Name, MRN..." value="{{ request('search') }}">
            <select name="payment_status" class="form-select" style="max-width: 160px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <a href="{{ route('billing.create') }}" class="btn btn-primary">
            <i class="bi bi-receipt me-1"></i> Create Manual Invoice
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Total Tariff</th>
                    <th>Amount Paid</th>
                    <th>Balance Due</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="fw-bold">{{ $inv->invoice_number }}</td>
                        <td class="small">{{ $inv->invoice_date->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('patients.show', $inv->patient) }}" class="fw-semibold text-decoration-none">
                                {{ $inv->patient->full_name }}
                            </a>
                            <div class="small text-muted">{{ $inv->patient->mrn }}</div>
                        </td>
                        <td class="fw-bold">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($inv->total_amount, 2) }}</td>
                        <td class="text-success">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($inv->paid_amount, 2) }}</td>
                        <td class="fw-bold {{ $inv->balance_due > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($inv->balance_due, 2) }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $inv->payment_status }} text-capitalize">{{ $inv->payment_status }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('billing.show', $inv) }}" class="btn btn-outline-primary" title="Open Statement">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('billing.print', $inv) }}" target="_blank" class="btn btn-outline-secondary" title="Print Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No invoices found matching criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
        <div class="card-footer bg-white">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
