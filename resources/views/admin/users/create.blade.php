@extends('layouts.app')

@section('title', 'Register Staff Account')
@section('page_title', 'Hospital Staff Administration - Create Account')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 760px; margin: 0 auto;">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Create New Staff Account</h5>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Staff Directory
        </a>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold">Full Name & Title *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Sarah Connor, RN" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Hospital Role / Department *</label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Choose Role --</option>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>
                                {{ $label }} ({{ ucfirst($key) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold">Work Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="e.g. s.connor@hospital.local" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Contact Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+1 (555) 019-..." value="{{ old('phone') }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold">Initial Password *</label>
                    <input type="text" name="password" class="form-control" value="Staff@{{ rand(1000, 9999) }}" required minlength="8">
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle me-1"></i>Staff member will be prompted to change this on initial login.
                    </small>
                </div>
                <div class="col-md-5 d-flex align-items-center">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="isActiveSwitch">Account Active Immediately</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold">Register Staff Member</button>
            </div>
        </form>
    </div>
</div>
@endsection
