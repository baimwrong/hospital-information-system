@extends('layouts.app')

@section('title', 'Clinical Command Dashboard')
@section('page_title', 'Hospital Operations Command Center')

@section('content')
<div class="row g-3 mb-4">
    <!-- Active Patients -->
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="metric-val">{{ number_format($metrics['total_patients']) }}</div>
                <div class="metric-label">Registered Patients</div>
            </div>
        </div>
    </div>

    <!-- Today's Appointments -->
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-calendar2-check-fill"></i>
            </div>
            <div>
                <div class="metric-val">{{ number_format($metrics['today_appointments']) }}</div>
                <div class="metric-label">Today's Appointments</div>
            </div>
        </div>
    </div>

    <!-- Inpatient Bed Occupancy -->
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-hospital-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-baseline justify-content-between">
                    <div class="metric-val">{{ $metrics['occupied_beds'] }}/{{ $metrics['total_beds'] }}</div>
                    <small class="fw-bold text-warning">{{ $metrics['bed_occupancy_rate'] }}%</small>
                </div>
                <div class="metric-label">Bed Occupancy Rate</div>
            </div>
        </div>
    </div>

    <!-- Today's Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div>
                <div class="metric-val">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($metrics['today_revenue'], 2) }}</div>
                <div class="metric-label">Revenue Collected Today</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Secondary Status Pills -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">ACTIVE INPATIENT ADMISSIONS</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ $metrics['active_admissions'] }}</h3>
                </div>
                <a href="{{ route('inpatient.index') }}" class="btn btn-sm btn-outline-primary">View Wards</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">PENDING LAB INVESTIGATIONS</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ $metrics['pending_lab_tests'] }}</h3>
                </div>
                <a href="{{ route('laboratory.index') }}" class="btn btn-sm btn-outline-info">Go to LIS</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">LOW STOCK PHARMACY ALERTS</span>
                    <h3 class="fw-bold text-danger mt-1 mb-0">{{ $metrics['low_stock_medicines'] }}</h3>
                </div>
                <a href="{{ route('pharmacy.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-outline-danger">Refill Stock</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Today's Outpatient Appointments Queue -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>Outpatient (OPD) Schedule Queue</h6>
                <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-light border">All Appointments</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Doctor & Specialty</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAppointments as $apt)
                            <tr>
                                <td class="fw-bold text-secondary">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}</td>
                                <td>
                                    <a href="{{ route('patients.show', $apt->patient) }}" class="fw-semibold text-decoration-none">
                                        {{ $apt->patient->full_name }}
                                    </a>
                                    <div class="text-muted small">{{ $apt->patient->mrn }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $apt->doctor->user->name ?? 'Specialist' }}</div>
                                    <small class="text-muted">{{ $apt->doctor->specialization }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $apt->priority }} text-capitalize">{{ $apt->priority }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $apt->status }} text-capitalize">{{ str_replace('_', ' ', $apt->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No appointments scheduled for today.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Critical Triage Alerts & Low Stock -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-heart-pulse-fill me-2"></i>High Priority Triage Alerts</h6>
                <a href="{{ route('vitals.index') }}" class="btn btn-sm btn-light border">View Triage</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($triageAlerts as $alert)
                    <div class="list-group-item d-flex align-items-center justify-content-between py-2">
                        <div>
                            <a href="{{ route('patients.show', $alert->patient) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $alert->patient->full_name }}
                            </a>
                            <div class="small text-muted">
                                BP: <span class="fw-semibold text-danger">{{ $alert->blood_pressure_systolic }}/{{ $alert->blood_pressure_diastolic }}</span> &bull; 
                                HR: {{ $alert->heart_rate }} bpm &bull; 
                                SpO2: {{ $alert->oxygen_saturation }}%
                            </div>
                        </div>
                        <span class="badge badge-{{ $alert->triage_level }} text-capitalize">
                            {{ $alert->triage_level }}
                        </span>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small">No emergency or urgent triage alerts active.</div>
                @endforelse
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="bi bi-capsule text-warning me-2"></i>Critical Drug Formulary Stock</h6>
                <a href="{{ route('pharmacy.index') }}" class="btn btn-sm btn-light border">Formulary</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine</th>
                            <th>Remaining</th>
                            <th>Threshold</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockMeds as $med)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $med->brand_name }}</div>
                                    <small class="text-muted">{{ $med->generic_name }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-danger">{{ $med->stock_quantity }}</span>
                                </td>
                                <td>{{ $med->min_stock_alert }}</td>
                                <td>
                                    <a href="{{ route('pharmacy.edit', $med) }}" class="btn btn-sm btn-outline-secondary py-0">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted small">All medicines are above stock thresholds.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
