@extends('layouts.app')

@section('title', 'Edit Patient - ' . $patient->full_name)
@section('page_title', 'Update Patient Demographics')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Editing: {{ $patient->full_name }}</h5>
        <span class="badge bg-secondary fs-6">{{ $patient->mrn }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('patients.update', $patient) }}" method="POST">
            @csrf
            @method('PUT')

            <h6 class="fw-bold text-primary mb-3">Personal Demographics</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">First Name *</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $patient->first_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $patient->last_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date of Birth *</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Gender *</label>
                    <select name="gender" class="form-select" required>
                        <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $patient->gender) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="Unknown" {{ $patient->blood_group === 'Unknown' ? 'selected' : '' }}>Unknown</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Genotype</label>
                    <input type="text" name="genotype" class="form-control" value="{{ old('genotype', $patient->genotype) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">National ID</label>
                    <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $patient->national_id) }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Contact & Clinical Status</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $patient->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="deceased" {{ old('status', $patient->status) === 'deceased' ? 'selected' : '' }}>Deceased</option>
                        <option value="archived" {{ old('status', $patient->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $patient->address) }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Allergies & Insurance</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Allergies (comma-separated)</label>
                    <input type="text" name="allergies" class="form-control" value="{{ old('allergies', is_array($patient->allergies) ? implode(', ', $patient->allergies) : '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Chronic Diseases (comma-separated)</label>
                    <input type="text" name="chronic_diseases" class="form-control" value="{{ old('chronic_diseases', is_array($patient->chronic_diseases) ? implode(', ', $patient->chronic_diseases) : '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Insurance Carrier</label>
                    <input type="text" name="insurance_provider" class="form-control" value="{{ old('insurance_provider', $patient->insurance_provider) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Policy Number</label>
                    <input type="text" name="insurance_policy_number" class="form-control" value="{{ old('insurance_policy_number', $patient->insurance_policy_number) }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Update Record</button>
            </div>
        </form>
    </div>
</div>
@endsection
