@extends('layouts.app')

@section('title', 'Add Medication')
@section('page_title', 'Pharmacy - Add Drug to Formulary')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-capsule text-primary me-2"></i>New Formulary Drug Entry</h5>
        <span class="badge bg-primary fs-6">{{ $nextCode }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('pharmacy.store') }}" method="POST">
            @csrf
            <input type="hidden" name="code" value="{{ $nextCode }}">

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Brand / Proprietary Name *</label>
                    <input type="text" name="brand_name" class="form-control" placeholder="e.g. Augmentin 625mg" value="{{ old('brand_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Generic / Active Molecule Name *</label>
                    <input type="text" name="generic_name" class="form-control" placeholder="e.g. Amoxicillin + Clavulanic Acid" value="{{ old('generic_name') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Pharmacological Category *</label>
                    <input type="text" name="category" class="form-control" placeholder="e.g. Antibiotics, Analgesics, Antidiabetic" value="{{ old('category') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Dosage Form *</label>
                    <select name="dosage_form" class="form-select" required>
                        <option value="Tablet">Tablet</option>
                        <option value="Capsule">Capsule</option>
                        <option value="Syrup">Syrup</option>
                        <option value="Injection">Injection (Vial/Ampoule)</option>
                        <option value="IV Fluid">IV Fluid Infusion</option>
                        <option value="Inhaler">Aerosol Inhaler</option>
                        <option value="Ointment">Topical Ointment/Cream</option>
                        <option value="Drops">Eye/Ear Drops</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Dosage Strength *</label>
                    <input type="text" name="strength" class="form-control" placeholder="e.g. 500mg, 10mg/ml" value="{{ old('strength') }}" required>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Inventory Quantities & Pricing</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Initial Stock Quantity *</label>
                    <input type="number" name="stock_quantity" class="form-control" placeholder="100" value="{{ old('stock_quantity', 100) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Min Stock Alert Level *</label>
                    <input type="number" name="min_stock_alert" class="form-control" placeholder="20" value="{{ old('min_stock_alert', 20) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Hospital Retail Unit Price *</label>
                    <input type="number" step="0.01" name="unit_price" class="form-control" placeholder="15.00" value="{{ old('unit_price') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Acquisition / Purchase Cost</label>
                    <input type="number" step="0.01" name="purchase_cost" class="form-control" placeholder="10.00" value="{{ old('purchase_cost') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Batch & Expiry Quality Control</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Batch / Lot Number</label>
                    <input type="text" name="batch_number" class="form-control" placeholder="e.g. BAT-2026-09" value="{{ old('batch_number') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Manufacturer / Supplier</label>
                    <input type="text" name="manufacturer" class="form-control" placeholder="Pharmaceutical Ltd" value="{{ old('manufacturer') }}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Storage Condition</label>
                    <input type="text" name="storage_condition" class="form-control" placeholder="e.g. Store below 25°C in dry place" value="{{ old('storage_condition') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pharmacy.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Add Medicine</button>
            </div>
        </form>
    </div>
</div>
@endsection
