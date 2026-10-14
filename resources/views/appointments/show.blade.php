@extends('layouts.app')

@section('title', 'Appointment - ' . $appointment->appointment_number)
@section('page_title', 'Appointment File')

@section('content')
<div class="mb-3">
    <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Schedule
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 fw-bold">{{ $appointment->appointment_number }}</h5>
                    <small class="text-muted">{{ $appointment->appointment_date->format('l, F j, Y') }} at {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-{{ $appointment->priority }} text-capitalize fs-6">{{ $appointment->priority }}</span>
                    <span class="badge badge-{{ $appointment->status }} text-capitalize fs-6">{{ str_replace('_', ' ', $appointment->status) }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                <h6 class="fw-bold text-muted text-uppercase small">Reason for Consultation</h6>
                <p class="fs-6 mb-4">{{ $appointment->reason_for_visit }}</p>

                @if($appointment->notes)
                    <h6 class="fw-bold text-muted text-uppercase small">Booking Notes</h6>
                    <p class="text-secondary mb-4">{{ $appointment->notes }}</p>
                @endif

                <hr class="my-4">

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('vitals.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" class="btn btn-primary d-flex align-items-center gap-1">
                        <i class="bi bi-activity"></i> Record Nursing Vitals
                    </a>
                    <a href="{{ route('encounters.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" class="btn btn-warning d-flex align-items-center gap-1">
                        <i class="bi bi-journal-medical"></i> Conduct Doctor Consultation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Patient Information</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $appointment->patient->full_name }}</h5>
                <span class="badge bg-light text-dark border mb-3">{{ $appointment->patient->mrn }}</span>

                <ul class="list-unstyled small mb-3">
                    <li class="mb-1"><strong class="text-muted">Age / Gender:</strong> {{ $appointment->patient->age }} yrs, {{ ucfirst($appointment->patient->gender) }}</li>
                    <li class="mb-1"><strong class="text-muted">Phone:</strong> {{ $appointment->patient->phone }}</li>
                    <li class="mb-1"><strong class="text-muted">Blood Group:</strong> {{ $appointment->patient->blood_group }}</li>
                </ul>

                <a href="{{ route('patients.show', $appointment->patient) }}" class="btn btn-outline-primary btn-sm w-100">
                    Open Complete EHR File
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Assigned Physician</h6>
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-1">{{ $appointment->doctor->user->name ?? 'Physician' }}</h6>
                <div class="text-muted small mb-2">{{ $appointment->doctor->specialization }}</div>
                <div class="small mb-1"><strong>Room:</strong> {{ $appointment->doctor->room_number ?? 'OPD Clinic' }}</div>
                <div class="small"><strong>Department:</strong> {{ $appointment->department->name ?? 'OPD' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
