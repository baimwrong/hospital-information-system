@extends('layouts.app')

@section('title', 'Admit Patient')
@section('page_title', 'Inpatient Department (IPD) - Hospitalization Intake')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-door-open text-primary me-2"></i>Inpatient Admission Form</h5>
        <span class="badge bg-primary fs-6">{{ $nextNumber }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('inpatient.admissions.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Select Patient for Hospitalization *</label>
                <select name="patient_id" class="form-select form-select-lg" required>
                    <option value="">-- Choose Patient --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (old('patient_id', $selectedPatient?->id) == $p->id) ? 'selected' : '' }}>
                            {{ $p->mrn }} - {{ $p->full_name }} ({{ $p->age }} yrs, {{ $p->gender }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Attending Inpatient Physician *</label>
                    <select name="doctor_id" class="form-select" required>
                        <option value="">-- Choose Doctor --</option>
                        @foreach($doctors as $d)
                            <option value="{{ $d->id }}" {{ old('doctor_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->user->name ?? 'Doctor' }} ({{ $d->specialization }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Assign Vacant Bed *</label>
                    <select name="bed_id" class="form-select" required>
                        <option value="">-- Select Available Bed --</option>
                        @foreach($availableBeds as $bed)
                            <option value="{{ $bed->id }}" {{ old('bed_id') == $bed->id ? 'selected' : '' }}>
                                {{ $bed->ward->name }} - Bed {{ $bed->bed_number }} ({{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($bed->effective_rate, 2) }}/day)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Clinical Indication / Reason for Admission *</label>
                <textarea name="admission_reason" class="form-control" rows="4" placeholder="Admitting diagnosis, initial monitoring requirements, telemetry needs, oxygen support..." required>{{ old('admission_reason') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('inpatient.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Admit Patient to Bed</button>
            </div>
        </form>
    </div>
</div>
@endsection
