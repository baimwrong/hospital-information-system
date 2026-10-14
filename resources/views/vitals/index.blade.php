@extends('layouts.app')

@section('title', 'Triage & Vital Signs')
@section('page_title', 'Nursing Triage & Patient Vitals')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('vitals.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <select name="triage_level" class="form-select">
                    <option value="">All Triage Urgency Classifications</option>
                    @foreach(['resuscitation', 'emergency', 'urgent', 'semi_urgent', 'non_urgent'] as $lvl)
                        <option value="{{ $lvl }}" {{ request('triage_level') === $lvl ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $lvl)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filter Triage</button>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('vitals.create') }}" class="btn btn-primary">
                    <i class="bi bi-activity me-1"></i> Record New Vitals
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Recent Vital Signs Intake</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Patient</th>
                    <th>Triage Category</th>
                    <th>Blood Pressure</th>
                    <th>Heart Rate</th>
                    <th>Temp</th>
                    <th>SpO2</th>
                    <th>Weight / Height</th>
                    <th>BMI</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vitals as $v)
                    <tr>
                        <td class="small">{{ $v->recorded_at->format('M d, Y - H:i') }}</td>
                        <td>
                            <a href="{{ route('patients.show', $v->patient) }}" class="fw-semibold text-decoration-none">
                                {{ $v->patient->full_name }}
                            </a>
                            <div class="small text-muted">{{ $v->patient->mrn }}</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $v->triage_level }} text-capitalize">{{ str_replace('_', ' ', $v->triage_level) }}</span>
                        </td>
                        <td class="fw-bold {{ ($v->blood_pressure_systolic > 140) ? 'text-danger' : '' }}">
                            {{ $v->blood_pressure_systolic }}/{{ $v->blood_pressure_diastolic }} mmHg
                        </td>
                        <td>{{ $v->heart_rate }} bpm</td>
                        <td>{{ $v->temperature }} °C</td>
                        <td>{{ $v->oxygen_saturation }}%</td>
                        <td>{{ $v->weight }}kg / {{ $v->height }}cm</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $v->bmi }}</span>
                        </td>
                        <td class="small text-muted">{{ $v->recordedBy->name ?? 'Nurse' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">No vital signs entries recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vitals->hasPages())
        <div class="card-footer bg-white">
            {{ $vitals->links() }}
        </div>
    @endif
</div>
@endsection
