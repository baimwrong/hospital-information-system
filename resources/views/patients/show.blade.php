@extends('layouts.app')

@section('title', 'Patient EHR - ' . $patient->full_name)
@section('page_title', 'Electronic Health Record (EHR)')

@section('content')
<!-- Patient Clinical Banner -->
<div class="patient-header-card mb-4 shadow-sm">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold fs-3 shadow" style="width: 64px; height: 64px;">
                {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name, 0, 1)) }}
            </div>
            <div>
                <h3 class="fw-bold mb-1 text-white">{{ $patient->full_name }}</h3>
                <div class="d-flex flex-wrap align-items-center gap-2 small text-white-50">
                    <span class="badge bg-light text-dark fw-bold">{{ $patient->mrn }}</span>
                    <span>&bull; {{ ucfirst($patient->gender) }}, {{ $patient->age }} yrs ({{ $patient->date_of_birth?->format('M d, Y') }})</span>
                    <span>&bull; <i class="bi bi-telephone"></i> {{ $patient->phone }}</span>
                    <span>&bull; Blood: <strong class="text-white">{{ $patient->blood_group }}</strong> ({{ $patient->genotype ?? 'N/A' }})</span>
                </div>
            </div>
        </div>

        <!-- Quick Clinical Actions -->
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('vitals.create', ['patient_id' => $patient->id]) }}" class="btn btn-light btn-sm text-dark fw-semibold">
                <i class="bi bi-activity text-danger me-1"></i> Record Vitals
            </a>
            <a href="{{ route('encounters.create', ['patient_id' => $patient->id]) }}" class="btn btn-warning btn-sm fw-semibold">
                <i class="bi bi-journal-medical me-1"></i> New Consultation
            </a>
            <a href="{{ route('inpatient.admissions.create', ['patient_id' => $patient->id]) }}" class="btn btn-info btn-sm text-white fw-semibold">
                <i class="bi bi-door-open me-1"></i> Admit to Ward
            </a>
            <a href="{{ route('billing.create', ['patient_id' => $patient->id]) }}" class="btn btn-success btn-sm fw-semibold">
                <i class="bi bi-receipt me-1"></i> Generate Invoice
            </a>
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-pencil"></i>
            </a>
        </div>
    </div>

    <!-- Alert Strip: Allergies & Chronic Conditions -->
    <div class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex flex-wrap align-items-center gap-2">
        <span class="small fw-bold text-white"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Allergies:</span>
        @if(!empty($patient->allergies) && is_array($patient->allergies))
            @foreach($patient->allergies as $allergy)
                <span class="badge bg-danger">{{ $allergy }}</span>
            @endforeach
        @else
            <span class="small text-white-50">No known allergies recorded</span>
        @endif

        <span class="ms-3 small fw-bold text-white"><i class="bi bi-heart-pulse me-1"></i> Chronic:</span>
        @if(!empty($patient->chronic_diseases) && is_array($patient->chronic_diseases))
            @foreach($patient->chronic_diseases as $chronic)
                <span class="badge bg-secondary">{{ $chronic }}</span>
            @endforeach
        @else
            <span class="small text-white-50">None</span>
        @endif

        @if($patient->insurance_provider)
            <span class="ms-auto badge bg-light text-primary">
                <i class="bi bi-shield-check me-1"></i> {{ $patient->insurance_provider }} ({{ $patient->insurance_policy_number }})
            </span>
        @endif
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm border" id="ehrTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="encounters-tab" data-bs-toggle="tab" data-bs-target="#encounters-pane" type="button" role="tab">
            <i class="bi bi-journal-medical me-1"></i> Clinical Encounters ({{ $patient->medicalRecords->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="vitals-tab" data-bs-toggle="tab" data-bs-target="#vitals-pane" type="button" role="tab">
            <i class="bi bi-activity me-1"></i> Vital Signs ({{ $patient->vitals->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions-pane" type="button" role="tab">
            <i class="bi bi-capsule me-1"></i> Prescriptions ({{ $patient->prescriptions->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="labs-tab" data-bs-toggle="tab" data-bs-target="#labs-pane" type="button" role="tab">
            <i class="bi bi-clipboard2-pulse me-1"></i> Lab Reports ({{ $patient->labRequests->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="admissions-tab" data-bs-toggle="tab" data-bs-target="#admissions-pane" type="button" role="tab">
            <i class="bi bi-hospital me-1"></i> Admissions ({{ $patient->admissions->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices-pane" type="button" role="tab">
            <i class="bi bi-receipt me-1"></i> Billing ({{ $patient->invoices->count() }})
        </button>
    </li>
</ul>

<!-- Tab Panes -->
<div class="tab-content" id="ehrTabContent">
    <!-- Encounters Tab -->
    <div class="tab-pane fade show active" id="encounters-pane" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Physician Consultations & Progress Notes</h5>
            <a href="{{ route('encounters.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Document Encounter
            </a>
        </div>
        @forelse($patient->medicalRecords as $enc)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-primary me-2">{{ $enc->encounter_number }}</span>
                        <strong class="text-dark">{{ $enc->diagnosis_primary }}</strong>
                        @if($enc->diagnosis_icd10_code)
                            <span class="badge bg-light text-dark border">ICD-10: {{ $enc->diagnosis_icd10_code }}</span>
                        @endif
                    </div>
                    <div class="small text-muted">
                        {{ $enc->created_at->format('M d, Y - h:i A') }} &bull; 
                        Attending: <strong>{{ $enc->doctor->user->name ?? 'Physician' }}</strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="small fw-bold text-muted text-uppercase mb-1">Chief Complaint</h6>
                            <p class="mb-3">{{ $enc->chief_complaint }}</p>

                            @if($enc->history_of_present_illness)
                                <h6 class="small fw-bold text-muted text-uppercase mb-1">History of Present Illness</h6>
                                <p class="mb-0 text-secondary">{{ $enc->history_of_present_illness }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="small fw-bold text-muted text-uppercase mb-1">Treatment Plan & Orders</h6>
                            <p class="mb-3">{{ $enc->treatment_plan ?? 'Routine management' }}</p>

                            @if($enc->doctor_notes)
                                <h6 class="small fw-bold text-muted text-uppercase mb-1">Physician Internal Notes</h6>
                                <p class="mb-0 text-secondary">{{ $enc->doctor_notes }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-end">
                    <a href="{{ route('encounters.show', $enc) }}" class="btn btn-sm btn-outline-secondary">View Encounter File</a>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">
                No clinical encounters documented for this patient yet.
            </div>
        @endforelse
    </div>

    <!-- Vital Signs Tab -->
    <div class="tab-pane fade" id="vitals-pane" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Triage & Vital Signs Log</h5>
            <a href="{{ route('vitals.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-activity me-1"></i> New Vital Signs Entry
            </a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Triage</th>
                            <th>BP (mmHg)</th>
                            <th>Heart Rate</th>
                            <th>Temp (°C)</th>
                            <th>SpO2</th>
                            <th>Weight / Height</th>
                            <th>BMI</th>
                            <th>Nurse</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patient->vitals as $v)
                            <tr>
                                <td class="small">{{ $v->recorded_at->format('M d, Y - H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ $v->triage_level }} text-capitalize">{{ $v->triage_level }}</span>
                                </td>
                                <td class="fw-bold {{ ($v->blood_pressure_systolic > 140) ? 'text-danger' : '' }}">
                                    {{ $v->blood_pressure_systolic }}/{{ $v->blood_pressure_diastolic }}
                                </td>
                                <td>{{ $v->heart_rate }} bpm</td>
                                <td>{{ $v->temperature }} °C</td>
                                <td>{{ $v->oxygen_saturation }}%</td>
                                <td>{{ $v->weight }}kg / {{ $v->height }}cm</td>
                                <td>{{ $v->bmi }}</td>
                                <td class="small text-muted">{{ $v->recordedBy->name ?? 'Staff' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No vitals entries recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Prescriptions Tab -->
    <div class="tab-pane fade" id="prescriptions-pane" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Prescription Orders & Pharmacy Fulfillments</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Rx Number</th>
                            <th>Date</th>
                            <th>Physician</th>
                            <th>Prescribed Drugs</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patient->prescriptions as $rx)
                            <tr>
                                <td class="fw-bold">{{ $rx->prescription_number }}</td>
                                <td class="small">{{ $rx->created_at->format('M d, Y') }}</td>
                                <td>{{ $rx->doctor->user->name ?? 'Doctor' }}</td>
                                <td>
                                    @foreach($rx->items as $item)
                                        <div class="small">
                                            <strong>{{ $item->medicine->brand_name }}</strong> ({{ $item->dosage }}) - Qty: {{ $item->quantity_prescribed }}
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge bg-{{ $rx->status === 'dispensed' ? 'success' : 'warning text-dark' }} text-capitalize">
                                        {{ $rx->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No prescriptions issued for this patient.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Lab Reports Tab -->
    <div class="tab-pane fade" id="labs-pane" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Diagnostic Pathology & Laboratory Orders</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Doctor</th>
                            <th>Tests & Results</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patient->labRequests as $lab)
                            <tr>
                                <td class="fw-bold">{{ $lab->request_number }}</td>
                                <td class="small">{{ $lab->created_at->format('M d, Y') }}</td>
                                <td>{{ $lab->doctor->user->name ?? 'Doctor' }}</td>
                                <td>
                                    @foreach($lab->results as $res)
                                        <div class="small">
                                            <strong>{{ $res->parameter_name }}:</strong> 
                                            {{ $res->result_value }} {{ $res->unit }}
                                            @if($res->flag !== 'normal')
                                                <span class="badge bg-danger ms-1">{{ strtoupper($res->flag) }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge bg-{{ $lab->status === 'completed' ? 'success' : 'info' }} text-capitalize">
                                        {{ str_replace('_', ' ', $lab->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('laboratory.show', $lab) }}" class="btn btn-sm btn-outline-secondary">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No laboratory orders recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Admissions Tab -->
    <div class="tab-pane fade" id="admissions-pane" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Inpatient Hospitalization History</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Admission #</th>
                            <th>Admit Date</th>
                            <th>Discharge Date</th>
                            <th>Ward & Bed</th>
                            <th>Doctor</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patient->admissions as $adm)
                            <tr>
                                <td class="fw-bold">{{ $adm->admission_number }}</td>
                                <td class="small">{{ $adm->admission_date->format('M d, Y - H:i') }}</td>
                                <td class="small">{{ $adm->discharge_date ? $adm->discharge_date->format('M d, Y - H:i') : 'Still Inpatient' }}</td>
                                <td>{{ $adm->bed->ward->name }} (Bed {{ $adm->bed->bed_number }})</td>
                                <td>{{ $adm->doctor->user->name ?? 'Physician' }}</td>
                                <td class="small">{{ $adm->admission_reason }}</td>
                                <td>
                                    <span class="badge bg-{{ $adm->status === 'admitted' ? 'primary' : 'secondary' }} text-capitalize">
                                        {{ $adm->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No hospitalization records.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Billing Tab -->
    <div class="tab-pane fade" id="invoices-pane" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Invoices & Financial Statement</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Balance Due</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patient->invoices as $inv)
                            <tr>
                                <td class="fw-bold">{{ $inv->invoice_number }}</td>
                                <td class="small">{{ $inv->invoice_date->format('M d, Y') }}</td>
                                <td>{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($inv->total_amount, 2) }}</td>
                                <td class="text-success">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="fw-bold {{ $inv->balance_due > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($inv->balance_due, 2) }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $inv->payment_status }} text-capitalize">{{ $inv->payment_status }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('billing.show', $inv) }}" class="btn btn-sm btn-outline-primary">Invoice</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No invoices generated for this patient.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
