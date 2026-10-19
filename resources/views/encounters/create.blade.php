@extends('layouts.app')

@section('title', 'Conduct Clinical Encounter')
@section('page_title', 'Physician Consultation & EMR Encounter')

@section('content')
<div class="row g-4">
    <!-- Main Consultation Workspace -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-journal-medical text-primary me-2"></i>Clinical Encounter Assessment</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('encounters.store') }}" method="POST">
                    @csrf

                    @if($appointment)
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Patient *</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">-- Choose Patient --</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ (old('patient_id', $patient?->id) == $p->id) ? 'selected' : '' }}>
                                        {{ $p->mrn }} - {{ $p->full_name }} ({{ $p->age }} yrs, {{ $p->gender }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Attending Physician *</label>
                            <select name="doctor_id" class="form-select" required>
                                <option value="">-- Choose Attending Doctor --</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}" {{ (old('doctor_id', $currentDoctor?->id ?? $appointment?->doctor_id) == $d->id) ? 'selected' : '' }}>
                                        {{ $d->user->name ?? 'Physician' }} - {{ $d->specialization }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-chat-left-dots me-1"></i> Clinical History</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Chief Complaint (Presenting Problem) *</label>
                        <textarea name="chief_complaint" class="form-control" rows="2" placeholder="e.g. Severe throbbing frontal headache for 3 days, accompanied by nausea..." required>{{ old('chief_complaint', $appointment?->reason_for_visit) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">History of Present Illness (HPI)</label>
                        <textarea name="history_of_present_illness" class="form-control" rows="2" placeholder="Chronological timeline of symptoms, exacerbating/relieving factors...">{{ old('history_of_present_illness') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Physical Examination Findings</label>
                        <textarea name="physical_examination" class="form-control" rows="2" placeholder="HEENT, Cardiovascular, Respiratory, Abdominal, Neurological findings...">{{ old('physical_examination') }}</textarea>
                    </div>

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-card-checklist me-1"></i> Diagnostic Assessment & ICD-10</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Primary Diagnosis *</label>
                            <input type="text" name="diagnosis_primary" class="form-control" placeholder="e.g. Essential (primary) hypertension" value="{{ old('diagnosis_primary') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">ICD-10 Diagnostic Code</label>
                            <input type="text" name="diagnosis_icd10_code" class="form-control" placeholder="e.g. I10, E11.9, J06.9" value="{{ old('diagnosis_icd10_code') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Secondary / Differential Diagnoses</label>
                            <input type="text" name="diagnosis_secondary" class="form-control" placeholder="e.g. Tension type headache, Chronic insomnia" value="{{ old('diagnosis_secondary') }}">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-capsule me-1"></i> Electronic Prescription (Direct Formulary Order)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addMedBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add Medication
                        </button>
                    </div>
                    <div class="p-3 bg-light rounded border mb-4">
                        <div id="medicinesContainer">
                            <div class="row g-2 mb-2 med-row align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold">Select Drug</label>
                                    <select name="medicines[0][id]" class="form-select form-select-sm">
                                        <option value="">-- Choose Medication --</option>
                                        @foreach($medicines as $med)
                                            <option value="{{ $med->id }}">{{ $med->brand_name }} ({{ $med->strength }}) - Stock: {{ $med->stock_quantity }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Dosage</label>
                                    <input type="text" name="medicines[0][dosage]" class="form-control form-control-sm" placeholder="1 tab daily">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Quantity</label>
                                    <input type="number" name="medicines[0][quantity]" class="form-control form-control-sm" placeholder="30">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Instructions</label>
                                    <div class="d-flex gap-1">
                                        <input type="text" name="medicines[0][instructions]" class="form-control form-control-sm" placeholder="After meals">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-med-btn" title="Remove Drug" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-clipboard2-pulse me-1"></i> Order Laboratory Investigations</h6>
                    <div class="row g-2 mb-4 p-3 bg-light rounded border">
                        @foreach($labTestTypes as $testType)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="lab_test_type_ids[]" value="{{ $testType->id }}" id="lab_test_{{ $testType->id }}">
                                    <label class="form-check-label small" for="lab_test_{{ $testType->id }}">
                                        <strong>{{ $testType->name }}</strong> ({{ $testType->category }})
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-calendar-event me-1"></i> Treatment Plan & Follow-up</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Treatment Plan, Dietary & Activity Guidance</label>
                        <textarea name="treatment_plan" class="form-control" rows="2" placeholder="Lifestyle modifications, self-monitoring instructions, return precautions...">{{ old('treatment_plan') }}</textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Follow-up Clinical Date</label>
                            <input type="date" name="follow_up_date" class="form-control" value="{{ old('follow_up_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Doctor Private Confidential Notes</label>
                            <input type="text" name="doctor_notes" class="form-control" placeholder="Internal clinical impressions..." value="{{ old('doctor_notes') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('encounters.index') }}" class="btn btn-light border px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 fw-semibold">Save Encounter & Dispatch Orders</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Patient Context Sidebar -->
    <div class="col-lg-4">
        @if($patient)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Active Patient Profile</h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $patient->full_name }}</h5>
                    <span class="badge bg-light text-dark border mb-3">{{ $patient->mrn }}</span>
                    <ul class="list-unstyled small mb-3">
                        <li class="mb-1"><strong>Age / Gender:</strong> {{ $patient->age }} yrs, {{ ucfirst($patient->gender) }}</li>
                        <li class="mb-1"><strong>Blood Group:</strong> {{ $patient->blood_group }} ({{ $patient->genotype ?? 'N/A' }})</li>
                        <li class="mb-1"><strong>Phone:</strong> {{ $patient->phone }}</li>
                    </ul>

                    <h6 class="small fw-bold text-danger mb-1"><i class="bi bi-exclamation-octagon me-1"></i> Allergies</h6>
                    <div class="mb-3">
                        @if(!empty($patient->allergies) && is_array($patient->allergies))
                            @foreach($patient->allergies as $alg)
                                <span class="badge bg-danger mb-1">{{ $alg }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">No allergies recorded</span>
                        @endif
                    </div>

                    <h6 class="small fw-bold text-dark mb-1"><i class="bi bi-heart-pulse me-1"></i> Chronic Conditions</h6>
                    <div>
                        @if(!empty($patient->chronic_diseases) && is_array($patient->chronic_diseases))
                            @foreach($patient->chronic_diseases as $cd)
                                <span class="badge bg-secondary mb-1">{{ $cd }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">None</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Latest Vitals Widget -->
            @if($latestVital = $patient->vitals->first())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-activity text-danger me-2"></i>Most Recent Vitals</h6>
                        <small class="text-muted">{{ $latestVital->recorded_at->diffForHumans() }}</small>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">Blood Pressure</small>
                                    <strong class="fs-6 {{ $latestVital->blood_pressure_systolic > 140 ? 'text-danger' : 'text-dark' }}">
                                        {{ $latestVital->blood_pressure_systolic }}/{{ $latestVital->blood_pressure_diastolic }}
                                    </strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">Heart Rate</small>
                                    <strong class="fs-6 text-dark">{{ $latestVital->heart_rate }} bpm</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">Body Temp</small>
                                    <strong class="fs-6 text-dark">{{ $latestVital->temperature }} °C</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">Oxygen SpO2</small>
                                    <strong class="fs-6 text-dark">{{ $latestVital->oxygen_saturation }}%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="card border-0 shadow-sm p-4 text-center text-muted">
                <i class="bi bi-person-bounding-box fs-1 d-block mb-2 text-secondary"></i>
                Select a patient from the dropdown to view clinical history and vitals.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let medIndex = 1;
    const container = document.getElementById('medicinesContainer');
    const addMedBtn = document.getElementById('addMedBtn');

    if (!container || !addMedBtn) return;

    // Grab medicines select options from the initial row
    const initialSelect = container.querySelector('select');
    const optionsHtml = initialSelect ? initialSelect.innerHTML : '';

    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.med-row');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-med-btn');
            if (btn) {
                btn.disabled = (rows.length <= 1);
            }
        });
    }

    addMedBtn.addEventListener('click', function () {
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 mb-2 med-row align-items-center';
        newRow.innerHTML = `
            <div class="col-md-5">
                <select name="medicines[${medIndex}][id]" class="form-select form-select-sm">
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="medicines[${medIndex}][dosage]" class="form-control form-control-sm" placeholder="Dosage">
            </div>
            <div class="col-md-2">
                <input type="number" name="medicines[${medIndex}][quantity]" class="form-control form-control-sm" placeholder="Qty">
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-1">
                    <input type="text" name="medicines[${medIndex}][instructions]" class="form-control form-control-sm" placeholder="Instructions">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-med-btn" title="Remove Drug">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newRow);
        medIndex++;
        updateRemoveButtons();
    });

    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-med-btn');
        if (btn) {
            const rows = container.querySelectorAll('.med-row');
            if (rows.length > 1) {
                btn.closest('.med-row').remove();
                updateRemoveButtons();
            }
        }
    });

    updateRemoveButtons();
});
</script>
@endpush
