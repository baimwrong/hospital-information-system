@extends('layouts.app')

@section('title', 'Outpatient Appointments')
@section('page_title', 'Outpatient Department (OPD) - Appointments')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('appointments.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by Appointment #, Patient Name, MRN..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(['scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled', 'no_show'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('appointments.index') }}" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold">Appointment Schedule</h5>
        <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-calendar-plus me-1"></i> Book Appointment
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Appt #</th>
                    <th>Date & Time</th>
                    <th>Patient</th>
                    <th>Attending Doctor</th>
                    <th>Department</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $apt)
                    <tr>
                        <td class="fw-bold">{{ $apt->appointment_number }}</td>
                        <td>
                            <div>{{ $apt->appointment_date->format('M d, Y') }}</div>
                            <small class="text-muted fw-bold">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('patients.show', $apt->patient) }}" class="fw-semibold text-decoration-none">
                                {{ $apt->patient->full_name }}
                            </a>
                            <div class="small text-muted">{{ $apt->patient->mrn }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $apt->doctor->user->name ?? 'Physician' }}</div>
                            <small class="text-muted">{{ $apt->doctor->specialization }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $apt->department->name ?? 'OPD' }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $apt->priority }} text-capitalize">{{ $apt->priority }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $apt->status }} text-capitalize">{{ str_replace('_', ' ', $apt->status) }}</span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Action
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><a class="dropdown-item" href="{{ route('appointments.show', $apt) }}"><i class="bi bi-eye me-2"></i>View Details</a></li>
                                    <li><a class="dropdown-item" href="{{ route('vitals.create', ['patient_id' => $apt->patient_id, 'appointment_id' => $apt->id]) }}"><i class="bi bi-activity me-2"></i>Record Vitals</a></li>
                                    <li><a class="dropdown-item" href="{{ route('encounters.create', ['patient_id' => $apt->patient_id, 'appointment_id' => $apt->id]) }}"><i class="bi bi-journal-medical me-2"></i>Start Consultation</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('appointments.update-status', $apt) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="checked_in">
                                            <button type="submit" class="dropdown-item text-primary"><i class="bi bi-check-circle me-2"></i>Mark Checked In</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('appointments.update-status', $apt) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i>Cancel Appointment</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No appointments found matching criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($appointments->hasPages())
        <div class="card-footer bg-white">
            {{ $appointments->links() }}
        </div>
    @endif
</div>
@endsection
