@extends('layouts.app')

@section('title', 'Inpatient (IPD) Management')
@section('page_title', 'Inpatient Department (IPD) & Hospitalization')

@section('content')
<!-- IPD Occupancy Statistics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="metric-card">
            <div class="metric-val text-primary">{{ $totalAdmitted }}</div>
            <div class="metric-label">Currently Admitted Inpatients</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="metric-card">
            <div class="metric-val text-success">{{ $availableBeds }}</div>
            <div class="metric-label">Available Vacant Beds</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="metric-card">
            <div class="metric-val text-danger">{{ $occupiedBeds }}</div>
            <div class="metric-label">Occupied Beds</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="metric-card">
            <div class="metric-val text-dark">{{ $totalBeds }}</div>
            <div class="metric-label">Total Hospital Capacity</div>
        </div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form action="{{ route('inpatient.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 500px;">
            <input type="text" name="search" class="form-control" placeholder="Search admission #, patient name, MRN..." value="{{ request('search') }}">
            <select name="status" class="form-select" style="max-width: 160px;" onchange="this.form.submit()">
                <option value="admitted" {{ request('status', 'admitted') === 'admitted' ? 'selected' : '' }}>Admitted</option>
                <option value="discharged" {{ request('status') === 'discharged' ? 'selected' : '' }}>Discharged</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('inpatient.wards') }}" class="btn btn-outline-primary">
                <i class="bi bi-grid-3x3-gap me-1"></i> Ward & Bed Grid
            </a>
            <a href="{{ route('inpatient.admissions.create') }}" class="btn btn-primary">
                <i class="bi bi-door-open me-1"></i> New Admission
            </a>
        </div>
    </div>
</div>

<!-- Admissions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Active Inpatient Cohort</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Admission #</th>
                    <th>Patient</th>
                    <th>Ward & Bed</th>
                    <th>Attending Doctor</th>
                    <th>Admission Date</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admissions as $adm)
                    <tr>
                        <td class="fw-bold">{{ $adm->admission_number }}</td>
                        <td>
                            <a href="{{ route('patients.show', $adm->patient) }}" class="fw-semibold text-decoration-none">
                                {{ $adm->patient->full_name }}
                            </a>
                            <div class="small text-muted">{{ $adm->patient->mrn }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $adm->bed->ward->name }}</div>
                            <span class="badge bg-light text-dark border">Bed {{ $adm->bed->bed_number }}</span>
                        </td>
                        <td>{{ $adm->doctor->user->name ?? 'Doctor' }}</td>
                        <td class="small">{{ $adm->admission_date->format('M d, Y - H:i') }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $adm->duration_days }} day(s)</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $adm->status === 'admitted' ? 'primary' : 'secondary' }} text-capitalize">
                                {{ $adm->status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('inpatient.show', $adm) }}" class="btn btn-sm btn-outline-primary">
                                Admission File
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No inpatient admissions found matching filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admissions->hasPages())
        <div class="card-footer bg-white">
            {{ $admissions->links() }}
        </div>
    @endif
</div>
@endsection
