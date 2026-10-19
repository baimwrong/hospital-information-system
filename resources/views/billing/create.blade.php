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

            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-list-check me-1"></i> Itemized Billing Charges *</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                    <i class="bi bi-plus-circle me-1"></i> Add Line Item
                </button>
            </div>

            <div id="itemsContainer" class="mb-3">
                <div class="row g-2 mb-2 item-row align-items-center">
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
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm price-input" placeholder="0.00" required>
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Remove Item" disabled>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Live Calculation Summary Card -->
            <div class="card bg-light border-0 mb-4 p-3">
                <div class="row g-2 align-items-center small">
                    <div class="col-sm-3">
                        <span class="text-muted">Gross Subtotal:</span>
                        <div class="fw-bold fs-6 text-dark" id="displaySubtotal">$0.00</div>
                    </div>
                    <div class="col-sm-3">
                        <span class="text-muted">Discount:</span>
                        <div class="fw-bold fs-6 text-danger" id="displayDiscount">-$0.00</div>
                    </div>
                    <div class="col-sm-3">
                        <span class="text-muted">Tax:</span>
                        <div class="fw-bold fs-6 text-secondary" id="displayTax">+$0.00</div>
                    </div>
                    <div class="col-sm-3 text-sm-end">
                        <span class="text-muted">Total Net Payable:</span>
                        <div class="fw-bold fs-5 text-primary" id="displayTotal">$0.00</div>
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
                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountInput" class="form-control" value="{{ old('discount_amount', '0.00') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tax Amount</label>
                    <input type="number" step="0.01" min="0" name="tax_amount" id="taxInput" class="form-control" value="{{ old('tax_amount', '0.00') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('billing.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Create Invoice</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let itemIndex = 1;
    const container = document.getElementById('itemsContainer');
    const addItemBtn = document.getElementById('addItemBtn');
    const discountInput = document.getElementById('discountInput');
    const taxInput = document.getElementById('taxInput');

    const displaySubtotal = document.getElementById('displaySubtotal');
    const displayDiscount = document.getElementById('displayDiscount');
    const displayTax = document.getElementById('displayTax');
    const displayTotal = document.getElementById('displayTotal');

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
            const price = parseFloat(row.querySelector('.price-input')?.value || 0);
            if (!isNaN(qty) && !isNaN(price)) {
                subtotal += (qty * price);
            }
        });

        const discount = parseFloat(discountInput.value || 0) || 0;
        const tax = parseFloat(taxInput.value || 0) || 0;
        const total = Math.max(0, subtotal - discount + tax);

        displaySubtotal.textContent = '$' + subtotal.toFixed(2);
        displayDiscount.textContent = '-$' + discount.toFixed(2);
        displayTax.textContent = '+$' + tax.toFixed(2);
        displayTotal.textContent = '$' + total.toFixed(2);
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-item-btn');
            if (btn) {
                btn.disabled = (rows.length <= 1);
            }
        });
    }

    addItemBtn.addEventListener('click', function () {
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 mb-2 item-row align-items-center';
        newRow.innerHTML = `
            <div class="col-md-3">
                <select name="items[${itemIndex}][item_type]" class="form-select form-select-sm" required>
                    <option value="procedure">Clinical Procedure</option>
                    <option value="consultation">Consultation Fee</option>
                    <option value="medication">Medication / Consumable</option>
                    <option value="lab_test">Laboratory Investigation</option>
                    <option value="bed_charge">Nursing / Ward Charge</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" placeholder="Description of service/fee..." required>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm qty-input" value="1" min="1" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm price-input" placeholder="0.00" required>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Remove Item">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;
        updateRemoveButtons();
        calculateTotals();
    });

    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-item-btn');
        if (btn) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                btn.closest('.item-row').remove();
                updateRemoveButtons();
                calculateTotals();
            }
        }
    });

    container.addEventListener('input', calculateTotals);
    discountInput.addEventListener('input', calculateTotals);
    taxInput.addEventListener('input', calculateTotals);

    calculateTotals();
    updateRemoveButtons();
});
</script>
@endpush
@endsection
