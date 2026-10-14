@extends('layouts.app')

@section('title', 'Doctor Profile - ' . $doctor->user->name)
@section('page_title', 'Physician Profile & Practice')

@section('content')
<div class="mb-3 d-flex align-items-center justify-content-between">
    <a href="{{ route('doctors.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Roster
    </a>
    @if(Auth::user()->isAdmin())
        <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit Profile
        </a>
    @endif
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fw-bold fs-1 mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-person-badge"></i>
            </div>
            <h4 class="fw-bold mb-1">{{ $doctor->user->name ?? 'Physician' }}</h4>
            <div class="text-primary fw-semibold mb-2">{{ $doctor->specialization }}</div>
            <span class="badge bg-light text-dark border mb-3">{{ $doctor->qualification ?? 'Medical Specialist' }}</span>

            <hr class="my-3">

            <div class="text-start small">
                <div class="mb-2"><strong class="text-muted">Department:</strong> {{ $doctor->department->name ?? 'Clinical' }}</div>
                <div class="mb-2"><strong class="text-muted">License ID:</strong> {{ $doctor->license_number }}</div>
                <div class="mb-2"><strong class="text-muted">Consulting Room:</strong> {{ $doctor->room_number ?? 'OPD Clinic' }}</div>
                <div class="mb-2"><strong class="text-muted">Standard Fee:</strong> {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($doctor->consultation_fee, 2) }}</div>
                <div class="mb-2"><strong class="text-muted">Email:</strong> {{ $doctor->user->email ?? 'N/A' }}</div>
                <div class="mb-2"><strong class="text-muted">Status:</strong> <span class="badge bg-success text-capitalize">{{ $doctor->status }}</span></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Biography & Specialization Overview</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-secondary leading-relaxed mb-0">
                    {{ $doctor->biography ?? 'No formal biography provided for this specialist.' }}
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Recent Clinical Consultations</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Encounter #</th>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Diagnosis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctor->medicalRecords as $mr)
                            <tr>
                                <td class="fw-bold">{{ $mr->encounter_number }}</td>
                                <td class="small">{{ $mr->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('patients.show', $mr->patient) }}" class="fw-semibold text-decoration-none">
                                        {{ $mr->patient->full_name }}
                                    </a>
                                </td>
                                <td>{{ $mr->diagnosis_primary }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No encounters recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
