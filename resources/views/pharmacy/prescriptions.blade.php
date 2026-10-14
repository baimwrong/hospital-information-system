@extends('layouts.app')

@section('title', 'Prescription Dispensing')
@section('page_title', 'Pharmacy - Prescription Orders Fulfillment')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0 fw-bold">Outpatient & Inpatient Prescription Orders</h5>
            <small class="text-muted">Review clinician orders, verify dosage safety, and dispense medications from stock.</small>
        </div>
        <div>
            <a href="{{ route('pharmacy.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Inventory Catalog
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Rx Number</th>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Prescribing Doctor</th>
                    <th>Prescribed Formulary Items</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prescriptions as $rx)
                    <tr>
                        <td class="fw-bold">{{ $rx->prescription_number }}</td>
                        <td class="small">{{ $rx->created_at->format('M d, Y - H:i') }}</td>
                        <td>
                            <a href="{{ route('patients.show', $rx->patient) }}" class="fw-semibold text-decoration-none">
                                {{ $rx->patient->full_name }}
                            </a>
                            <div class="small text-muted">{{ $rx->patient->mrn }}</div>
                        </td>
                        <td>{{ $rx->doctor->user->name ?? 'Physician' }}</td>
                        <td>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($rx->items as $item)
                                    <li class="mb-1">
                                        <span class="fw-semibold">{{ $item->medicine->brand_name }}</span> ({{ $item->dosage }}) 
                                        &bull; Qty: <strong>{{ $item->quantity_prescribed }}</strong>
                                        @if($item->instructions)
                                            <span class="text-muted">({{ $item->instructions }})</span>
                                        @endif
                                        @if($item->medicine->stock_quantity < $item->quantity_prescribed && $rx->status !== 'dispensed')
                                            <span class="badge bg-danger ms-1">Stock Low: {{ $item->medicine->stock_quantity }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <span class="badge bg-{{ $rx->status === 'dispensed' ? 'success' : 'warning text-dark' }} text-capitalize">
                                {{ $rx->status }}
                            </span>
                            @if($rx->dispensed_at)
                                <div class="small text-muted" style="font-size: 0.7rem;">{{ $rx->dispensed_at->format('M d, H:i') }}</div>
                            @endif
                        </td>
                        <td>
                            @if($rx->status !== 'dispensed')
                                <form action="{{ route('pharmacy.prescriptions.dispense', $rx) }}" method="POST" onsubmit="return confirm('Dispense this prescription? Stock quantities will be deducted and billing generated.')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                                        <i class="bi bi-check-circle"></i> Dispense
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-light text-success border">
                                    <i class="bi bi-check2-all me-1"></i> Fulfilled
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No prescriptions waiting for dispensing.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($prescriptions->hasPages())
        <div class="card-footer bg-white">
            {{ $prescriptions->links() }}
        </div>
    @endif
</div>
@endsection
