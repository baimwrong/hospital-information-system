@extends('layouts.app')

@section('title', 'Clinical Encounter - ' . $encounter->encounter_number)
@section('page_title', 'Clinical Encounter Document')

@section('content')
<div class="mb-3 d-flex align-items-center justify-content-between">
    <a href="{{ route('encounters.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Encounters
    </a>
    <div class="d-flex align-items-center gap-2">
        @if($encounter->prescription)
            <a href="{{ route('pharmacy.prescriptions.print', $encounter->prescription) }}" target="_blank" class="btn btn-sm btn-outline-success">
                <i class="bi bi-printer me-1"></i> Print Rx
            </a>
        @endif
        <a href="{{ route('patients.show', $encounter->patient) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-person me-1"></i> Open Patient EHR
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <div>
            <span class="badge bg-primary fs-6 me-2">{{ $encounter->encounter_number }}</span>
            <span class="text-muted">{{ $encounter->created_at->format('l, F j, Y - H:i') }}</span>
        </div>
        <div>
            <span class="badge bg-light text-dark border">
                Attending: {{ $encounter->doctor->user->name ?? 'Physician' }} ({{ $encounter->doctor->specialization }})
            </span>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Patient Strip -->
        <div class="p-3 bg-light rounded border mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="fw-bold mb-1">{{ $encounter->patient->full_name }}</h5>
                <span class="badge bg-secondary me-2">{{ $encounter->patient->mrn }}</span>
                <span class="small text-muted">{{ $encounter->patient->age }} yrs &bull; {{ ucfirst($encounter->patient->gender) }} &bull; Blood: {{ $encounter->patient->blood_group }}</span>
            </div>
            <div>
                @if($encounter->patient->allergies && is_array($encounter->patient->allergies))
                    <span class="badge bg-danger">Allergies: {{ implode(', ', $encounter->patient->allergies) }}</span>
                @endif
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <h6 class="fw-bold text-primary text-uppercase small"><i class="bi bi-chat-quote me-1"></i> Chief Complaint</h6>
                <div class="p-3 bg-white border rounded">
                    {{ $encounter->chief_complaint }}
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-primary text-uppercase small"><i class="bi bi-clock-history me-1"></i> History of Present Illness</h6>
                <div class="p-3 bg-white border rounded">
                    {{ $encounter->history_of_present_illness ?? 'None recorded.' }}
                </div>
            </div>
        </div>

        @if($encounter->physical_examination)
            <div class="mb-4">
                <h6 class="fw-bold text-primary text-uppercase small"><i class="bi bi-clipboard2-check me-1"></i> Physical Examination</h6>
                <div class="p-3 bg-white border rounded">
                    {{ $encounter->physical_examination }}
                </div>
            </div>
        @endif

        <div class="mb-4">
            <h6 class="fw-bold text-primary text-uppercase small"><i class="bi bi-check2-all me-1"></i> Diagnostic Assessment</h6>
            <div class="p-3 bg-light border rounded">
                <div class="fs-5 fw-bold text-dark mb-1">
                    {{ $encounter->diagnosis_primary }}
                    @if($encounter->diagnosis_icd10_code)
                        <span class="badge bg-primary fs-6 ms-2">ICD-10: {{ $encounter->diagnosis_icd10_code }}</span>
                    @endif
                </div>
                @if($encounter->diagnosis_secondary)
                    <div class="small text-muted"><strong>Secondary:</strong> {{ $encounter->diagnosis_secondary }}</div>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold text-primary text-uppercase small"><i class="bi bi-arrow-repeat me-1"></i> Treatment Plan & Guidance</h6>
            <div class="p-3 bg-white border rounded">
                {{ $encounter->treatment_plan ?? 'Routine management.' }}
            </div>
        </div>

        @if($encounter->prescription)
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold text-primary text-uppercase small mb-0"><i class="bi bi-capsule me-1"></i> Prescribed Medications (Rx #{{ $encounter->prescription->prescription_number }})</h6>
                    <a href="{{ route('pharmacy.prescriptions.print', $encounter->prescription) }}" target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-printer me-1"></i> Print Rx
                    </a>
                </div>
                <div class="table-responsive border rounded bg-white">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Medicine</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Instructions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($encounter->prescription->items as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->medicine->name ?? 'Medication' }}</td>
                                    <td>{{ $item->dosage }}</td>
                                    <td>{{ $item->frequency }}</td>
                                    <td>{{ $item->duration }}</td>
                                    <td class="small text-muted">{{ $item->instructions ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($encounter->labRequests && $encounter->labRequests->isNotEmpty())
            <div class="mb-4">
                <h6 class="fw-bold text-primary text-uppercase small mb-2"><i class="bi bi-clipboard2-pulse me-1"></i> Ordered Laboratory Tests</h6>
                <div class="list-group">
                    @foreach($encounter->labRequests as $req)
                        <div class="list-group-item d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold">{{ $req->request_number }}</span>
                                <span class="badge bg-{{ $req->status === 'completed' ? 'success' : 'secondary' }} ms-2 text-capitalize">{{ str_replace('_', ' ', $req->status) }}</span>
                                <div class="small text-muted">{{ $req->results->pluck('parameter_name')->implode(', ') }}</div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('laboratory.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                    View Results
                                </a>
                                @if($req->status === 'completed')
                                    <a href="{{ route('laboratory.requests.print', $req) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-printer me-1"></i> Print Report
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($encounter->follow_up_date)
            <div class="p-3 bg-primary bg-opacity-10 border border-primary-subtle rounded d-flex align-items-center gap-2">
                <i class="bi bi-calendar-check text-primary fs-4"></i>
                <div>
                    <strong class="text-primary">Clinical Follow-up Scheduled:</strong>
                    <div>{{ $encounter->follow_up_date->format('l, F j, Y') }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
