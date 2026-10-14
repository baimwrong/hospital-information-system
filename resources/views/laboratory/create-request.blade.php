@extends('layouts.app')

@section('title', 'Order Laboratory Test')
@section('page_title', 'Laboratory - New Diagnostic Requisition')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clipboard2-pulse text-primary me-2"></i>Laboratory Order Requisition</h5>
        <span class="badge bg-primary fs-6">{{ $nextNumber }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('laboratory.requests.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Patient *</label>
                    <select name="patient_id" class="form-select form-select-lg" required>
                        <option value="">-- Choose Patient --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ (old('patient_id', $selectedPatient?->id) == $p->id) ? 'selected' : '' }}>
                                {{ $p->mrn }} - {{ $p->full_name }} ({{ $p->age }} yrs, {{ $p->gender }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Ordering Physician *</label>
                    <select name="doctor_id" class="form-select form-select-lg" required>
                        <option value="">-- Choose Doctor --</option>
                        @foreach($doctors as $d)
                            <option value="{{ $d->id }}" {{ old('doctor_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->user->name ?? 'Physician' }} ({{ $d->specialization }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Requisition Priority *</label>
                    <select name="priority" class="form-select" required>
                        <option value="routine" {{ old('priority') === 'routine' ? 'selected' : '' }}>Routine (Standard Queue)</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="stat" {{ old('priority') === 'stat' ? 'selected' : '' }}>STAT (Emergency Immediate Turnaround)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Clinical Indication / Diagnostic Notes</label>
                    <input type="text" name="clinical_notes" class="form-control" placeholder="Symptoms, pre-op clearance, follow-up..." value="{{ old('clinical_notes') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Select Laboratory Investigations *</h6>
            <div class="row g-3 mb-4 p-3 bg-light rounded border">
                @foreach($testTypes as $test)
                    <div class="col-md-6">
                        <div class="card p-2 border">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="test_type_ids[]" value="{{ $test->id }}" id="chk_{{ $test->id }}">
                                <label class="form-check-label w-100" for="chk_{{ $test->id }}" style="cursor: pointer;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark small">{{ $test->name }}</strong>
                                        <span class="badge bg-light text-primary border">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($test->price, 2) }}</span>
                                    </div>
                                    <small class="text-muted d-block">{{ $test->category }} &bull; Specimen: {{ $test->sample_type ?? 'Blood' }} &bull; TAT: ~{{ $test->turnaround_time_hours }}h</small>
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('laboratory.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Submit Lab Requisition</button>
            </div>
        </form>
    </div>
</div>
@endsection
