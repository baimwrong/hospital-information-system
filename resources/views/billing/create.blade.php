@extends('layouts.app')

@section('title', 'Generate Invoice')
@section('page_title', 'Billing - Custom Fee & Service Invoicing')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-receipt text-primary me-2"></i>Create Medical Invoice</h5>
        <span class="badge bg-primary fs-6">{{ $nextNumber }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('billing.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Patient Account *</label>
                    <select name="patient_id" class="form-select form-select-lg" required>
                        <option value="">-- Choose Patient --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ (old('patient_id', $selectedPatient?->id) == $p->id) ? 'selected' : '' }}>
                                {{ $p->mrn }} - {{ $p->full_name }} ({{ $p->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Invoice Date *</label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Itemized Billing Charges *</h6>
            <div id="itemsContainer" class="mb-4">
                <div class="row g-2 mb-2 item-row">
                    <div class="col-md-3">
                        <select name="items[0][item_type]" class="form-select form-select-sm" required>
                            <option value="procedure">Clinical Procedure</option>
                            <option value="consultation">Consultation Fee</option>
                            <option value="medication">Medication / Consumable</option>
                            <option value="lab_test">Laboratory Investigation</option>
                            <option value="bed_charge">Nursing / Ward Charge</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Description of service/fee..." required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[0][quantity]" class="form-control form-control-sm qty-input" value="1" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm price-input" placeholder="Unit Price" required>
                    </div>
                </div>

                <div class="row g-2 mb-2 item-row">
                    <div class="col-md-3">
                        <select name="items[1][item_type]" class="form-select form-select-sm">
                            <option value="medication">Medication / Consumable</option>
                            <option value="procedure">Clinical Procedure</option>
                            <option value="consultation">Consultation Fee</option>
                            <option value="lab_test">Laboratory Investigation</option>
                            <option value="bed_charge">Nursing / Ward Charge</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="items[1][description]" class="form-control form-control-sm" placeholder="Additional service (optional)...">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[1][quantity]" class="form-control form-control-sm qty-input" value="1" min="1">
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="items[1][unit_price]" class="form-control form-control-sm price-input" placeholder="Unit Price">
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Billing Notes / Insurance Policy</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Third-party insurance details, waiver notes...">{{ old('notes') }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Discount Amount</label>
                    <input type="number" step="0.01" name="discount_amount" class="form-control" value="{{ old('discount_amount', '0.00') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tax Amount</label>
                    <input type="number" step="0.01" name="tax_amount" class="form-control" value="{{ old('tax_amount', '0.00') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('billing.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Create Invoice</button>
            </div>
        </form>
    </div>
</div>
@endsection
