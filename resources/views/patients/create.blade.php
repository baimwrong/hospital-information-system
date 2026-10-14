@extends('layouts.app')

@section('title', 'Register New Patient')
@section('page_title', 'Outpatient / Inpatient Registration')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Patient Intake Form</h5>
        <span class="badge bg-primary fs-6">{{ $nextMrn }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('patients.store') }}" method="POST">
            @csrf
            <input type="hidden" name="mrn" value="{{ $nextMrn }}">

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-lines-fill me-1"></i> Personal Demographics</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">First Name *</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date of Birth *</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Gender *</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="Unknown">Unknown</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Genotype</label>
                    <input type="text" name="genotype" class="form-control" placeholder="e.g. AA, AS, SS" value="{{ old('genotype') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">National ID / SSN</label>
                    <input type="text" name="national_id" class="form-control" value="{{ old('national_id') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-telephone-inbound me-1"></i> Contact & Address</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" placeholder="+1-555-0192" value="{{ old('phone') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="patient@example.com" value="{{ old('email') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Residential Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Street, City, State" value="{{ old('address') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-exclamation me-1"></i> Emergency Contact (Next of Kin)</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Contact Person Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Contact Phone Number</label>
                    <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Relationship</label>
                    <input type="text" name="emergency_contact_relation" class="form-control" placeholder="e.g. Spouse, Parent, Sibling" value="{{ old('emergency_contact_relation') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-heart-pulse me-1"></i> Clinical History & Insurance</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Known Allergies (Comma separated)</label>
                    <input type="text" name="allergies" class="form-control" placeholder="e.g. Penicillin, Peanuts, Sulfa" value="{{ old('allergies') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Chronic Diseases (Comma separated)</label>
                    <input type="text" name="chronic_diseases" class="form-control" placeholder="e.g. Hypertension, Asthma, Diabetes" value="{{ old('chronic_diseases') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Insurance Carrier</label>
                    <input type="text" name="insurance_provider" class="form-control" placeholder="e.g. BlueCross, Aetna" value="{{ old('insurance_provider') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Insurance Policy / Member ID</label>
                    <input type="text" name="insurance_policy_number" class="form-control" placeholder="e.g. POL-889102" value="{{ old('insurance_policy_number') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('patients.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Save Patient Record</button>
            </div>
        </form>
    </div>
</div>
@endsection
