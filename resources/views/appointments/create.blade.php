@extends('layouts.app')

@section('title', 'Schedule Appointment')
@section('page_title', 'Outpatient Department (OPD) - Book Appointment')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i>New Patient Appointment</h5>
        <span class="badge bg-primary fs-6">{{ $nextNumber }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('appointments.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Select Patient *</label>
                <select name="patient_id" class="form-select form-select-lg" required>
                    <option value="">-- Choose Patient from Registry --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (old('patient_id', $selectedPatient?->id) == $p->id) ? 'selected' : '' }}>
                            {{ $p->mrn }} - {{ $p->full_name }} ({{ $p->phone }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Consulting Physician *</label>
                    <select name="doctor_id" class="form-select" required>
                        <option value="">-- Select Specialist --</option>
                        @foreach($doctors as $d)
                            <option value="{{ $d->id }}" {{ old('doctor_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->user->name ?? 'Doctor' }} - {{ $d->specialization }} ({{ $d->department->name ?? 'Dept' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Department (Optional Override)</label>
                    <select name="department_id" class="form-select">
                        <option value="">Auto-select Doctor's Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date *</label>
                    <input type="date" name="appointment_date" class="form-control" value="{{ old('appointment_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Time Slot *</label>
                    <input type="time" name="appointment_time" class="form-control" value="{{ old('appointment_time', '09:00') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Priority *</label>
                    <select name="priority" class="form-select" required>
                        <option value="routine" {{ old('priority') === 'routine' ? 'selected' : '' }}>Routine</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="emergency" {{ old('priority') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Reason for Visit / Symptoms *</label>
                <textarea name="reason_for_visit" class="form-control" rows="3" placeholder="Describe presenting symptoms, checkup reason, or clinical indications..." required>{{ old('reason_for_visit') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold text-muted">Additional Booking Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Referral source, special accommodations, interpreter needed...">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('appointments.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Confirm Appointment</button>
            </div>
        </form>
    </div>
</div>
@endsection
