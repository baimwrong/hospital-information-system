@extends('layouts.app')

@section('title', 'Patients Directory')
@section('page_title', 'Electronic Medical Records (EMR) - Patients')

@section('content')
<!-- Filter & Search Toolbar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('patients.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by MRN, Name, Phone, Email, National ID..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="gender" class="form-select">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ request('gender') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="blood_group" class="form-select">
                    <option value="">All Blood Groups</option>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                        <option value="{{ $bg }}" {{ request('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('patients.index') }}" class="btn btn-light border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Patients Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold">Active Patient Cohort</h5>
        <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-person-plus me-1"></i> Register Patient
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>MRN</th>
                    <th>Full Name</th>
                    <th>Gender / Age</th>
                    <th>Blood Group</th>
                    <th>Phone / Contact</th>
                    <th>Insurance Provider</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $patient)
                    <tr>
                        <td>
                            <a href="{{ route('patients.show', $patient) }}" class="fw-bold text-decoration-none">
                                {{ $patient->mrn }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $patient->full_name }}</div>
                            <small class="text-muted">{{ $patient->email ?? 'No email recorded' }}</small>
                        </td>
                        <td>
                            <span class="text-capitalize">{{ $patient->gender }}</span> &bull; 
                            <span class="text-muted">{{ $patient->age }} yrs</span>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">{{ $patient->blood_group }}</span>
                        </td>
                        <td>
                            <div>{{ $patient->phone }}</div>
                            <small class="text-muted">{{ $patient->emergency_contact_phone ? 'ICE: ' . $patient->emergency_contact_phone : '' }}</small>
                        </td>
                        <td>
                            {{ $patient->insurance_provider ?? 'Self-Pay' }}
                        </td>
                        <td>
                            <span class="badge bg-success text-capitalize">{{ $patient->status }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-primary" title="Clinical File">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-secondary" title="Edit Demographics">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-info" title="Book Appointment">
                                    <i class="bi bi-calendar-plus"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                            No patient records match the criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($patients->hasPages())
        <div class="card-footer bg-white">
            {{ $patients->links() }}
        </div>
    @endif
</div>
@endsection
