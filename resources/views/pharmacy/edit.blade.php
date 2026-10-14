@extends('layouts.app')

@section('title', 'Edit Medication - ' . $medicine->brand_name)
@section('page_title', 'Pharmacy - Edit Medication')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Update Medicine: {{ $medicine->brand_name }}</h5>
        <span class="badge bg-secondary fs-6">{{ $medicine->code }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('pharmacy.update', $medicine) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Brand / Proprietary Name *</label>
                    <input type="text" name="brand_name" class="form-control" value="{{ old('brand_name', $medicine->brand_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Generic Molecule Name *</label>
                    <input type="text" name="generic_name" class="form-control" value="{{ old('generic_name', $medicine->generic_name) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Category *</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $medicine->category) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Dosage Form *</label>
                    <input type="text" name="dosage_form" class="form-control" value="{{ old('dosage_form', $medicine->dosage_form) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Strength *</label>
                    <input type="text" name="strength" class="form-control" value="{{ old('strength', $medicine->strength) }}" required>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Stock & Pricing</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Current Stock Level *</label>
                    <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $medicine->stock_quantity) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Min Stock Alert Level *</label>
                    <input type="number" name="min_stock_alert" class="form-control" value="{{ old('min_stock_alert', $medicine->min_stock_alert) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Unit Retail Price *</label>
                    <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price', $medicine->unit_price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Purchase Cost</label>
                    <input type="number" step="0.01" name="purchase_cost" class="form-control" value="{{ old('purchase_cost', $medicine->purchase_cost) }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Batch & Expiry</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Batch Number</label>
                    <input type="text" name="batch_number" class="form-control" value="{{ old('batch_number', $medicine->batch_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $medicine->expiry_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Manufacturer</label>
                    <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer', $medicine->manufacturer) }}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Storage Condition</label>
                    <input type="text" name="storage_condition" class="form-control" value="{{ old('storage_condition', $medicine->storage_condition) }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pharmacy.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Update Stock & Record</button>
            </div>
        </form>
    </div>
</div>
@endsection
