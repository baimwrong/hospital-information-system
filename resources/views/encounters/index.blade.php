@extends('layouts.app')

@section('title', 'Clinical Encounters')
@section('page_title', 'Outpatient Electronic Medical Records (EMR)')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('encounters.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Search encounters by Encounter #, Primary Diagnosis, ICD-10 Code, Patient Name or MRN..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('encounters.create') }}" class="btn btn-primary w-100">
                    <i class="bi bi-plus-lg me-1"></i> New Encounter
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Physician Consultation Encounters</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Encounter #</th>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Physician</th>
                    <th>Primary Diagnosis</th>
                    <th>ICD-10</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $enc)
                    <tr>
                        <td class="fw-bold">{{ $enc->encounter_number }}</td>
                        <td class="small">{{ $enc->created_at->format('M d, Y - H:i') }}</td>
                        <td>
                            <a href="{{ route('patients.show', $enc->patient) }}" class="fw-semibold text-decoration-none">
                                {{ $enc->patient->full_name }}
                            </a>
                            <div class="small text-muted">{{ $enc->patient->mrn }}</div>
                        </td>
                        <td>{{ $enc->doctor->user->name ?? 'Physician' }}</td>
                        <td class="fw-semibold text-dark">{{ $enc->diagnosis_primary }}</td>
                        <td>
                            @if($enc->diagnosis_icd10_code)
                                <span class="badge bg-light text-dark border">{{ $enc->diagnosis_icd10_code }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('encounters.show', $enc) }}" class="btn btn-sm btn-outline-primary">
                                Full Encounter
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No clinical encounters recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
        <div class="card-footer bg-white">
            {{ $records->links() }}
        </div>
    @endif
</div>
@endsection
