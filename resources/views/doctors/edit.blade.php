@extends('layouts.app')

@section('title', 'Edit Doctor - ' . $doctor->user->name)
@section('page_title', 'Update Physician Profile')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Update Doctor: {{ $doctor->user->name }}</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('doctors.update', $doctor) }}" method="POST">
            @csrf
            @method('PUT')

            <h6 class="fw-bold text-primary mb-3">Account Details</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Doctor Full Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $doctor->user->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Hospital Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $doctor->user->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Phone Extension</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $doctor->user->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Staff Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $doctor->status) === 'active' ? 'selected' : '' }}>Active On Duty</option>
                        <option value="on_leave" {{ old('status', $doctor->status) === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="inactive" {{ old('status', $doctor->status) === 'inactive' ? 'selected' : '' }}>Inactive / Resigned</option>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Clinical Credentials & Department</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Department *</label>
                    <select name="department_id" class="form-select" required>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $doctor->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Specialization *</label>
                    <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $doctor->specialization) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">License Number *</label>
                    <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $doctor->license_number) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Qualifications</label>
                    <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $doctor->qualification) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Consultation Fee *</label>
                    <input type="number" step="0.01" name="consultation_fee" class="form-control" value="{{ old('consultation_fee', $doctor->consultation_fee) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Consulting Room</label>
                    <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $doctor->room_number) }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Biography</label>
                <textarea name="biography" class="form-control" rows="3">{{ old('biography', $doctor->biography) }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('doctors.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
