@extends('layouts.app')

@section('title', 'Register Physician')
@section('page_title', 'Medical Staff - Register Physician')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge text-primary me-2"></i>Medical Practitioner Registration</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('doctors.store') }}" method="POST">
            @csrf

            <h6 class="fw-bold text-primary mb-3">Account & Login Credentials</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Doctor Full Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Dr. Jane Smith, MD" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Hospital Email *</label>
                    <input type="email" name="email" class="form-control" placeholder="jane.smith@hospital.local" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Temporary Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" value="Doctor@2026" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Direct Phone Extension</label>
                    <input type="text" name="phone" class="form-control" placeholder="+1-555-0199" value="{{ old('phone') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Clinical Credentials & Department</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Department *</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">-- Choose Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Specialization / Subspecialty *</label>
                    <input type="text" name="specialization" class="form-control" placeholder="e.g. Interventional Cardiology, Pediatric Neurology" value="{{ old('specialization') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Medical License Number *</label>
                    <input type="text" name="license_number" class="form-control" placeholder="MD-2026-XXXX" value="{{ old('license_number') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Qualifications & Degrees</label>
                    <input type="text" name="qualification" class="form-control" placeholder="e.g. MBBS, MD, FACC" value="{{ old('qualification') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Consultation Fee *</label>
                    <input type="number" step="0.01" name="consultation_fee" class="form-control" value="{{ old('consultation_fee', '80.00') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Consulting Room / Clinic</label>
                    <input type="text" name="room_number" class="form-control" placeholder="Clinic 204 (2nd Floor)" value="{{ old('room_number') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Biography & Profile Summary</h6>
            <div class="mb-4">
                <textarea name="biography" class="form-control" rows="3" placeholder="Physician background, fellowships, clinical interests...">{{ old('biography') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('doctors.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Register Physician</button>
            </div>
        </form>
    </div>
</div>
@endsection
