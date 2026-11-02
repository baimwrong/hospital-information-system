@extends('layouts.app')

@section('title', 'Inpatient Admission - ' . $admission->admission_number)
@section('page_title', 'Inpatient Hospitalization Record')

@section('content')
<div class="mb-3 d-flex align-items-center justify-content-between">
    <a href="{{ route('inpatient.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Admissions
    </a>
    <div>
        @if($admission->status === 'admitted')
            <button type="button" class="btn btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#transferBedModal">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer Bed / Ward
            </button>
        @endif
        <a href="{{ route('patients.show', $admission->patient) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-person me-1"></i> Open Patient EHR
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Admission Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="badge bg-primary fs-6 me-2">{{ $admission->admission_number }}</span>
                    <span class="badge bg-{{ $admission->status === 'admitted' ? 'success' : 'secondary' }} text-capitalize">
                        {{ $admission->status }}
                    </span>
                </div>
                <small class="text-muted">Admitted: {{ $admission->admission_date->format('M d, Y - H:i') }}</small>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Assigned Ward & Bed</small>
                        <strong class="fs-5 text-dark">{{ $admission->bed->ward->name }}</strong>
                        <div class="text-primary fw-bold">Bed {{ $admission->bed->bed_number }}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Attending Physician</small>
                        <strong class="fs-5 text-dark">{{ $admission->doctor->user->name ?? 'Physician' }}</strong>
                        <div class="text-muted small">{{ $admission->doctor->specialization }}</div>
                    </div>
                </div>

                <h6 class="fw-bold text-muted small text-uppercase">Admission Reason</h6>
                <div class="p-3 bg-light rounded border mb-4">
                    {{ $admission->admission_reason }}
                </div>

                @if($admission->status === 'discharged')
                    <div class="alert alert-info border mb-0">
                        <h6 class="fw-bold text-dark"><i class="bi bi-clipboard2-check me-1"></i> Discharge Summary</h6>
                        <p class="mb-2">{{ $admission->discharge_summary }}</p>
                        <div class="small">
                            <strong>Discharge Condition:</strong> <span class="badge bg-success text-capitalize">{{ str_replace('_', ' ', $admission->condition_at_discharge) }}</span> &bull; 
                            <strong>Discharge Date:</strong> {{ $admission->discharge_date?->format('M d, Y - H:i') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Discharge Patient Form (If currently admitted) -->
        @if($admission->status === 'admitted')
            <div class="card border-0 shadow-sm border-warning">
                <div class="card-header bg-warning bg-opacity-10 py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-box-arrow-right text-warning me-2"></i>Discharge Patient & Generate Inpatient Invoice</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('inpatient.discharge', $admission) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Discharge Clinical Summary *</label>
                            <textarea name="discharge_summary" class="form-control" rows="3" placeholder="Condition upon release, medications reconciled, outpatient follow-up plan..." required>{{ old('discharge_summary') }}</textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Discharge Patient Condition *</label>
                                <select name="condition_at_discharge" class="form-select" required>
                                    <option value="recovered">Recovered / Resolved</option>
                                    <option value="improved" selected>Improved / Clinically Stable</option>
                                    <option value="referred">Referred to Tertiary Specialty</option>
                                    <option value="against_medical_advice">Discharged Against Medical Advice (DAMA)</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="p-2 bg-light rounded border w-100 small">
                                    <strong>Calculated Stay:</strong> {{ $admission->duration_days }} day(s) @ {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($admission->bed->effective_rate, 2) }}/day
                                    = <strong class="text-primary">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($admission->duration_days * $admission->bed->effective_rate, 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning fw-bold px-4">
                            Confirm Discharge & Create Invoice
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Patient Context Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Patient Details</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $admission->patient->full_name }}</h5>
                <span class="badge bg-light text-dark border mb-3">{{ $admission->patient->mrn }}</span>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-1"><strong>Age / Gender:</strong> {{ $admission->patient->age }} yrs, {{ ucfirst($admission->patient->gender) }}</li>
                    <li class="mb-1"><strong>Blood Group:</strong> {{ $admission->patient->blood_group }}</li>
                    <li class="mb-1"><strong>Emergency Contact:</strong> {{ $admission->patient->emergency_contact_name }} ({{ $admission->patient->emergency_contact_phone }})</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@if($admission->status === 'admitted')
<!-- Modal: Transfer Bed -->
<div class="modal fade" id="transferBedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('inpatient.admissions.transfer', $admission) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Transfer Inpatient Bed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3">
                        Currently in <strong>{{ $admission->bed->ward->name }}</strong> - Bed <strong>{{ $admission->bed->bed_number }}</strong>.
                        Transferring will mark the old bed as <em>cleaning</em> and assign the patient to the selected new bed.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Destination Ward & Bed *</label>
                        <select name="destination_bed_id" class="form-select" required>
                            <option value="">-- Choose available destination bed --</option>
                            @foreach($availableBeds ?? [] as $b)
                                <option value="{{ $b->id }}">
                                    {{ $b->ward->name }} - Bed {{ $b->bed_number }} ({{ ucfirst($b->bed_type) }} &bull; {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($b->effective_rate, 2) }}/day)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Clinical Reason for Transfer</label>
                        <input type="text" name="transfer_reason" class="form-control" placeholder="e.g. Upgraded to ICU due to clinical deterioration / Step down to General Ward">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm fw-bold">Confirm Bed Transfer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
