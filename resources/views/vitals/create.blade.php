@extends('layouts.app')

@section('title', 'Record Vital Signs & Triage')
@section('page_title', 'Nursing Intake - Vital Signs & Triage')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-activity text-danger me-2"></i>Patient Clinical Triage Intake</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('vitals.store') }}" method="POST">
            @csrf

            @if($appointment)
                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
            @endif

            <div class="mb-4">
                <label class="form-label fw-bold">Select Patient *</label>
                <select name="patient_id" class="form-select form-select-lg" required>
                    <option value="">-- Choose Patient --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (old('patient_id', $patient?->id) == $p->id) ? 'selected' : '' }}>
                            {{ $p->mrn }} - {{ $p->full_name }} ({{ $p->gender }}, {{ $p->age }} yrs)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Emergency Triage Category *</label>
                <div class="row g-2">
                    <div class="col-md">
                        <label class="card p-2 text-center border-danger bg-danger bg-opacity-10 cursor-pointer">
                            <input type="radio" name="triage_level" value="resuscitation" class="form-check-input mb-1 mx-auto" {{ old('triage_level') === 'resuscitation' ? 'checked' : '' }}>
                            <strong class="text-danger small">1. Resuscitation</strong>
                            <small class="text-muted" style="font-size: 0.7rem;">Immediate threat</small>
                        </label>
                    </div>
                    <div class="col-md">
                        <label class="card p-2 text-center border-warning bg-warning bg-opacity-10 cursor-pointer">
                            <input type="radio" name="triage_level" value="emergency" class="form-check-input mb-1 mx-auto" {{ old('triage_level') === 'emergency' ? 'checked' : '' }}>
                            <strong class="text-dark small">2. Emergency</strong>
                            <small class="text-muted" style="font-size: 0.7rem;">Within 15 mins</small>
                        </label>
                    </div>
                    <div class="col-md">
                        <label class="card p-2 text-center border-warning bg-warning bg-opacity-10 cursor-pointer">
                            <input type="radio" name="triage_level" value="urgent" class="form-check-input mb-1 mx-auto" {{ old('triage_level') === 'urgent' ? 'checked' : '' }}>
                            <strong class="text-dark small">3. Urgent</strong>
                            <small class="text-muted" style="font-size: 0.7rem;">Within 30 mins</small>
                        </label>
                    </div>
                    <div class="col-md">
                        <label class="card p-2 text-center border-primary bg-primary bg-opacity-10 cursor-pointer">
                            <input type="radio" name="triage_level" value="semi_urgent" class="form-check-input mb-1 mx-auto" {{ old('triage_level') === 'semi_urgent' ? 'checked' : '' }}>
                            <strong class="text-primary small">4. Semi-Urgent</strong>
                            <small class="text-muted" style="font-size: 0.7rem;">Within 60 mins</small>
                        </label>
                    </div>
                    <div class="col-md">
                        <label class="card p-2 text-center border-success bg-success bg-opacity-10 cursor-pointer">
                            <input type="radio" name="triage_level" value="non_urgent" class="form-check-input mb-1 mx-auto" {{ old('triage_level', 'non_urgent') === 'non_urgent' ? 'checked' : '' }}>
                            <strong class="text-success small">5. Non-Urgent</strong>
                            <small class="text-muted" style="font-size: 0.7rem;">Standard queue</small>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Systolic BP (mmHg)</label>
                    <input type="number" name="blood_pressure_systolic" class="form-control" placeholder="120" value="{{ old('blood_pressure_systolic') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Diastolic BP (mmHg)</label>
                    <input type="number" name="blood_pressure_diastolic" class="form-control" placeholder="80" value="{{ old('blood_pressure_diastolic') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Heart Rate (bpm)</label>
                    <input type="number" name="heart_rate" class="form-control" placeholder="72" value="{{ old('heart_rate') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Body Temp (°C)</label>
                    <input type="number" step="0.1" name="temperature" class="form-control" placeholder="36.8" value="{{ old('temperature') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Oxygen Saturation (%)</label>
                    <input type="number" name="oxygen_saturation" class="form-control" placeholder="98" value="{{ old('oxygen_saturation') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Respiratory Rate (bpm)</label>
                    <input type="number" name="respiratory_rate" class="form-control" placeholder="16" value="{{ old('respiratory_rate') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Weight (kg)</label>
                    <input type="number" step="0.1" name="weight" id="weight" class="form-control" placeholder="70.5" value="{{ old('weight') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Height (cm)</label>
                    <input type="number" step="0.1" name="height" id="height" class="form-control" placeholder="175" value="{{ old('height') }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Clinical Observations & Nursing Notes</label>
                <textarea name="clinical_notes" class="form-control" rows="3" placeholder="Patient general appearance, mobility, pain scale (1-10), acute symptoms...">{{ old('clinical_notes') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('vitals.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Save Vitals & Assign Triage</button>
            </div>
        </form>
    </div>
</div>
@endsection
