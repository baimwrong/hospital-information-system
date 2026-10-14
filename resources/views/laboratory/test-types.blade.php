@extends('layouts.app')

@section('title', 'Laboratory Test Catalog')
@section('page_title', 'LIS - Diagnostic Test Tariff & Reference Intervals')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between">
    <a href="{{ route('laboratory.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Orders
    </a>
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newTestModal">
        <i class="bi bi-plus-lg me-1"></i> Add Test to Catalog
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Test Code</th>
                    <th>Investigation Name</th>
                    <th>Category</th>
                    <th>Sample Type</th>
                    <th>Price</th>
                    <th>Standard Reference Interval</th>
                    <th>TAT (Hours)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testTypes as $tt)
                    <tr>
                        <td class="fw-bold">{{ $tt->test_code }}</td>
                        <td class="fw-semibold text-dark">{{ $tt->name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $tt->category }}</span></td>
                        <td>{{ $tt->sample_type ?? 'Whole Blood' }}</td>
                        <td class="fw-bold">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($tt->price, 2) }}</td>
                        <td class="small text-muted" style="max-width: 280px;">{{ $tt->reference_range ?? 'Standard range' }}</td>
                        <td>{{ $tt->turnaround_time_hours }} hrs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Test Type -->
<div class="modal fade" id="newTestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('laboratory.test-types.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">New Diagnostic Investigation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Test Code *</label>
                            <input type="text" name="test_code" class="form-control" placeholder="e.g. CRP-01" required>
                        </div>
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Investigation Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. C-Reactive Protein (CRP)" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Category *</label>
                            <input type="text" name="category" class="form-control" placeholder="Immunology" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Price *</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="35.00" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Specimen Type</label>
                            <input type="text" name="sample_type" class="form-control" placeholder="Serum (Gold SST)">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Turnaround (Hours)</label>
                            <input type="number" name="turnaround_time_hours" class="form-control" value="4" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reference Range / Normal Values</label>
                        <textarea name="reference_range" class="form-control" rows="2" placeholder="e.g. <3.0 mg/L (Normal)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save to Catalog</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
